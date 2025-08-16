<?php

namespace App\Livewire\LaundryThroughput;

use App\Enums\OrderCarpetStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderCarpet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Charts extends Component
{
    public string $activeTab = 'monthly';
    public array $chartData = [
        'throughput' => [],
        'statusBreakdown' => [],
        'revenue' => [],
        'orderStatus' => [],
        'driverPerformance' => [],
    ];

    public float $totalCurrentMonthCarpets = 0;
    public float $totalPreviousMonthCarpets = 0;
    public float $percentageChangeCarpets = 0;

    public float $totalRevenueCurrentMonth = 0;
    public float $totalRevenuePreviousMonth = 0;
    public float $percentageChangeRevenue = 0;
    public float $avgOrderValue = 0;

    public string $weeklyTrend = 'Stable';
    public string $monthlyTrend = 'Stable';
    public string $yearlyChange = '0%';

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->calculateSummaryStats();
        $this->loadChartData();
        $this->calculateTrends();

        $this->dispatch('update-charts', ['data' => $this->chartData]);
    }

    private function calculateSummaryStats()
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $this->totalCurrentMonthCarpets = OrderCarpet::whereIn('status', [OrderCarpetStatus::COMPLETED->value, OrderCarpetStatus::DELIVERED->value])
            ->where('updated_at', '>=', $currentMonthStart)
            ->count();

        $this->totalPreviousMonthCarpets = OrderCarpet::whereIn('status', [OrderCarpetStatus::COMPLETED->value, OrderCarpetStatus::DELIVERED->value])
            ->whereBetween('updated_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $this->percentageChangeCarpets = $this->totalPreviousMonthCarpets > 0
            ? (($this->totalCurrentMonthCarpets - $this->totalPreviousMonthCarpets) / $this->totalPreviousMonthCarpets) * 100
            : ($this->totalCurrentMonthCarpets > 0 ? 100 : 0);

        $this->totalRevenueCurrentMonth = Order::where('status', OrderStatus::DELIVERED->value)
            ->where('updated_at', '>=', $currentMonthStart)
            ->sum('total_amount');

        $this->totalRevenuePreviousMonth = Order::where('status', OrderStatus::DELIVERED->value)
            ->whereBetween('updated_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount');

        $this->percentageChangeRevenue = $this->totalRevenuePreviousMonth > 0
            ? (($this->totalRevenueCurrentMonth - $this->totalRevenuePreviousMonth) / $this->totalRevenuePreviousMonth) * 100
            : ($this->totalRevenueCurrentMonth > 0 ? 100 : 0);

        $currentMonthOrderCount = Order::where('updated_at', '>=', $currentMonthStart)->count();
        $this->avgOrderValue = $currentMonthOrderCount > 0 ? $this->totalRevenueCurrentMonth / $currentMonthOrderCount : 0;
    }

    private function loadChartData()
    {
        $this->chartData['throughput']['weekly'] = $this->getThroughputData('week', 12);
        $this->chartData['throughput']['monthly'] = $this->getThroughputData('month', 12);
        $this->chartData['throughput']['yearly'] = $this->getThroughputData('year', 5);
        $this->chartData['statusBreakdown'] = $this->getStatusBreakdownData();
        $this->chartData['revenue']['weekly'] = $this->getRevenueData('week', 12);
        $this->chartData['revenue']['monthly'] = $this->getRevenueData('month', 12);
        $this->chartData['revenue']['yearly'] = $this->getRevenueData('year', 5);
        $this->chartData['orderStatus'] = $this->getOrderStatusData();
        $this->chartData['driverPerformance'] = $this->getDriverPerformanceData();
    }

    private function getThroughputData(string $period, int $count): array
    {
        $startDate = match ($period) {
            'week' => Carbon::now()->subWeeks($count)->startOfWeek(),
            'month' => Carbon::now()->subMonths($count)->startOfMonth(),
            'year' => Carbon::now()->subYears($count)->startOfYear(),
        };

        $trunc_sql = "DATE_TRUNC('$period', updated_at)";

        $data = OrderCarpet::query()
            ->selectRaw("
                {$trunc_sql} as period_start,
                COUNT(*) as total_processed,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as completed_count,
                AVG(total_area) as avg_area,
                SUM(total_area) as total_area_sum
            ", [OrderCarpetStatus::COMPLETED->value, OrderCarpetStatus::DELIVERED->value])
            ->where('updated_at', '>=', $startDate)
            ->whereNotNull('updated_at')
            ->groupBy('period_start')
            ->orderBy('period_start')
            ->get();

        return $data->map(function ($item) use ($period) {
            $date = Carbon::parse($item->period_start);
            return [
                'label' => match ($period) {
                    'week' => 'W'.$date->weekOfYear.' '.$date->format('y'),
                    'month' => $date->format('M Y'),
                    'year' => $date->format('Y'),
                },
                'full_name' => match ($period) {
                    'week' => $date->format('d.m').' - '.$date->endOfWeek()->format('d.m.Y'),
                    'month' => $date->format('F Y'),
                    'year' => 'Rok '.$date->format('Y'),
                },
                'value' => (int) $item->total_processed,
                'completed_count' => (int) $item->completed_count,
                'avg_area' => round((float) $item->avg_area, 2),
                'total_area' => round((float) $item->total_area_sum, 2),
                'completion_rate' => $item->total_processed > 0 ? round(($item->completed_count / $item->total_processed) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function getStatusBreakdownData(): array
    {
        $data = OrderCarpet::selectRaw('status, COUNT(*) as count, AVG(total_area) as avg_area')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        $total = $data->sum('count');

        return $data->map(function ($item) use ($total) {
            $status = OrderCarpetStatus::tryFrom($item->status);
            return [
                'label' => $status ? $status->getLabel() : 'Nieznany',
                'value' => (int) $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
                'avg_area' => round((float) $item->avg_area, 2),
            ];
        })->toArray();
    }

    private function getRevenueData(string $period, int $count): array
    {
        $startDate = match ($period) {
            'week' => Carbon::now()->subWeeks($count)->startOfWeek(),
            'month' => Carbon::now()->subMonths($count)->startOfMonth(),
            'year' => Carbon::now()->subYears($count)->startOfYear(),
        };

        $trunc_sql = "DATE_TRUNC('$period', updated_at)";

        $data = Order::query()
            ->selectRaw("
                {$trunc_sql} as period_start,
                SUM(total_amount) as total_revenue
            ")
            ->where('status', OrderStatus::DELIVERED->value)
            ->where('updated_at', '>=', $startDate)
            ->groupBy('period_start')
            ->orderBy('period_start')
            ->get();

        return $data->map(function ($item) use ($period) {
            $date = Carbon::parse($item->period_start);
            return [
                'label' => match ($period) {
                    'week' => 'W'.$date->weekOfYear.' '.$date->format('y'),
                    'month' => $date->format('M Y'),
                    'year' => $date->format('Y'),
                },
                'value' => round((float) $item->total_revenue, 2),
            ];
        })->toArray();
    }

    private function getOrderStatusData(): array
    {
        $data = Order::select('status', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        $total = $data->sum('count');

        return $data->map(function ($item) use ($total) {
            $status = OrderStatus::tryFrom($item->status);
            return [
                'label' => $status ? $status->getLabel() : 'Nieznany',
                'value' => (int) $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function getDriverPerformanceData(): array
    {
        return Order::join('drivers', 'orders.assigned_driver_id', '=', 'drivers.id')
            ->join('users', 'drivers.user_id', '=', 'users.id')
            ->selectRaw("users.first_name || ' ' || users.last_name as driver_name, COUNT(orders.id) as order_count")
            ->where('orders.created_at', '>=', Carbon::now()->startOfMonth())
            ->groupBy('driver_name')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($item) => ['label' => $item->driver_name, 'value' => (int) $item->order_count])
            ->toArray();
    }

    private function calculateTrends()
    {
        $weeklyData = $this->chartData['throughput']['weekly'] ?? [];
        if (count($weeklyData) >= 2) {
            $recent = array_slice($weeklyData, -4);
            $previous = array_slice($weeklyData, -8, 4);
            $recentAvg = collect($recent)->avg('value');
            $previousAvg = collect($previous)->avg('value');

            if ($previousAvg > 0) {
                $weeklyChangePercent = (($recentAvg - $previousAvg) / $previousAvg) * 100;
                if ($weeklyChangePercent > 5) {
                    $this->weeklyTrend = 'Rosnący trend';
                } elseif ($weeklyChangePercent < -5) {
                    $this->weeklyTrend = 'Malejący trend';
                } else {
                    $this->weeklyTrend = 'Stabilny';
                }
            }
        }

        $monthlyData = $this->chartData['throughput']['monthly'] ?? [];
        if (count($monthlyData) >= 2) {
            $lastTwo = array_slice($monthlyData, -2);
            $change = $lastTwo[1]['value'] - $lastTwo[0]['value'];
            $this->monthlyTrend = $change > 0 ? "Wzrost o {$change}" : ($change < 0 ? "Spadek o ".abs($change) : 'Bez zmian');
        }

        $yearlyData = $this->chartData['throughput']['yearly'] ?? [];
        if (count($yearlyData) >= 2) {
            $lastTwo = array_slice($yearlyData, -2);
            $changePercent = $lastTwo[0]['value'] > 0 ?
                (($lastTwo[1]['value'] - $lastTwo[0]['value']) / $lastTwo[0]['value']) * 100 : ($lastTwo[1]['value'] > 0 ? 100 : 0);
            $this->yearlyChange = ($changePercent >= 0 ? '+' : '').round($changePercent, 1).'%';
        }
    }

    public function updatedActiveTab()
    {
        $this->dispatch('update-charts', ['data' => $this->chartData]);
    }

    public function render()
    {
        return view('livewire.laundry-throughput.charts');
    }
}