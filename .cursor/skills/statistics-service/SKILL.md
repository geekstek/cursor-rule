---
name: statistics-service
description: 统计服务层最佳实践。业务需要任何统计、数据的宏观聚合与分析时使用，主要应用场景仪表盘，统计卡片，图表(Chart)等，都需要使用 Statistics Service（统计服务）编写具体逻辑，提高复用性(Filament Widget 和 API 数据输出)
---

# StatisticsService 定义

# 目的

文件的內容、目標和範圍

`StatisticsService`（统计服务）的核心定位是 **“数据的宏观聚合与分析”**。

Repositories 负责“取回单行或多行数据（Micro View）”，而 StatisticsService 负责“把数据聚合成指标、趋势或报表（Macro View）”。

它的主要应用场景是：**仪表盘 (Dashboard)、报表导出 (Excel)、趋势图表 (Charts)**。

### 1. 核心特征

1. **只读 (Read-Only)**：绝对不修改数据库。
2. **聚合查询 (Aggregations)**：大量使用 `SUM`, `COUNT`, `AVG`, `GROUP BY`。
3. **数据清洗 (Transformation)**：把数据库查出来的原始行，转换成前端图表可以直接用的格式（例如补全日期空缺）。
4. **性能敏感 (Performance)**：通常是 **缓存 (Cache)** 的重度使用者。

### 2. 基础写法：仪表盘核心指标

这是最简单的形式，用于计算“总销售额”、“待办任务数”等。

参考示例代码: `assets/SalesStatisticsService.php`

### 3. 进阶写法：图表数据（时间序列补全）

这是 `StatisticsService` 最有价值的地方。

数据库查出的 `GROUP BY date` 数据通常是不连续的（比如星期三没订单，数据库就没这条记录）。**Service 层负责把“0”补齐**，让前端拿到连续的数组。

参考示例代码: `assets/UserGrowthStatisticsService.php`

### 4. 高级写法：带缓存的复杂报表

统计查询通常很慢。StatisticsService 是放置缓存逻辑的最佳地点，这样 Controller 或 Action 不需要关心数据是从 Redis 拿的还是算出来的。

参考示例代码: `assets/FinanceReportService.php`

### 5. StatisticsService vs Repository

这是最容易混淆的地方：**“这不就是数据库查询吗？为什么不放 Repository？”**

| **维度** | **Repository** | **StatisticsService** |
| --- | --- | --- |
| **返回对象** | **Entity / Model** (如 `User` 对象) | **Scalar / Array / DTO** (如 `int`, `float`, `json`) |
| **关注点** | 增删改查 (CRUD) | 聚合分析 (OLAP) |
| **SQL 特征** | `find`, `save`, `where` | `sum`, `avg`, `group by`, `union` |
| **典型用途** | 业务逻辑处理、事务中 | 报表展示、KPI 计算 |
| **补全逻辑** | 无（数据库啥样就啥样） | 有（补0、格式化、转换单位） |

**示例对比：**

- `OrderRepository::findUnpaid($userId)`  → 返回 `Order[]` 集合（Model）。
- `OrderStatisticsService::getUnpaidCount($userId)`  → 返回 `int` 7。

### 7. 总结

1. **宏观视角**：当你不再关心“某一个订单”，而是关心“订单总数/总额/趋势”时，用 `StatisticsService`。
2. **数据加工厂**：不仅仅是透传数据库结果，还要负责把数据**加工**成前端图表好用的样子（比如补0、转换成 `['label' => [], 'data' => []]` 格式）。
3. **缓存层**：它是做查询缓存的最佳位置。
4. **命名**：`<Domain>StatisticsService` 或 `<Domain>ReportService`。
