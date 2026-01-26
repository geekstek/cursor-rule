<?php

namespace App\Services\Analytics;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class UserGrowthStatisticsService
{
    /**
     * 获取近 30 天用户增长趋势（补全空缺日期）
     * 适合前端 ECharts / Chart.js 直接使用
     */
    public function getRegistrationTrend(Carbon $start, Carbon $end): array
    {
        // 1. 数据库聚合查询 (只查有数据的天)
        // 结果形如: ['2023-10-01' => 5, '2023-10-03' => 2]
        $rawData = User::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date'); // Key是日期, Value是数量

        // 2. 逻辑补全 (Filling Gaps)
        $trend = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $trend[] = [
                'date' => $formattedDate,
                // 如果数据库没查到这天，就填 0
                'value' => $rawData->get($formattedDate, 0),
            ];
        }

        return $trend;
    }
}