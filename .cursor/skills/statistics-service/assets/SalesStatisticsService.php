<?php

namespace App\Services\Analytics;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Carbon;

class SalesStatisticsService
{
    /**
     * 获取今日实时概览数据
     * 返回数组或简单的 DTO
     */
    public function getDailyOverview(): array
    {
        $today = Carbon::today();

        return [
            'total_revenue' => Order::whereDate('created_at', $today)
                ->where('status', OrderStatus::PAID)
                ->sum('amount'),
                
            'order_count' => Order::whereDate('created_at', $today)
                ->count(),
                
            'average_ticket' => Order::whereDate('created_at', $today)
                ->where('status', OrderStatus::PAID)
                ->avg('amount') ?? 0.00,
        ];
    }
}