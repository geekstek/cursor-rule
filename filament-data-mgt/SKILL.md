---
name: filament-data-mgt
description: 专注于 Filament CRUD的设计规范。当需要对整个业务模块设计各个功能的分组(Filament Cluster、Filament Resource、Filament Page)，判断 Filament 各种“数据管理能力”在职责上的边界差异、在大型业务系统中，各能力各自“最适合/不适合”的场景以及如何组合使用，避免系统退化成“数据库编辑器”，并获得最优用户体验（UX）。
---

# Filament 数据管理能力深度解析

目标是解决三个核心问题：

1.  **Filament 各种“数据管理能力”在职责上的边界差异**
2.  **在大型业务系统中，各能力各自“最适合/不适合”的场景**
3.  **如何组合使用，避免系统退化成“数据库编辑器”，并获得最优用户体验（UX）**
    
---

## 一、先给你一个**整体决策地图（非常重要）**

> 你现在最大的风险不是“功能不够”，而是 **全部用 Resource，导致管理后台成为 DB Admin 工具，而不是业务系统**

### Filament 各能力的“定位轴心”

| 维度 | 更偏数据 | 更偏业务 |
| --- | --- | --- |
| 技术抽象 | Model CRUD | 业务用例 / 流程 |
| 用户感知 | 表结构 | 业务动作 |
| 权限控制 | 表级 / 行级 | 用例级 |
| 推荐对象 | 管理员 / IT | 业务人员 |

**从左到右：**

```php
Resources
  └─ Relation Managers
       └─ Nested Resources
            └─ Relation Pages
                 └─ Custom Resource Pages
                      └─ Custom Pages
```

> **越往右，越不像数据库，越像“系统功能”**

---

## 二、逐项深度分析（结合你真实业务）

---

## 1️⃣ Resources（资源）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/resources/overview)

### 本质定位

> **“可被业务直接管理的核心业务实体”**

不是“所有表”，而是**对人有意义的业务对象**

### 特色

-   强 CRUD 能力（List / Create / Edit / View）
-   强权限体系（Policy + Filament Gate）
-   支持：
    -   Filters / Scopes
    -   Bulk Actions
    -   Global Search
-   最容易被滥用
    

### 在你系统中的**正确使用范围**

✅ **必须使用 Resource 的对象**

| 模块 | 示例 |
| --- | --- |
| 行政人事 | 员工、岗位、合同 |
| 财务 | 收入单、支出单、对账批次 |
| 出入管理 | 门禁设备、通行卡 |
| 家政 | 服务订单 |
| 巡逻 | 巡逻任务、巡逻点 |

❌ **不应该用 Resource 的对象**

-   中间表（pivot）
-   纯配置表（状态枚举、字典）
-   纯日志表（如 door\_access\_logs）
-   “只有程序用”的技术表
    

### UX 判断标准（非常重要）

> **如果业务人员会问：**
> -   “我要新增一个 ××”
> -   “我要查 ×× 的状态”
> -   “我要对 ×× 执行操作”
>   

→ **才值得一个 Resource**

---

## 2️⃣ Custom Pages（自定义页面）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/navigation/custom-pages)

### 本质定位

> **“不是 CRUD，而是一个业务页面”**

### 特色

-   不绑定 Model
-   不受 CRUD 约束
-   可以是：
    -   仪表盘
    -   向用友 / 金蝶同步
    -   批量导入 / 审批
    -   报表分析
        

### 在你系统中的高价值场景

✅ **必定使用 Custom Pages 的场景**

| 场景 | 说明 |
| --- | --- |
| 财务对接 | 用友 / 金蝶同步页面 |
| 集团视角 | 跨 company\_id 汇总报表 |
| 复杂操作 | “结算本月物业费” |
| 审批中心 | 多模块统一审批 |

### UX 优势

-   用户不会“迷路到数据表”
-   页面语义明确（一个页面 = 一个业务目标）
    

---

## 3️⃣ Relation Managers（关系管理器）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/resources/managing-relationships)

### 本质定位

> **“在父资源内部，管理简单的从属数据”**

### 特色

-   嵌在 Resource 的 Tab / Section 内
-   适合：
    -   数量少
    -   操作简单
    -   强依附关系

### 正确使用场景

✅ **强烈推荐**

| 父资源 | Relation |
| --- | --- |
| 员工 | 家庭成员 |
| 社区 | 楼栋 |
| 服务订单 | 服务明细 |
| 企业 | 银行账户 |

### ❌ 不适合

-   关系数据本身有复杂生命周期
-   需要独立权限
-   需要复杂筛选 / 报表

> **一旦 Relation Manager 页面开始变复杂，就该升级为 Relation Page 或 Nested Resource**

---

## 4️⃣ Relation Pages（关系页面）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/resources/managing-relationships#relation-pages)

### 本质定位

> **“关系数据很重要，但仍然从属于父对象”**

### 特色

-   独立 URL
-   保留父上下文
-   可以拥有复杂 Table / Form
    

### 在你系统中极其重要（⭐）

适合你这种 **“业务多 + 数据多 + 但逻辑有主线”** 的系统

### 高价值场景

| 父 | 关系 |
| --- | --- |
| 企业 | 社区 |
| 社区 | 门禁记录 |
| 员工 | 巡逻记录 |
| 业户 | 缴费记录 |

### UX 优势

-   用户知道“我在看谁的什么数据”
-   不会丢失上下文
-   比 Relation Manager 可扩展得多

---

## 5️⃣ Nested Resources（嵌套资源）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/resources/nesting)

### 本质定位

> **“这是一个资源，但必须在父资源上下文中存在”**

### 特色

-   URL 强语义
-   权限天然继承
-   数据隔离非常清晰

### 在你 multi-tenancy 场景中的价值

⭐ **强烈推荐用于 company / community 级结构**

| 示例 |
| --- |
| Company → Communities |
| Community → Buildings |
| Community → Devices |
| Community → Staff |

### 什么时候必用？

-   数据 **不能脱离父对象存在**
-   需要 **天然限制 company\_id**
-   防止“跨社区误操作”
    

---

## 6️⃣ Singular Resources（单一资源）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/resources/singular)

### 本质定位

> **“整个系统只有一份的数据”**

### 特色

-   没有 List
-   只有 View / Edit
-   非常适合“系统级配置”

### 在你系统中的典型用途

| 示例 |
| --- |
| 集团基础信息 |
| 财务对接配置（用友 / 金蝶） |
| 全局业务规则 |
| 消息模板总配置 |

> **这能极大减少用户认知负担**

---

## 7️⃣ Custom Resource Pages（自定义资源页面）

参考资源：[官方文档](https://filamentphp.com/docs/4.x/resources/custom-pages)

### 本质定位

> **“围绕某个资源的业务操作页面”**

### 这是你系统的**核心能力之一**

### 高价值使用场景

| 资源 | 页面 |
| --- | --- |
| 账单 | 批量结算 |
| 员工 | 调岗 |
| 社区 | 初始化物业数据 |
| 企业 | 年度汇总 |

### 为什么不用 Custom Page？

-   需要 Resource 权限
-   需要当前记录上下文
-   操作强依赖 Model

---

## 8️⃣ Clusters（集群）

### 本质定位

> **“导航级的业务分组”**

不是数据概念，而是 **UX 结构设计**

### 在你多 package + 多 panel 的系统中

⭐ **必用**

### 正确分法

```
财务管理
 ├─ 收入管理
 ├─ 支出管理
 ├─ 财务对接
 └─ 财务报表

出入管理
 ├─ 门禁设备
 ├─ 权限规则
 └─ 通行记录
```

### UX 价值

-   减少左侧菜单噪音
-   降低新用户学习成本
-   与业务模块天然对齐

---

## 三、推荐你的「暂时最优组合方案」

### ✅ 核心原则（请记住）

> **不是“一个表一个 Resource”，而是“一个业务对象一个 Resource”**

### 推荐结构（示意）

-   **70%** 核心对象 → Resources
-   **20%** 关键从属对象 → Nested Resources / Relation Pages
-   **10%** 复杂操作 → Custom Pages / Custom Resource Pages

### 示例：财务模块

-   Resource：账单、付款记录
-   Relation Page：社区 → 缴费记录
-   Custom Resource Page：账单 → 月度结算
-   Custom Page：用友 / 金蝶同步
-   Singular Resource：财务全局配置

---

## 四、最后给你一句“架构判断金句”

> **如果一个页面的存在价值是“让用户完成某件事”，而不是“修改一行数据”，那它就不应该只是一个 Resource。**
