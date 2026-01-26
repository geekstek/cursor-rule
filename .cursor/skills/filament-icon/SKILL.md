---
name: filament-icon
description: 在Filament 后台加入图标规范。使用场景在Filament Resource / Filament Page / Filament Widget 等。
---

# Filament Icon 定义

# 目的

为避免调用错误的 Heroicon 图标(不使用字串), 设计时查看 `Filament\Support\Icons\Heroicon` Enum类的可选择Icon, 并使用类似`Heroicon::User`、`Heroicon::CheckCircle`、`Heroicon::XCircle` 的 Enum Case来调用图标。