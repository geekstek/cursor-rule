---
name: filament-resource
description: 专注于 Filament Resource开发规范。需要开发Filament Resource 时必需遵守。
---

# Filament Resource 开发规范

## 核心原则

- 数据加载, 防止 N+1
- 路由引用：在引用 Filament 路由时，应尽量使用 getUrl() 方法，而不是 Laravel 的 route() 辅助函数。例如，使用 ClassScheduleResource::getUrl('index')，而不是 route('filament.admin.resources.class-schedules.index')。此外，请指定确切的资源类名，而不是使用 getResource()。
- 避免生成不必要的页面：在生成 Filament 资源时，除非有明确指示，否则不要生成查看页面（View page）或信息列表（Infolist）。
- 枚举（Enum）的使用与接口：当在 Eloquent 模型字段中使用枚举类时，如果尚未添加，请实现 HasLabel、HasColor 和 HasIcon 接口，而不是在 Filament 表单（Forms）/表格（Tables）中手动指定值、标签、颜色或图标。
    - 关键事项：必须始终使用接口定义中的确切返回类型声明——切勿替换为特定类型（例如：getIcon() 应使用 string|BackedEnum|Htmlable|null，而不是 string|Heroicon|null）。
    - 在使用枚举定义默认值时，切勿添加 ->value。
    - 请参考此文档页面：https://filamentphp.com/docs/4.x/advanced/enums
- 优先使用枚举对象：只要存在枚举类，请始终尽可能使用枚举对象而不是硬编码的字符串值。例如，在测试中创建数据时，如果字段被转换（cast）为枚举，则应使用该枚举对象，而不是硬编码的字符串。
- 图标的使用：在添加图标时，始终使用 Filament 枚举类 Filament\Support\Icons\Heroicon，而不是使用字符串。
- 操作授权：在添加需要授权的操作（actions）时，请在操作对象上使用 ->authorize('ability') 方法，而不是手动调用 Gate::authorize() 或检查 Gate::allows()。authorize() 方法会自动处理授权强制执行以及操作的可见性。
- 唯一性验证默认值：在 Filament v4 中，验证规则 unique() 默认已包含 ignoreRecord: true，因此无需额外指定它。
- 代码示例 `assets\YYYResource.php`

## 目录结构规范

### Resource 目录组织
```
Resources/
├── ResourceName/
│   ├── ResourceNameResource.php     # 主资源类
│   ├── Actions/                     # 自定义操作
│   │   └── CustomAction.php
│   ├── Exports/                     # 导出类
│   │   └── ResourceNameExporter.php 
│   ├── Imports/                     # 导入类
│   │   └── ResourceNameImporter.php
│   ├── Pages/                       # 页面类 
│   │   ├── CreateResourceName.php   # 新增页
│   │   ├── EditResourceName.php     # 编辑页
│   │   ├── ListResourceNames.php    # 列表页
│   │   └── ViewResourceName.php     # 详情页
│   ├── RelationManagers/            # 关联管理器 
│   │   └── RelatedResourceManager.php # 
│   ├── Schemas/                     
│   │   ├── ResourceNameForm.php     # 表单定义类
│   │   └── ResourceNameInfolist.php # 详情定义类
│   └── Tables/                       
│       └── ResourceNamesTable.php  # 表格定义类
```

### 2. 文件命名规范
- **Resource 类**: `{ModelName}Resource.php`
- **页面类**: `{Action}{ModelName}.php` (如 `CreateEmployeeProfile.php`)
- **Schema 类**: `{ModelName}{Type}.php` (如 `EmployeeProfileForm.php`)
- **Table 类**: `{ModelName}sTable.php` (复数形式)
- **Manager 类**: `{RelationName}Manager.php`
- **Importer 类**: `{ModelName}Importer.php`
- **Exporter 类**: `{ModelName}Exporter.php`

## `getPages` 方法

以下示例代表 `index` / `create` / `edit` / `view` 都有独立的 路由可以访问

```php
public static function getPages(): array
{
    return [
        'index' => ListYYY::route('/'),
        'create' => CreateYYY::route('/create'),
        'edit' => EditYYY::route('/{record}/edit'),
        'view' => ViewYYY::route('/{record}'),
    ];
}
```

但对于 `Form` / `Infolist` 的字段量少时，强烈建议不在 `getPages()` 数组中注册 `create` 、 `edit` 、 `view`，没有独立路由时代表使用 **Modal 弹窗**，这样页面不会留白太多，可以提升用户体验

注意: 使用 **Modal 弹窗**时，Filament 不会再使用 `CreateRecord`、`EditRecord`、 `ViewRecord` 类，即代表 当中的 `mutateFormDataBeforeCreate` `handleRecordCreation` `mutateFormDataBeforeFill` `handleRecordUpdate` 等方法不会生效，如果业务复杂时需要用到以上方法，要在在 对应的 CreateAction(通常在ListRecord类中找到) / EditAction / ViewEdit(在Table 定义类的`recordActions`中找到) 编写业务逻辑

注意: 如果资源有Filament Relation Manager时，View 没有独立路由 而使用弹窗时，Relation Manager不会显示，所以有Filament Relation Manager时，需要在注册 view 数组

## 全局搜索

在 Resource 中关于全局搜索的方法 `getGlobalSearchResultTitle`、`getGloballySearchableAttributes`、`getGlobalSearchResultDetails`、`getGlobalSearchEloquentQuery`、`getGlobalSearchResultUrl`、`getGlobalSearchResultActions`，不是全部 Resource 都需要, 请判断后才加入

## 国际化规范

### 1. 翻译键命名
```php
// 资源翻译文件结构
resources/lang/zh_CN/resources/model-name.php

return [
    'labels' => [
        'navigation_label' => '导航标签',
        'model_label' => '模型标签',
        'plural_model_label' => '复数模型标签',
    ],
    'sections' => [
        'section_name' => [
            'label' => '分组标签',
        ],
    ],
    'fields' => [
        'field_name' => [
            'label' => '字段标签',
            'helper' => '字段帮助文本',
        ],
    ],
];
```

## 权限控制

```php
use Geekstek\Uacl\Attributes\ResourcePermissions;
use Geekstek\Uacl\Concerns\HasResourcePermission;

#[ResourcePermissions(permissions: [
    'view-any',
    'view',
    'update',
    'delete',
    'delete-any',
    'import',
    'export',
    'activity-log',
])]
class BillResource extends Resource
{
    use HasResourcePermission;

    // ...
}
```

