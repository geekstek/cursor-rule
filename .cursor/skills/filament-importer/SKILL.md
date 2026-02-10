---
name: filament-importer
description: 专注于 Filament Importer 类开发规范。
---

# 核心原则

- 如果是 `BelongsTo` 和 `BelongsToMany` 关系, 优先使用 `relationship()` 方法
- 字段是必填时, 除了在 `ImportColumn` 加入 `->requiredMapping()` 方法外, 还要在 `->rules([])` 数组中加入 `'required'`
- 如果要解析的关联关系不是 `BelongsTo` 和 `BelongsToMany` 关系, 请在 `fillRecordUsing()` 闭包中手动查询关联记录并赋值外键, 找不到时抛出 `RowImportFailedException`
- `RowImportFailedException` 可以在 `resolveRecord()`、`fillRecordUsing()`、生命周期钩子(`beforeFill`/`beforeSave` 等)、`saveRecord()` 中抛出, 均会被捕获并记录到失败 CSV

# 方法执行顺序

基于 `Importer::__invoke()` 源码, 每行 CSV 数据的处理顺序如下：

```
 1. remapData()                           -- 列名映射
 2. castStateUsing()                      -- 每列类型转换 (validation 之前)
 3. resolveRecord()                       -- 解析/创建 Model 实例
 4. checkColumnMappingRequirementsForNewRecords()
 5. beforeValidate hook
 6. rules() / validateData()              -- 验证
 7. afterValidate hook
 8. beforeFill hook
 9. fillRecordUsing() / fillRecord()      -- 每列填充到 Model
10. afterFill hook
11. beforeSave hook
12. beforeCreate / beforeUpdate hook
13. saveRecord()                          -- 持久化
14. afterSave hook
15. afterCreate / afterUpdate hook
```

关键要点：
- `resolveRecord()` 在 `fillRecordUsing()` **之前**执行, 所以在 `resolveRecord()` 中只能通过 `$this->data['column_name']` 读取数据
- `fillRecordUsing()` 在 `rules()` 验证**之后**执行, 此时数据已经通过校验
- `beforeFill` 钩子可用于缓存非目标 Model 的字段, 避免被 `fillRecord()` 写入无效列

# RowImportFailedException 异常作用域

`RowImportFailedException` 的 try-catch 位于 `ImportCsv` Job 层, 包裹了整个 `Importer::__invoke()` 调用。因此在以下**任何位置**抛出均有效, 该行会被标记为失败并记录错误信息到失败 CSV：

- `resolveRecord()` -- 找不到目标记录时
- `fillRecordUsing()` 闭包 -- 关联查询失败时
- 生命周期钩子 (`beforeFill`, `beforeSave`, `afterSave` 等) -- 业务校验不通过时
- `saveRecord()` 重写 -- 捕获底层异常转换为行级错误时

```php
// 示例: 在 fillRecordUsing() 中使用
ImportColumn::make('community_name')
    ->fillRecordUsing(function ($state, $record, $options) {
        $community = Community::query()
            ->where('name', $state)
            ->where('tenant_id', $options['tenant_id'] ?? null)
            ->first();

        if (! $community) {
            throw new RowImportFailedException("找不到社区：{$state}");
        }

        $record->community_id = $community->id;
    }),

// 示例: 在 saveRecord() 中兜底捕获
public function saveRecord(): void
{
    try {
        parent::saveRecord();
    } catch (\Throwable $e) {
        throw new RowImportFailedException('保存记录失败：' . $e->getMessage());
    }
}
```

# 导入关联关系

使用 `relationship()` 方法导入关系。目前，支持 `BelongsTo` 和 `BelongsToMany` 关系。例如，如果您的 CSV 文件中有一个 category 列，则可能需要导入类别 `BelongsTo` 关系

```php
use Filament\Actions\Imports\ImportColumn;

ImportColumn::make('author')
    ->relationship()
```

CSV 文件中的 `author` 列将映射到数据库中的 `author_id` 列。CSV 文件中应包含作者的主键，通常为 `id` 。

如果该列有值，但找不到作者，则导入将验证失败。Filament 会自动为所有关系列添加验证，以确保关系在需要时不为空。

## 自定义关系导入解决方案

要使用不同的列查找相关记录，可以将列名传递为 `resolveUsing`

```php
use Filament\Actions\Imports\ImportColumn;

ImportColumn::make('author')
    ->relationship(resolveUsing: 'email')
```

## 复杂的自定义解析过程

可以通过向 `resolveUsing` 传递一个函数来自定义解析过程，该函数应返回与关系关联的记录

```php
use App\Models\Author;
use Filament\Actions\Imports\ImportColumn;

ImportColumn::make('author')
    ->relationship(resolveUsing: function (string $state): ?Author {
        return Author::query()
            ->where('email', $state)
            ->orWhere('username', $state)
            ->first();
    })
```

## relationship() 与 fillRecordUsing() 选择边界

### 决策矩阵

| 场景 | 推荐方法 | 原因 |
|------|----------|------|
| BelongsTo/BelongsToMany + 简单主键匹配 | `relationship()` | 自动处理验证和外键赋值 |
| BelongsTo/BelongsToMany + 按名称/其他字段查找 | `relationship(resolveUsing: 'column')` | 简洁，自动验证 |
| BelongsTo/BelongsToMany + 多租户/复杂条件 | `relationship(resolveUsing: fn)` | 闭包可注入 `$options` 获取 tenant_id |
| HasMany/MorphTo 等其他关系 | `fillRecordUsing()` | `relationship()` 不支持 |
| 需要自定义错误消息 | `fillRecordUsing()` | 可抛出 `RowImportFailedException` 自定义消息 |

### 关键区别

**relationship() 的优势：**
- 自动添加存在性验证（找不到时返回标准验证错误）
- 自动处理外键赋值（无需手动 `$record->xxx_id = ...`）
- 支持结果缓存（相同值不重复查询）

**fillRecordUsing() 的优势：**
- 可抛出 `RowImportFailedException` 自定义错误消息
- 支持任意关联类型
- 完全控制赋值逻辑

### 多租户场景示例

BelongsTo 关系需要多租户条件过滤时，**优先使用 `relationship(resolveUsing: fn)`**：

```php
// 推荐：使用 relationship() + 闭包
ImportColumn::make('category')
    ->label('分类')
    ->requiredMapping()
    ->relationship(resolveUsing: function (string $state, array $options): ?Category {
        return Category::query()
            ->where('name', $state)
            ->where('tenant_id', $options['tenant_id'] ?? null)
            ->first();
    }),
```

如果需要自定义错误消息（如中文提示），则使用 `fillRecordUsing()`：

```php
// 需要自定义错误消息时
ImportColumn::make('category_name')
    ->label('分类')
    ->requiredMapping()
    ->rules(['required', 'string'])
    ->fillRecordUsing(function ($state, $record, $options) {
        $category = Category::query()
            ->where('name', $state)
            ->where('tenant_id', $options['tenant_id'] ?? null)
            ->first();

        if (! $category) {
            throw new RowImportFailedException("找不到分类：{$state}");
        }

        $record->category_id = $category->id;
    }),
```

## 非 BelongsTo/BelongsToMany 关系的处理

当关联关系不是 `BelongsTo` 或 `BelongsToMany` 时 (如 HasMany、MorphTo 等), 不能使用 `relationship()` 方法。此时应在 `fillRecordUsing()` 中手动解析：

```php
ImportColumn::make('fee_type_name')
    ->label('费用类型')
    ->rules(['required', 'string'])
    ->fillRecordUsing(function ($state, $record, $options) {
        $feeType = FeeType::query()
            ->where('name', $state)
            ->where('tenant_id', $options['tenant_id'] ?? null)
            ->first();

        if (! $feeType) {
            throw new RowImportFailedException("找不到费用类型：{$state}");
        }

        $record->fee_type_id = $feeType->id;
    }),
```

# BaseImporter 辅助方法

项目的 `BaseImporter` (`Geekstek\Support\Filament\Imports\BaseImporter`) 提供以下辅助方法, 可在 `castStateUsing()` 或 `fillRecordUsing()` 中使用：

| 方法 | 用途 | 返回值 |
|---|---|---|
| `parseBooleanSafely($state)` | 解析 "是/否/true/false/yes/no/y/n/Y/N" | `?bool` |
| `parseDateSafely($state)` | 解析多种日期格式 (Y-m-d, Y/m/d, d/m/Y 等) | `?string` (Y-m-d) |
| `parseDateTimeSafely($state)` | 解析日期时间 | `?string` (Y-m-d H:i:s) |
| `parseYearMonthSafely($state)` | 解析年月 (2024-01, 2024/01, 2024年1月 等) | `?string` (Y-m) |

以上方法在解析失败时会自动抛出 `RowImportFailedException`, 无需额外处理。

使用示例:

```php
ImportColumn::make('effective_start')
    ->label('生效日期')
    ->castStateUsing(fn ($state) => static::parseDateSafely($state)),

ImportColumn::make('is_active')
    ->label('是否启用')
    ->castStateUsing(fn ($state) => static::parseBooleanSafely($state)),
```

# castStateUsing 最佳实践

## 闭包可注入参数

`castStateUsing()` 闭包支持以下依赖注入参数：

| 参数 | 类型 | 说明 |
|---|---|---|
| `$column` | `ImportColumn` | 当前导入列实例 |
| `$data` | `array` | 当前行处理后的完整数据 |
| `$importer` | `Importer` | 当前 Importer 实例 |
| `$options` | `array` | 导入开始时定义的选项 (如 tenant_id) |
| `$originalData` | `array` | 当前行的原始数据 (处理前) |
| `$originalState` | `mixed` | 当前列的原始数据 (处理前) |
| `$record` | `Model` | 当前正在导入的 Eloquent 记录 (可按具体 Model 类型声明) |
| `$state` | `mixed` | 当前列的值 |

## ✅ 适合

- 字符串转 `int`/`float`/`bool`
- 日期格式解析（Excel 日期 / 字符串日期）
- 去掉空格、统一大小写
- 枚举映射（例如 男/女 -> M/F）
- 处理 N/A / null / 空字符串规则
- 通过名称查 id（例如 部门名称 -> `department_id`）

示例（部门名称转 id）

```php
ImportColumn::make('department')
    ->castStateUsing(fn ($state) => Department::where('name', trim($state))->value('id'));
```

## ❌ 不建议

- 依赖其他字段的逻辑（例如需要同时读 `first_name` + `last_name`）
- 创建 record 或更新 record
- 做业务规则（例如“如果是经理则设置 role”）

因为 `castStateUsing()` 是 column 级别，会导致逻辑分散在多个字段里，不好维护。

## 最佳实践

### 枚举字段处理

```php
// Importer处理顺序 先 castStateUsing 处理转换再验证rules
ImportColumn::make('status')
    ->label('状态')
    ->requiredMapping()
    ->castStateUsing(function ($state): ?string {
        return YYYStatus::fromLabel($state)->value;
    })
    ->rules([
        'required',
        Rule::in(YYYStatus::getValuesArray()),
    ]),
```

### 布尔字段处理

对于 "是/否" 类型字段, 使用 `Rule::in` 验证并在 `fillRecordUsing()` 中转换：

```php
ImportColumn::make('is_active')
    ->label('是否启用')
        ->castStateUsing(function ($state, $record) {
        if (blank($state)) {
            return true; // 默认值
        }

        return $state === '是';
    })
    ->rules([
        'nullable',
        'in:是,否',
    ]),
```

或使用 BaseImporter 的辅助方法简化:

```php
ImportColumn::make('is_active')
    ->label('是否启用')
    ->castStateUsing(function ($state) {
        return static::parseBooleanSafely($state) ?? true;
    })
    ->rules([
        'nullable', 
        Rule::in([
            '是', 
            '否',
            'true', 
            'false'
        ])
    ]),
```

# fillRecordUsing 最佳实践

当默认 fill 行为不够用时，做行级别的赋值逻辑。

它的职责是：

| “我已经知道 record 是谁了，现在我决定怎么把 data 写入它”

## 闭包可注入参数

`fillRecordUsing()` 闭包支持以下依赖注入参数：

| 参数 | 类型 | 说明 |
|---|---|---|
| `$column` | `ImportColumn` | 当前导入列实例 |
| `$data` | `array` | 当前行处理后的完整数据 |
| `$importer` | `Importer` | 当前 Importer 实例 |
| `$options` | `array` | 导入开始时定义的选项 (如 tenant_id) |
| `$originalData` | `array` | 当前行的原始数据 (处理前) |
| `$record` | `Model` | 当前正在导入的 Eloquent 记录 (可按具体 Model 类型声明) |
| `$state` | `mixed` | 当前列的值 |

## ✅ 适合

### 多字段组合写入一个字段

例如 CSV 有 first_name + last_name，数据库只有 name

```php
->fillRecordUsing(function (Model $record, array $data): void {
    $record->name = $data['first_name'] . ' ' . $data['last_name'];
})
```

### 需要 condition 才写入（部分更新）

例如：如果导入值为空，就不要覆盖旧值

```php
->fillRecordUsing(Model $record, array $data): void
{
    if (! blank($data['phone'])) {
        $record->phone = $data['phone'];
    }
}
```

### 写入关联表 / pivot 表

例如导入 user 时，同时同步 roles、tags

```php
->fillRecordUsing(Model $record, array $data): void
{
    $record->fill($data);

    $record->roles()->sync($data['role_ids'] ?? []);
}
```

### 需要业务规则

例如：导入员工时，如果部门是“财务部”则自动标记 finance flag

```php
->fillRecordUsing(Model $record, array $data): void
{
    $record->fill($data);

    $record->is_finance = $data['department_id'] === 3;
}
```

### No-op 列 (仅供 resolveRecord 使用的列)

当某些列仅用于 `resolveRecord()` 中查找/创建关联记录, 不需要写入目标 Model 时, 使用空的 `fillRecordUsing()` 防止自动填充：

```php
ImportColumn::make('community_name')
    ->label('社区名称')
    ->rules(['required', 'string'])
    ->fillRecordUsing(function () {
        // no-op：仅用于 resolveRecord() 中匹配社区，不写入目标 Model
    }),
```

## ❌ 不建议：

- 做字段级转换（应该放到 castStateUsing）

- 查找 record（应该放 resolveRecord）

# 多租户 (Multi-Tenant) 处理

使用 `getOptionsFormComponents()` 传递 tenant_id, 在 `beforeSave()` 中设置：

```php
public static function getOptionsFormComponents(): array
{
    return [
        Hidden::make('tenant_id')
            ->default(Filament::getTenant()?->getKey()),
    ];
}

protected function beforeSave(): void
{
    if ($this->options['tenant_id'] ?? false) {
        $this->getRecord()->tenant_id = $this->options['tenant_id'];
    }
}
```

在 `resolveRecord()` 和 `fillRecordUsing()` 中通过 `$this->options['tenant_id']` 或 `$options['tenant_id']` 访问。

# saveRecord() 异常兜底

重写 `saveRecord()` 将底层异常转换为行级失败消息, 便于在失败 CSV 中记录：

```php
public function saveRecord(): void
{
    try {
        parent::saveRecord();
    } catch (\Throwable $e) {
        throw new RowImportFailedException('保存记录失败：' . $e->getMessage());
    }
}
```

# 复杂 resolveRecord (级联查找/创建)

当导入需要级联创建多层关联时, 在 `resolveRecord()` 中逐级解析, 使用 protected 方法拆分逻辑：

```php
public function resolveRecord(): ?Asset
{
    $community = $this->resolveCommunity();
    $building = $this->resolveBuilding($community);
    $floor = $this->resolveFloor($community, $building);

    return Asset::firstOrNew([
        'tenant_id' => $this->options['tenant_id'],
        'community_id' => $community->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
        'name' => $this->data['name'] ?? '',
    ]);
}

protected function resolveCommunity(): Community
{
    $name = $this->data['community_name'] ?? null;
    if (blank($name)) {
        throw new RowImportFailedException('社区名称不能为空');
    }

    return Community::firstOrCreate(
        ['tenant_id' => $this->options['tenant_id'], 'slug' => $this->data['community_slug']],
        ['name' => $name, 'status' => 'active']
    );
}
```

# 源码模板

- 参考源码模板 `assets\YYYImporter.php`
