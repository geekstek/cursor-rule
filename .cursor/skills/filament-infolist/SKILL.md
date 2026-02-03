---
name: filament-infolist
description: 专注于 Filament 详情页的设计规范。使用场景在Filament Infolist 定义 等。
---

# Filament Infolist 设计规范

# 核心原则

- 零 Description：Section 标题必须精准，配合 Icon + Color 传递意图，节省垂直空间。
- 黄金布局 (2/3 + 1/3)：默认采用“左侧内容 + 右侧管理”的布局，避免单列直排导致页面过长。

# 布局架构规范 (Layout Architecture)

为了控制屏效，表单统一使用 Grid 系统控制宽度。

## 1. 标准资源表单 (Resource Infolist)

使用 3栏网格 作为基座：

- 左侧 (Column Span 2)：生产区。放标题、正文、核心业务数据。
- 右侧 (Column Span 1)：管理区。放状态、分类、图片、元数据、设置。

参考代码模版: `assets\main-sidebar-infolist.php`

## 2. 字段密度控制

在 Section 内部，根据字段类型决定列宽：

- 富文本/大输入框：columnSpanFull() (通栏)。
- 成对数据 (时间/坐标/价格)：columns(2) (1/2 + 1/2)。
- 短文本密集区：在侧边栏中自动堆叠，或在主栏使用 columns(3)。

# 空间优化策略 (无 Description 模式)

为了弥补没有 Description 带来的解释性缺失，并防止页面过长：

## 善用 Placeholder：

消除歧义 (Disambiguation) 和 维持视觉完整性 (Visual Integrity)。当数据为 null 或空字符串时，如果不显示任何内容，用户会困惑：“是系统加载失败了？还是本来就没数据？还是样式出错了？”

- 场景 A：有效期/结束时间

    - 如果 ends_at 为空，通常意味着“永久有效”。
    - Bad UX: 显示空白或 -。
    - Good UX: 显示 "Forever" 或 "Unlimited"。

```php
TextEntry::make('ends_at')
    ->date()
    ->placeholder('Forever') // 明确告知用户：这是永久的
```

- 场景 B：上级归属

    - 如果 parent_id 为空，意味着它是“顶级分类”或“根节点”。

```php
TextEntry::make('parent.name')
    ->label('Parent Category')
    ->placeholder('Root Level') // 明确告知：这是顶级
```

- 场景 C：指派人

    - 如果 assignee_id 为空，意味着“待认领”或“放入公海池”。

```php
TextEntry::make('assignee.name')
    ->placeholder('Unassigned') // 比空白更有行动导向
```

## Tabs 的介入时机：

- 原则：如果同一层级（如主栏内）出现了 3个以上 高度超过 300px 的 Section，必须将其转换为 Tabs。
- Tab 图标色：Tab 也可以设置 `iconColor`，遵循上述同样的色彩规范。

## Fieldset 代替小 Section：

- 不要在大 Section 里套小 Section，这会产生双重边框，浪费空间。
- 使用 `Fieldset` 对相关字段（如地址：省/市/区）进行分组。

