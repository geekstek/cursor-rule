---
name: composer-checklist
description: Composer checklist to ensure dependencies are properly installed
disable-model-invocation: true
---
# Composer 清单

## 概述

确保依赖正常安装

## 审查类别

### require

- [ ] 确保 require 安装好以下的依赖，如有缺失，请安装上:
    - "geekstek/support": "^2.0"
    - "geekstek/uacl": "^2.0"

### require-dev
- [ ] 确保 require-dev 没有以下依赖，如发现已安装，请移除:
    - "phpunit/phpunit"
- [ ] 确保 require-dev 安装好以下的依赖，如有缺失，请安装上
    - "ergebnis/composer-normalize": "^2.48"
    - "geekstek/code-generator": "^2.0"
    - "laravel/pint": "^1.24"
    - "pestphp/pest": "^4.0"
    - "pestphp/pest-plugin-laravel": "^4.0"
    - "roave/security-advisories": "dev-latest"

### config

- [ ] 确保 config 留有以下配置:
```
"config": {
    // ...
    "allow-plugins": {
        "ergebnis/composer-normalize": true,
        "pestphp/pest-plugin": true,
        "phpstan/extension-installer": true
    }
},
```

### scripts
- [ ] 确保 scripts 定义好以下命令，如发现缺失，请补回:
    - "post-autoload-dump": "@php ./vendor/bin/testbench package:discover --ansi"
    - "analyse": "vendor/bin/phpstan analyse"
    - "format": "vendor/bin/pint"
    - "test": "vendor/bin/pest"
    - "test-coverage": "vendor/bin/pest --coverage"

## 最后

- [ ] 如有修改 `composer.json` 文件，执行 `composer update` 并确没有问题，如有问题请提供建议及修复。
- [ ] 执行 `composer normalize`
