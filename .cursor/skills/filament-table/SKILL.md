---
name: filament-table
description: 专注于 Filament 表格的设计规范。使用场景在Filament Table Columns定义、Filament Table Filter 筛选表单布局 及组件 等。
---

# Filament Table 设计规范

# 核心原则

- 根据 示例代码: `assets\table-template.php`，优化 configure() 方法的调用方式

## 活化列(Columns)

为减少 Table 列的数量及用户体验

### 记录动作 recordActions

在 `->recordActions()` 中必须使用 `Filament\Actions\ActionGroup` 来包裹其他动作类，避免展开所有 Action，占据页面宽度

## 全局配置

为了方便管理，系统加入全局Table 配置管理 (`Table::configureUsing()`)，需要对旧代码进行优化

如发现`$table` 中有使用以下方法，而且参数一致，请删除

- `FiltersLayout::Modal`
- `->filtersFormColumns(2)`
- `->persistColumnSearchesInSession()`
- `->persistFiltersInSession()`
- `->persistSearchInSession()`
- `->persistSortInSession()`
- `->striped()`

如发现`$table` 中有缺失以下方法，而且参数一致，请加上

- `->searchOnBlur()`
- `->searchDebounce('1000ms')`
- `->recordAction(null)`
- `->recordUrl(null)`

## filters 筛选 `->filters()` 设计及规范

### 布局 `->filtersFormSchema([])` 筛选表单布局

- 筛选表单默认为两栏，如果有需要可在`$table` 中使用 `->filtersFormColumns(?)` 调整
- `->filtersFormSchema([])` 中的数组支持 Filament Layout 布局(Grid / Section 等组件)，代码可以以 **Filament 表单编辑场景** @filament-form 来设计筛选表单，提升用户体验，参考: https://filamentphp.com/docs/4.x/tables/filters/layout#customizing-the-filter-form-schema
- 是两栏布局时，使用两个Grid分左/右栏，再在每个栏中加入Section 达成瀑布式布局，因为全局FilterForm默认使用`filtersFormColumns(2)`，所以不用 `Grid::make(2)->schema` 包裹左/右栏
- Section 的 icon() 使用 Filament\Support\Icons\Heroicon Enums，参考 **Filament 图标规范** @filament-icon
- 对于具有三种状态（通常为真、假和空白）的选择筛选器，例如 `is_admin` (`is_*`、`can_*`、`has_*`等)，使用 Filament\Tables\Filters\TernaryFilter 三元过滤器，参考: https://filamentphp.com/docs/4.x/tables/filters/ternary
- 对于使用 `Filament\Tables\Filters\SelectFilter` 时, 需要加入 `->searchable()` 和 `->preload()`

### 字段

- 按需加入筛选字段