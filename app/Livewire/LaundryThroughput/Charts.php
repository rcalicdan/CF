<?php

namespace App\Livewire\LaundryThroughput;

use App\ActionService\PdfReportService;
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

    public function generateCsvReport()
    {
        try {
            $reportData = $this->preparePdfReportData();
            $csvService = new \App\ActionService\LaundryThroughputCsvReportService();
            $filename = $csvService->generateLaundryThroughputReport($reportData);

            $this->dispatch('download-csv', ['filename' => $filename]);

            session()->flash('message', 'Raport CSV został wygenerowany pomyślnie.');
        } catch (\Exception $e) {
            \Log::error('LaundryThroughput CSV Error: ' . $e->getMessage());
            session()->flash('error', 'Błąd podczas generowania raportu CSV: ' . $e->getMessage());
        }
    }

    public function generatePdfReport()
    {
        try {
            $reportData = $this->preparePdfReportData();
            $pdfService = new PdfReportService();
            $filename = $pdfService->generateLaundryThroughputReport($reportData);

            $this->dispatch('download-pdf-route', ['filename' => $filename]);

            session()->flash('message', 'Raport został wygenerowany pomyślnie.');
        } catch (\Exception $e) {
            \Log::error('LaundryThroughput PDF Error: ' . $e->getMessage());
            session()->flash('error', 'Błąd podczas generowania raportu: ' . $e->getMessage());
        }
    }

    private function preparePdfReportData(): array
    {
        return [
            'summary' => $this->getSummaryData(),
            'dailyData' => $this->getThroughputData('day', 30),
            'weeklyData' => $this->getThroughputData('week', 12),
            'monthlyData' => $this->getThroughputData('month', 12),
            'revenueDaily' => $this->getRevenueData('day', 30),
            'revenueWeekly' => $this->getRevenueData('week', 12),
            'revenueMonthly' => $this->getRevenueData('month', 12),
            'clientPerformance' => $this->getClientPerformanceData(),
        ];
    }

    private function getSummaryData(): array
    {
        return [
            'current_month_carpets' => $this->totalCurrentMonthCarpets,
            'previous_month_carpets' => $this->totalPreviousMonthCarpets,
            'carpets_change_percentage' => $this->percentageChangeCarpets,
            'current_month_revenue' => $this->totalRevenueCurrentMonth,
            'previous_month_revenue' => $this->totalRevenuePreviousMonth,
            'revenue_change_percentage' => $this->percentageChangeRevenue,
            'avg_order_value' => $this->avgOrderValue,
            'weekly_trend' => $this->weeklyTrend,
            'monthly_trend' => $this->monthlyTrend,
            'yearly_change' => $this->yearlyChange,
        ];
    }

    private function getThroughputData(string $period, int $count): array
    {
        $startDate = match ($period) {
            'day' => Carbon::now()->subDays($count)->startOfDay(),
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
                SUM(total_area) as total_area_sum,
                AVG(CASE WHEN height > 0 AND width > 0 THEN (height * width) END) as avg_carpet_size,
                SUM(CASE WHEN height > 0 AND width > 0 THEN (height * width) END) as total_carpet_area
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
                    'day' => $date->format('d.m.Y'),
                    'week' => 'W' . $date->weekOfYear . ' ' . $date->format('y'),
                    'month' => $date->format('M Y'),
                    'year' => $date->format('Y'),
                },
                'full_name' => match ($period) {
                    'day' => $date->format('d F Y'),
                    'week' => $date->format('d.m') . ' - ' . $date->endOfWeek()->format('d.m.Y'),
                    'month' => $date->format('F Y'),
                    'year' => 'Rok ' . $date->format('Y'),
                },
                'value' => (int) $item->total_processed,
                'completed_count' => (int) $item->completed_count,
                'avg_area' => round((float) $item->avg_area, 2),
                'total_area' => round((float) $item->total_area_sum, 2),
                'avg_carpet_size' => round((float) $item->avg_carpet_size, 2),
                'total_carpet_area' => round((float) $item->total_carpet_area, 2),
                'completion_rate' => $item->total_processed > 0 ? round(($item->completed_count / $item->total_processed) * 100, 1) : 0,
                'weight_estimate' => round((float) $item->total_area_sum * 2.5, 2), // Assuming 2.5kg per m²
            ];
        })->toArray();
    }

    private function getRevenueData(string $period, int $count): array
    {
        $startDate = match ($period) {
            'day' => Carbon::now()->subDays($count)->startOfDay(),
            'week' => Carbon::now()->subWeeks($count)->startOfWeek(),
            'month' => Carbon::now()->subMonths($count)->startOfMonth(),
            'year' => Carbon::now()->subYears($count)->startOfYear(),
        };

        $trunc_sql = "DATE_TRUNC('$period', updated_at)";

        $data = Order::query()
            ->selectRaw("
                {$trunc_sql} as period_start,
                SUM(total_amount) as total_revenue,
                COUNT(*) as order_count,
                AVG(total_amount) as avg_order_value
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
                    'day' => $date->format('d.m.Y'),
                    'week' => 'W' . $date->weekOfYear . ' ' . $date->format('y'),
                    'month' => $date->format('M Y'),
                    'year' => $date->format('Y'),
                },
                'full_name' => match ($period) {
                    'day' => $date->format('d F Y'),
                    'week' => $date->format('d.m') . ' - ' . $date->endOfWeek()->format('d.m.Y'),
                    'month' => $date->format('F Y'),
                    'year' => 'Rok ' . $date->format('Y'),
                },
                'value' => round((float) $item->total_revenue, 2),
                'order_count' => (int) $item->order_count,
                'avg_order_value' => round((float) $item->avg_order_value, 2),
            ];
        })->toArray();
    }

    private function getClientPerformanceData(): array
    {
        return Order::with(['client', 'orderCarpets'])
            ->whereHas('client')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->get()
            ->groupBy('client_id')
            ->map(function ($orders, $clientId) {
                $client = $orders->first()->client;
                if (!$client) {
                    return null;
                }

                $orderCarpets = $orders->flatMap->orderCarpets;

                return [
                    'client_name' => $client->full_name ?: ($client->first_name . ' ' . $client->last_name),
                    'city' => $client->city ?? 'N/A',
                    'order_count' => $orders->count(),
                    'carpet_count' => $orderCarpets->count(),
                    'total_area' => (float) $orderCarpets->sum('total_area'),
                    'total_revenue' => (float) $orders->sum('total_amount'),
                    'avg_order_value' => (float) $orders->avg('total_amount'),
                    'avg_carpet_area' => (float) $orderCarpets->avg('total_area'),
                    'weight_estimate' => (float) $orderCarpets->sum('total_area') * 2.5,
                ];
            })
            ->filter()
            ->sortByDesc('total_revenue')
            ->take(20)
            ->values()
            ->map(function ($item) {
                return [
                    'client_name' => $item['client_name'],
                    'city' => $item['city'],
                    'order_count' => (int) $item['order_count'],
                    'carpet_count' => (int) $item['carpet_count'],
                    'total_area' => round($item['total_area'], 2),
                    'total_revenue' => round($item['total_revenue'], 2),
                    'avg_order_value' => round($item['avg_order_value'], 2),
                    'avg_carpet_area' => round($item['avg_carpet_area'], 2),
                    'weight_estimate' => round($item['weight_estimate'], 2),
                ];
            })
            ->toArray();
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
            ->sum('total_amount') ?? 0;

        $this->totalRevenuePreviousMonth = Order::where('status', OrderStatus::DELIVERED->value)
            ->whereBetween('updated_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount') ?? 0;

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
        try {
            $results = DB::table('orders')
                ->join('drivers', 'orders.assigned_driver_id', '=', 'drivers.id')
                ->join('users', 'drivers.user_id', '=', 'users.id')
                ->select([
                    DB::raw('users.id as user_id'),
                    DB::raw('users.first_name'),
                    DB::raw('users.last_name'),
                    DB::raw('COUNT(orders.id) as order_count')
                ])
                ->where('orders.created_at', '>=', Carbon::now()->startOfMonth())
                ->whereNotNull('orders.assigned_driver_id')
                ->whereNotNull('users.first_name')
                ->where('users.first_name', '!=', '')
                ->groupBy('users.id', 'users.first_name', 'users.last_name')
                ->orderBy('order_count', 'desc')
                ->limit(5)
                ->get();


            return $results->map(function ($item) {
                $firstName = trim($item->first_name ?? '');
                $lastName = trim($item->last_name ?? '');

                if (empty($firstName) && empty($lastName)) {
                    $driverName = 'Nieznany kierowca';
                } elseif (empty($firstName)) {
                    $driverName = $lastName;
                } elseif (empty($lastName)) {
                    $driverName = $firstName;
                } else {
                    $driverName = $firstName . ' ' . $lastName;
                }


                return [
                    'label' => $driverName,
                    'value' => (int) $item->order_count
                ];
            })->filter(function ($item) {
                return !empty($item['label']) && $item['label'] !== 'N/A';
            })->values()->toArray();
        } catch (\Exception $e) {
            \Log::error('Error in getDriverPerformanceData: ' . $e->getMessage());
            return [];
        }
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
            $this->monthlyTrend = $change > 0 ? "Wzrost o {$change}" : ($change < 0 ? "Spadek o " . abs($change) : 'Bez zmian');
        }

        $yearlyData = $this->chartData['throughput']['yearly'] ?? [];
        if (count($yearlyData) >= 2) {
            $lastTwo = array_slice($yearlyData, -2);
            $changePercent = $lastTwo[0]['value'] > 0 ?
                (($lastTwo[1]['value'] - $lastTwo[0]['value']) / $lastTwo[0]['value']) * 100 : ($lastTwo[1]['value'] > 0 ? 100 : 0);
            $this->yearlyChange = ($changePercent >= 0 ? '+' : '') . round($changePercent, 1) . '%';
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
