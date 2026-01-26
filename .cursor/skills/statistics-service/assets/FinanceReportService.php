<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\Cache;
use App\Models\Transaction;

class FinanceReportService
{
    /**
     * 获取月度财务报表（缓存 1 小时）
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        $cacheKey = "report_finance_{$year}_{$month}";

        // 使用 Cache::remember 自动处理缓存命中/失效
        return Cache::remember($cacheKey, 3600, function () use ($year, $month) {
            return $this->calculateHeavyReport($year, $month);
        });
    }

    protected function calculateHeavyReport($year, $month): array
    {
        // ... 此处可能包含几百行复杂的 SQL 逻辑 ...
        // ... 跨表 Join、复杂的过滤等 ...
        
        return [
            'income' => ...,
            'expense' => ...,
            'profit' => ...,
            'details' => ...
        ];
    }
}