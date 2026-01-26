---
name: notification-service
description: 通知服务层最佳实践。核心职责是 **“对外喊话”**。它隔离了具体的发送渠道（邮件、短信、钉钉、Slack），让业务逻辑层（Action）不需要关心“怎么发”或“发给谁”，只需要关心“发什么”。
---

# NotificationService 定义

# 目的

文件的內容、目標和範圍

`NotificationService`（通知服务）在架构中的定位是 **“消息的发送者与路由者”**。

它的核心职责是 **“对外喊话”**。它隔离了具体的发送渠道（邮件、短信、钉钉、Slack），让业务逻辑层（Action）不需要关心“怎么发”或“发给谁”，只需要关心“发什么”。

### 1. 核心职责

1. **渠道抽象 (Abstraction)**：Action 只需要喊一句“通知老板”，Service 负责决定是发 Email 还是 SMS，或者是钉钉机器人。
2. **内容组装 (Formatting)**：将业务对象（如 `Order`）转换成通知模板所需的变量（如“您的订单 #123 已发货”）。
3. **接收人解析 (Recipient Resolution)**：(可选) 有时候 Action 只传了订单，Service 负责找出“谁是这个订单的管理员”并发送。

### 2. 基础写法：封装 Laravel Notification

这是最常见的情况。虽然 Laravel 有 `User->notify()`，但如果直接写在 Action 里，Action 会依赖具体的 Notification 类，且难以测试。

**封装后：**

```php
namespace App\Services\Notifications;

use App\Models\User;
use App\Models\LeaveRequest;
use App\Notifications\LeaveAppliedNotification; // Laravel 原生通知类

class LeaveNotificationService
{
    /**
     * 发送“请假已提交”通知给审批人
     */
    public function notifyApprover(User $approver, LeaveRequest $leave): void
    {
        // 这里的逻辑很简单，但它把具体的 Notification 类藏起来了
        // 以后如果要换成短信，只需要改这里，不用改 Action
        $approver->notify(new LeaveAppliedNotification($leave));
    }

    /**
     * 发送“审批结果”通知给申请人
     */
    public function notifyApplicantResult(LeaveRequest $leave): void
    {
        $applicant = $leave->user;
        
        // 可以在这里判断：如果用户绑定了微信，发微信；否则发邮件
        if ($applicant->wechat_openid) {
             // 调用微信发送逻辑...
        } else {
             $applicant->notify(new LeaveResultNotification($leave));
        }
    }
}
```

### 3. 进阶写法：多渠道智能路由

有时候通知逻辑很复杂，比如 **“监控报警”**：

- 一般问题：发 Slack。
- 严重问题：发 Slack + 邮件。
- 灾难问题：发短信 + 电话呼叫。

这种逻辑**不应该**写在 Action 里，必须封装在 Service 中。

```php
namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlert;

class AlertNotificationService
{
    public function sendAlert(string $message, string $level): void
    {
        $channels = ['slack']; // 默认渠道

        if ($level === 'critical') {
            $channels[] = 'mail';
            $channels[] = 'sms';
        }

        // 动态路由发送
        Notification::route('slack', config('logging.slack_webhook'))
            ->route('mail', 'admin@company.com')
            ->notify(new SystemAlert($message, $channels));
    }
}
```

### 4. 在 Action 中如何使用？

Action 负责 **“触发”**，Service 负责 **“执行”**。

**注意**：通知通常应该放在 **事务提交之后**（After Commit）。如果事务回滚了，却发出了“支付成功”的邮件，那就是严重的 Bug。

```php
class ApproveLeaveAction
{
    public function __construct(
        protected LeaveRepository $repo,
        protected LeaveNotificationService $notifier // 注入通知服务
    ) {}

    public function handle(User $manager, LeaveRequest $leave)
    {
        DB::transaction(function () use ($manager, $leave) {
            // 1. 修改业务状态
            $this->repo->approve($leave, $manager);
            
            // 2. 发送通知 (通常建议放入队列，避免阻塞)
            // 注意：最好的做法是触发 Event，在 Listener 里调 Service
            // 但如果是简单项目，直接在这里调 Service 也可以，但要注意事务问题
            $this->notifier->notifyApplicantResult($leave);
        });
    }
}
```

### 5. 什么时候用 Event，什么时候直接调 NotificationService？

这是架构设计中常见的抉择：

A. 直接调用 (`Action -> NotificationService`)

- **适用场景**：业务简单，必须通知（通知是业务流程的一部分，比如发送验证码）。
- **优点**：代码直观，一眼看懂发生了什么。
- **缺点**：耦合度稍高，会拖慢 Action 的响应速度（除非 Service 内部用了队列）。

B. 事件驱动 (`Action -> Event -> Listener -> NotificationService`)

- **适用场景**：解耦场景，或者一个动作触发多个不相关的通知（发邮件 + 统计埋点 + 推送大数据）。
- **优点**：Action 极其干净，响应极快（Event 异步处理）。
- **缺点**：代码分散，看 Action 无法直观知道“发通知了没”。

**推荐做法：**

在规范文档中，通常推荐 **“核心业务流程（如验证码）直接调 Service，副作用通知（如订单完成）走 Event”**。

### 6. NotificationService 书写规范总结

| **维度** | **规范** |
| --- | --- |
| **命名** | `<Domain>NotificationService` (如 `OrderNotificationService`) |
| **方法名** | `notify<Target>(...)` 或 `send<Type>(...)` |
| **入参** | 接收人 (User/Contact) + 业务对象 (Model/DTO) |
| **依赖** | 依赖 Laravel 的 Notification 门面或 Mailer |
| **禁止** | **禁止**在方法内修改业务数据状态（只读/发送） |
| **最佳实践** | 方法内部应当尽量将通知推入 **Queue (队列)**，避免阻塞主线程 |

### 7. 示例对比

- **Action**: "我要批准这个请假单。" (`ApproveLeaveAction`)
- **Service**: "好的，老板批了。我去写一封措辞优美的邮件，并通过 SMTP 服务器发给员工张三。" (`LeaveNotificationService`)
