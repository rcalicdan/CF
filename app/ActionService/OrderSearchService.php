<?php

namespace App\ActionService;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderSearchService
{
    public function getOrdersByScheduleDate(string $scheduleDate, int $perPage = 15): LengthAwarePaginator
    {
        $date = Carbon::parse($scheduleDate)->toDateString();
        
        return Order::with([
            'client',
            'driver.user',
            'priceList',
            'orderCarpets.services',
            'orderServices'
        ])
        ->whereDate('schedule_date', $date)
        ->orderBy('id', 'asc')
        ->paginate($perPage);
    }
}