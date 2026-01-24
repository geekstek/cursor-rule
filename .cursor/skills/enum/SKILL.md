---
name: create-update-enum-by-migration
description: PHP Enum 类 最佳实践。当编写Enum 类时使用。
---
# 根据Migration 创建/更新 Enum 类

## 概述

检查 Laravel Migration 文件中每个 string 数据类型的字段，判断类似 "类型、种类、状态"的字段，创建 PHP Enum 类。

## 步骤
1. **开发挸范**: 参考 Laravel 12 枚举 (Enum) 开发规范与最佳实践 @.cursor/rules/laravel-enum.mdc
    → if file not found: ERROR 没有找到laravel-enum.mdc

2. **创建或更新Enum类**: 根据枚举 (Enum) 开发规范与最佳实践，创建或更新Enum类。

3. **更新的 Migration 文件**: 修改对应的Migration 字段 加入 default()枚举支持。

## 最佳实践准则

1. Is-a 关系：方法应该回答“我是什么”或“我能做什么”（基于我当前的状态）。
2. 纯函数：方法应该只依赖 this（当前枚举值）或传入的简单参数，不依赖外部数据库、API 或全局状态。
3. 分组逻辑：利用 match 表达式将状态分组（例如哪些状态是“进行中”，哪些是“已结束”）。

## 业务逻辑方法

- 使用 `is` 前缀检查具体状态
- 使用 `can` 前缀检查操作权限
- 使用 `needs` 或 `requires` 前缀检查需求

## PHPDoc 注释
- 为每个公共方法添加简洁的中文注释
- 说明方法的业务含义而不是技术实现

## Rich Enums（充血枚举） 模式

将与“状态”紧密相关的轻量级业务逻辑封装在 Enum 类内部

```php
// ✅ 最佳实践：判断能否支付
public function canPay(): bool
{
    return match($this) {
        self::PENDING, self::APPROVED => true,
        default => false,
    };
}

// ✅ 最佳实践：判断是否是终态（不可再修改）
public function isFinal(): bool
{
    return in_array($this, [self::COMPLETED, self::CANCELLED, self::REJECTED]);
}
```

❌ 错误

- Enum 不应该依赖 DB 或 Model: 例如`return $this === self::APPROVED && User::where(...)->exists();`
- Enum 不应该调用支付网关 API: `Stripe::charge(...);`

## 颜色规范 getColor()

### Filament 标准颜色
- `success` - 成功/完成状态
- `warning` - 警告/待处理状态  
- `danger` - 错误/失败状态
- `info` - 信息/进行中状态
- `gray` - 中性/禁用状态
- `primary` - 主要/重要状态

## RESOURCES

- `assets/enum-template.php`: 最佳实践和代码结构原则

## 图标规范

### Heroicon 图标使用

查看 `Filament\Support\Icons\Heroicon Enum类的可选择Icon

## 国际化支持

默认不编写 国际化, **除非**指明需要翻译语言

### 翻译常量
```php
private const TRANSLATIONS = 'prop-acc::enums/payment-status.';
```

### 翻译方法（可选）
```php
public function getLabel(): ?string
{
    return match ($this) {
        self::PENDING => __(self::TRANSLATIONS . 'labels.pending'),
        self::COMPLETED => __(self::TRANSLATIONS . 'labels.completed'),
    };
}
```