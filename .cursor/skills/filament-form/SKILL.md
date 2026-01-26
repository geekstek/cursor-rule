---
name: filament-form
description: 专注于 表单编辑场景的设计规范。使用场景在Filament Form表单 / Filament Page 等。
---

# Filament Form 设计规范

# 核心原则

- 零 Description：Section 标题必须精准，配合 Icon + Color 传递意图，节省垂直空间。
- 黄金布局 (2/3 + 1/3)：默认采用“左侧内容 + 右侧管理”的布局，避免单列直排导致页面过长。

# 布局架构规范 (Layout Architecture)

为了控制屏效，表单统一使用 Grid 系统控制宽度。

## 1. 标准资源表单 (Resource Form)

使用 3栏网格 作为基座：

- 左侧 (Column Span 2)：生产区。放标题、正文、核心业务数据。
- 右侧 (Column Span 1)：管理区。放状态、分类、图片、元数据、设置。

参考代码模版: `assets\main-sidebar-form.php`

## 2. 字段密度控制

在 Section 内部，根据字段类型决定列宽：

- 富文本/大输入框：columnSpanFull() (通栏)。
- 成对数据 (时间/坐标/价格)：columns(2) (1/2 + 1/2)。
- 短文本密集区：在侧边栏中自动堆叠，或在主栏使用 columns(3)。

# 空间优化策略 (无 Description 模式)

为了弥补没有 Description 带来的解释性缺失，并防止页面过长：

## 善用 Placeholder 说明：

如果字段有需要解释时，不要写在 Section description 里，写在字段的 `placeholder()`、`hint()` 或 `helperText()` 里。 (按顺序优先合理地使用)

```php
TextInput::make('slug')
    ->placeholder('Unique ID used in URL') // 解释紧跟字段，不仅省空间，交互更直接
```

## Tabs 的介入时机：

- 原则：如果同一层级（如主栏内）出现了 3个以上 高度超过 300px 的 Section，必须将其转换为 Tabs。
- Tab 图标色：Tab 也可以设置 `iconColor`，遵循上述同样的色彩规范。

## Fieldset 代替小 Section：

- 不要在大 Section 里套小 Section，这会产生双重边框，浪费空间。
- 使用 `Fieldset` 对相关字段（如地址：省/市/区）进行分组。

