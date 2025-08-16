<?php

namespace App\Livewire\LaundryThroughput;

use App\Models\OrderCarpet;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Carbon;
use Livewire\Component;
use DB;

class Charts extends Component
{
    public string $activeTab = 'weekly';
    public array $chartData = [
        'weekly' => [],
        'monthly' => [],
        'yearly' => [],
        'statusBreakdown' => [],
    ];
    public float $totalCurrentMonth = 0;
    public float $totalPreviousMonth = 0;
    public float $percentageChange = 0;
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
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $this->totalCurrentMonth = OrderCarpet::query()
            ->whereIn('status', [
                OrderCarpetStatus::COMPLETED->value,
                OrderCarpetStatus::DELIVERED->value
            ])
            ->where('updated_at', '>=', $currentMonth)
            ->count();

        $this->totalPreviousMonth = OrderCarpet::query()
            ->whereIn('status', [
                OrderCarpetStatus::COMPLETED->value,
                OrderCarpetStatus::DELIVERED->value
            ])
            ->whereBetween('updated_at', [$previousMonth, $previousMonthEnd])
            ->count();

        if ($this->totalPreviousMonth > 0) {
            $this->percentageChange = (($this->totalCurrentMonth - $this->totalPreviousMonth) / $this->totalPreviousMonth) * 100;
        } else {
            $this->percentageChange = $this->totalCurrentMonth > 0 ? 100 : 0;
        }
    }

    private function loadChartData()
    {
        $this->chartData['weekly'] = $this->getWeeklyData();
        $this->chartData['monthly'] = $this->getMonthlyData();
        $this->chartData['yearly'] = $this->getYearlyData();
        $this->chartData['statusBreakdown'] = $this->getStatusBreakdownData();
    }

    private function getWeeklyData(): array
    {
        $startDate = Carbon::now()->subWeeks(12)->startOfWeek();
        
        $data = OrderCarpet::query()
            ->selectRaw("
                DATE_TRUNC('week', updated_at) as period,
                COUNT(*) as total_processed,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as completed_count,
                AVG(total_area) as avg_area,
                MIN(updated_at) as period_start
            ", [
                OrderCarpetStatus::COMPLETED->value,
                OrderCarpetStatus::DELIVERED->value
            ])
            ->where('updated_at', '>=', $startDate)
            ->whereNotNull('updated_at')
            ->groupBy(DB::raw("DATE_TRUNC('week', updated_at)"))
            ->orderBy('period')
            ->get();

        return $data->map(function ($item) {
            $weekStart = Carbon::parse($item->period_start);
            return [
                'label' => 'W' . $weekStart->weekOfYear . ' ' . $weekStart->format('Y'),
                'full_name' => $weekStart->format('d.m') . ' - ' . $weekStart->endOfWeek()->format('d.m.Y'),
                'value' => (int) $item->total_processed,
                'completed_count' => (int) $item->completed_count,
                'avg_area' => round((float) $item->avg_area, 2),
                'completion_rate' => $item->total_processed > 0 ? round(($item->completed_count / $item->total_processed) * 100, 1) : 0
            ];
        })->toArray();
    }

    private function getMonthlyData(): array
    {
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();
        
        $data = OrderCarpet::query()
            ->selectRaw("
                DATE_TRUNC('month', updated_at) as period,
                COUNT(*) as total_processed,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as completed_count,
                AVG(total_area) as avg_area,
                SUM(total_area) as total_area_sum,
                MIN(updated_at) as period_start
            ", [
                OrderCarpetStatus::COMPLETED->value,
                OrderCarpetStatus::DELIVERED->value
            ])
            ->where('updated_at', '>=', $startDate)
            ->whereNotNull('updated_at')
            ->groupBy(DB::raw("DATE_TRUNC('month', updated_at)"))
            ->orderBy('period')
            ->get();

        return $data->map(function ($item) {
            $monthStart = Carbon::parse($item->period_start);
            return [
                'label' => $monthStart->format('M Y'),
                'full_name' => $monthStart->format('F Y'),
                'value' => (int) $item->total_processed,
                'completed_count' => (int) $item->completed_count,
                'avg_area' => round((float) $item->avg_area, 2),
                'total_area' => round((float) $item->total_area_sum, 2),
                'completion_rate' => $item->total_processed > 0 ? round(($item->completed_count / $item->total_processed) * 100, 1) : 0
            ];
        })->toArray();
    }

    private function getYearlyData(): array
    {
        $startDate = Carbon::now()->subYears(5)->startOfYear();
        
        $data = OrderCarpet::query()
            ->selectRaw("
                DATE_TRUNC('year', updated_at) as period,
                COUNT(*) as total_processed,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as completed_count,
                AVG(total_area) as avg_area,
                SUM(total_area) as total_area_sum,
                MIN(updated_at) as period_start
            ", [
                OrderCarpetStatus::COMPLETED->value,
                OrderCarpetStatus::DELIVERED->value
            ])
            ->where('updated_at', '>=', $startDate)
            ->whereNotNull('updated_at')
            ->groupBy(DB::raw("DATE_TRUNC('year', updated_at)"))
            ->orderBy('period')
            ->get();

        return $data->map(function ($item) {
            $yearStart = Carbon::parse($item->period_start);
            return [
                'label' => $yearStart->format('Y'),
                'full_name' => 'Rok ' . $yearStart->format('Y'),
                'value' => (int) $item->total_processed,
                'completed_count' => (int) $item->completed_count,
                'avg_area' => round((float) $item->avg_area, 2),
                'total_area' => round((float) $item->total_area_sum, 2),
                'completion_rate' => $item->total_processed > 0 ? round(($item->completed_count / $item->total_processed) * 100, 1) : 0
            ];
        })->toArray();
    }

    private function getStatusBreakdownData(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        $data = OrderCarpet::query()
            ->selectRaw("
                status,
                COUNT(*) as count,
                AVG(total_area) as avg_area
            ")
            ->where('updated_at', '>=', $currentMonth)
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        $total = $data->sum('count');

        return $data->map(function ($item) use ($total) {
            $status = OrderCarpetStatus::tryFrom($item->status);
            $statusLabel = $status ? $status->getLabel() : 'Nieznany';
            
            return [
                'label' => $statusLabel,
                'value' => (int) $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
                'avg_area' => round((float) $item->avg_area, 2)
            ];
        })->toArray();
    }
    // ... (calculateTrends, updatedActiveTab, render remain the same)
    private function calculateTrends()
    {
        // Weekly trend calculation
        $weeklyData = $this->chartData['weekly'] ?? [];
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

        $monthlyData = $this->chartData['monthly'] ?? [];
        if (count($monthlyData) >= 2) {
            $lastTwo = array_slice($monthlyData, -2);
            if (count($lastTwo) == 2) {
                $change = $lastTwo[1]['value'] - $lastTwo[0]['value'];
                if ($change > 0) {
                    $this->monthlyTrend = "Wzrost o {$change} dywanów";
                } elseif ($change < 0) {
                    $this->monthlyTrend = "Spadek o " . abs($change) . " dywanów";
                } else {
                    $this->monthlyTrend = 'Bez zmian';
                }
            }
        }

        $yearlyData = $this->chartData['yearly'] ?? [];
        if (count($yearlyData) >= 2) {
            $lastTwo = array_slice($yearlyData, -2);
            if (count($lastTwo) == 2) {
                $changePercent = $lastTwo[0]['value'] > 0 ? 
                    (($lastTwo[1]['value'] - $lastTwo[0]['value']) / $lastTwo[0]['value']) * 100 : 0;
                $this->yearlyChange = ($changePercent >= 0 ? '+' : '') . round($changePercent, 1) . '%';
            }
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