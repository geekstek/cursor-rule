---
name: filament-table
description: 专注于 Filament 表格的设计规范。使用场景在Filament Table 定义 等。
---

# Filament Table 设计规范

# 核心原则

- 示例代码: `assets\table-template.php`

## 活化列(Columns)

为减少 Table 列的数量及用户体验

### 记录动作 recordActions

在 `->recordActions()` 中必须使用 `Filament\Actions\ActionGroup` 来包裹其他动作类, 避免展开所有 Action, 占据页面宽度

## 全局配置

为了方便管理, 系统加入全局Table 配置管理 (`Table::configureUsing()`), 需要对旧代码进行优化

如发现`$table` 中有使用以下方法, 而且参数一致, 请删除

- `FiltersLayout::Modal`
- `->filtersFormColumns(2)`
- `->persistColumnSearchesInSession()`
- `->persistFiltersInSession()`
- `->persistSearchInSession()`
- `->persistSortInSession()`
- `->striped()`

如发现`$table` 中有缺失以下方法, 而且参数一致, 请加上

- `->searchOnBlur()`
- `->searchDebounce('1000ms')`
- `->recordAction(null)`
- `->recordUrl(null)`

## filters 筛选

对 `->filters()` 设计及规范

### 布局

- 筛选表单默认为两栏, 如果有需要可在`$table` 中使用 `->filtersFormColumns(?)` 调整
- `->filters([])` 中的数组支持 Filament Layout 布局(Grid / Section 等组件), 代表可以以 **Filament 表单编辑场景** 来设计筛选表单, 提升用户体验

### 字段

- 按需加入筛选字段