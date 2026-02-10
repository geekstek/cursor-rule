---
name: filament-icon
description: Filament 图标规范。使用场景在Filament Resource / Filament Table Filter Form / Filament Page / Filament Widget 等。
---

# Filament Icon 定义

# 目的

- 类型安全：使用 Enum 替代字符串，避免拼写错误。
- 代码一致性：与项目中其他使用 Enum 的代码保持一致。

# Heroicon 枚举类的正确用法

添加 Heroicon Enum 导入
```php
use Filament\Support\Icons\Heroicon;

->icon(Heroicon::User)
->icon(Heroicon::OutlinedCheckCircle)
->icon(Heroicon::XCircle)
```

# 判断Icon 使用Outlined

## Section 和 Tab

无论在 Form / Infolist 中，实现 Icon 时 `Heroicon::XXX`实心图标，不使用 `Heroicon::OutlinedXXX` 线框图标。

### 常用 IconColor 选择

1. Primary (主色) —— 核心业务域

- `->iconColor('primary')`
- 规范原则：页面中最重要的、用户最需要聚焦的数据区块。
- 适用场景：
    - 基础信息：资源的“身份”数据（如：用户姓名/头像、文章标题/内容、商品价格/SKU）。
    - 核心业务逻辑：定义该记录存在的关键字段。
    - 默认状态：如果你不确定该用什么颜色，且该板块不是次要信息，使用 Primary。

2. Gray (灰色) —— 辅助/元数据/配置

- `->iconColor('gray')`
- 规范原则：降低视觉干扰，表示该区域是次要的、技术性的或可选的。
- 适用场景：
    - 元数据 (Metadata)：SEO 设置、Slug、系统生成的 ID。
    - 时间戳：创建时间、更新时间、最后登录时间。
    - 高级设置：通常配合 collapsed() 使用的“高级选项”，如 JSON 配置字段、回调地址等。
    - 关联关系：非核心的关联数据（如：所属标签、分类），不希望抢夺主信息的注意力。

3. Info (蓝色) —— 审计与参考

- `->iconColor('info')`
- 规范原则：提供客观的参考信息，不涉及价值判断（好/坏），仅做展示。
- 适用场景：
    - 审计日志：操作记录、修改历史、IP 记录。
    - 只读关联：在 Form 中展示但不可编辑的参考数据（如：创建该订单的用户详情）。
    - 帮助/指引：包含大量 Placeholder 或 View 字段的说明性板块。

4. Success (绿色) —— 验证与状态确认

- 规范原则：强调“已完成”、“安全”或“通过校验”的状态。
- 适用场景：
    - 认证信息：邮箱已验证、KYC 认证通过、手机号已绑定。
    - 财务确认：已支付详情、已退款详情。
    - 健康状态：服务运行正常、API 连接测试通过的展示区块。

5. Warning (橙色) —— 敏感操作与注意

- `->iconColor('warning')`
- 规范原则：引起用户警觉，提示此处修改需谨慎，或数据存在潜在问题。
- 适用场景：
    - 敏感修改 (Form)：修改密码、重置 API Key、修改权限（RBAC）。
    - 待处理事项：显示“库存预警”、“发货延迟”、“待审核”内容的板块。
    - 备注/便签：管理员留下的内部备注（Internal Notes），需醒目以防忽略。

6. Danger (红色) —— 危险区与阻断

- `->iconColor('danger')`
- 规范原则：最高级别的警示，通常涉及破坏性操作或严重错误。
- 适用场景：
    - 危险区 (Danger Zone)：包含删除按钮、注销账户、强制下线等操作的 Section。
    - 封禁/拒绝状态 (Infolist)：展示用户被封禁原因、交易被拒绝原因的详情块。
    - 错误堆栈：展示系统崩溃日志或 API 请求失败详情的区域。

## Resource / Page 菜单图标

菜单生效时使用实心图标, 不生效时使用线框图标

- `$activeNavigationIcon = Heroicon::XXX;`
- `$navigationIcon = Heroicon::OutlinedXXX;`
