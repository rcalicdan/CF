<?php

namespace App\Livewire\ProcessingCosts;

use App\ActionService\CsvReportService;
use App\ActionService\PdfReportService;
use App\Enums\CostType;
use App\Models\OrderCarpet;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class Charts extends Component
{
    public string $activeTab = 'weekly';
    public array $weeklyData = [];
    public array $monthlyData = [];
    public array $yearlyData = [];
    public array $costTypeData = [];

    public function mount()
    {
        $this->loadData();
    }

    public function generatePdf()
    {
        $reportData = [
            'weeklyData' => $this->weeklyData,
            'monthlyData' => $this->monthlyData,
            'yearlyData' => $this->yearlyData,
            'costTypeData' => $this->costTypeData,
            'monthlyComparison' => $this->getMonthlyComparison()
        ];
        $pdfService = new PdfReportService();
        $filename = $pdfService->generateProcessingCostsReport($reportData);
        $this->dispatch('download-pdf', ['filename' => $filename]);
    }

    public function generateCsv()
    {
        $reportData = [
            'weeklyData' => $this->weeklyData,
            'monthlyData' => $this->monthlyData,
            'yearlyData' => $this->yearlyData,
            'costTypeData' => $this->costTypeData,
            'monthlyComparison' => $this->getMonthlyComparison()
        ];
        $csvService = new CsvReportService();
        $filename = $csvService->generateProcessingCostsReport($reportData);
        $this->dispatch('download-csv', ['filename' => $filename]);
    }

    public function updatedActiveTab()
    {
        $this->loadData();
        $this->dispatch('updateCharts', [
            'tab' => $this->activeTab,
            'data' => $this->getChartData()
        ]);
    }

    public function loadData()
    {
        $this->weeklyData = $this->getWeeklyData();
        $this->monthlyData = $this->getMonthlyData();
        $this->yearlyData = $this->getYearlyData();
        $this->costTypeData = $this->getCostTypeData();
    }

    private function getWeeklyData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $costData = DB::table('processing_costs')
            ->selectRaw('
                EXTRACT(DOW FROM cost_date) as day_number,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count,
                AVG(amount) as avg_amount
            ')
            ->whereBetween('cost_date', [$startOfWeek, $endOfWeek])
            ->groupByRaw('EXTRACT(DOW FROM cost_date)')
            ->orderByRaw('EXTRACT(DOW FROM cost_date)')
            ->get()
            ->keyBy('day_number');

        $carpetCountData = OrderCarpet::selectRaw('
                EXTRACT(DOW FROM measured_at) as day_number,
                COUNT(*) as processed_count
            ')
            ->whereBetween('measured_at', [$startOfWeek, $endOfWeek])
            ->whereNotIn('status', [
                OrderCarpetStatus::PENDING->value,
                OrderCarpetStatus::PICKED_UP->value,
                OrderCarpetStatus::AT_LAUNDRY->value,
                OrderCarpetStatus::MEASURED->value
            ])
            ->groupByRaw('EXTRACT(DOW FROM measured_at)')
            ->get()
            ->keyBy('day_number');

        $weekDays = [
            1 => ['short' => 'Pon', 'full' => 'Poniedzialek'],
            2 => ['short' => 'Wt', 'full' => 'Wtorek'],
            3 => ['short' => 'Sr', 'full' => 'Sroda'],
            4 => ['short' => 'Czw', 'full' => 'Czwartek'],
            5 => ['short' => 'Pt', 'full' => 'Piatek'],
            6 => ['short' => 'Sob', 'full' => 'Sobota'],
            0 => ['short' => 'Nd', 'full' => 'Niedziela']
        ];

        $result = [];
        foreach ($weekDays as $dayNum => $dayNames) {
            $costItem = $costData->get($dayNum);
            $carpetItem = $carpetCountData->get($dayNum);

            $totalAmount = $costItem ? (float) $costItem->total_amount : 0;
            $transactionCount = $costItem ? (int) $costItem->transaction_count : 0;
            $avgAmount = $costItem ? (float) $costItem->avg_amount : 0;
            $processedCount = $carpetItem ? (int) $carpetItem->processed_count : 0;

            $avgCostPerCarpet = ($processedCount > 0) ? ($totalAmount / $processedCount) : 0;

            $result[] = [
                'label' => $dayNames['short'],
                'value' => $totalAmount,
                'full_name' => $dayNames['full'],
                'count' => $transactionCount,
                'average' => $avgAmount,
                'processed_count' => $processedCount,
                'avg_cost_per_carpet' => $avgCostPerCarpet
            ];
        }
        return $result;
    }

    private function getMonthlyData(): array
    {
        $currentYear = Carbon::now()->year;

        $costData = DB::table('processing_costs')
            ->selectRaw('
                EXTRACT(MONTH FROM cost_date) as month_number,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count,
                AVG(amount) as avg_amount,
                MAX(amount) as max_amount,
                MIN(amount) as min_amount
            ')
            ->whereRaw('EXTRACT(YEAR FROM cost_date) = ?', [$currentYear])
            ->groupByRaw('EXTRACT(MONTH FROM cost_date)')
            ->orderByRaw('EXTRACT(MONTH FROM cost_date)')
            ->get()
            ->keyBy('month_number');

        $carpetCountData = OrderCarpet::selectRaw('
                EXTRACT(MONTH FROM measured_at) as month_number,
                COUNT(*) as processed_count
            ')
            ->whereRaw('EXTRACT(YEAR FROM measured_at) = ?', [$currentYear])
            ->whereBetween('measured_at', [Carbon::create($currentYear, 1, 1), Carbon::create($currentYear, 12, 31, 23, 59, 59)])
            ->whereNotIn('status', [
                OrderCarpetStatus::PENDING->value,
                OrderCarpetStatus::PICKED_UP->value,
                OrderCarpetStatus::AT_LAUNDRY->value,
                OrderCarpetStatus::MEASURED->value
            ])
            ->groupByRaw('EXTRACT(MONTH FROM measured_at)')
            ->get()
            ->keyBy('month_number');

        $months = [
            1 => ['short' => 'Sty', 'full' => 'Styczen'],
            2 => ['short' => 'Lut', 'full' => 'Luty'],
            3 => ['short' => 'Mar', 'full' => 'Marzec'],
            4 => ['short' => 'Kwi', 'full' => 'Kwiecien'],
            5 => ['short' => 'Maj', 'full' => 'Maj'],
            6 => ['short' => 'Cze', 'full' => 'Czerwiec'],
            7 => ['short' => 'Lip', 'full' => 'Lipiec'],
            8 => ['short' => 'Sie', 'full' => 'Sierpien'],
            9 => ['short' => 'Wrz', 'full' => 'Wrzesien'],
            10 => ['short' => 'Paz', 'full' => 'Pazdziernik'],
            11 => ['short' => 'Lis', 'full' => 'Listopad'],
            12 => ['short' => 'Gru', 'full' => 'Grudzien']
        ];

        $result = [];
        foreach ($months as $monthNum => $monthNames) {
            $costItem = $costData->get($monthNum);
            $carpetItem = $carpetCountData->get($monthNum);

            $totalAmount = $costItem ? (float) $costItem->total_amount : 0;
            $transactionCount = $costItem ? (int) $costItem->transaction_count : 0;
            $avgAmount = $costItem ? (float) $costItem->avg_amount : 0;
            $maxAmount = $costItem ? (float) $costItem->max_amount : 0;
            $minAmount = $costItem ? (float) $costItem->min_amount : 0;
            $processedCount = $carpetItem ? (int) $carpetItem->processed_count : 0;

            $avgCostPerCarpet = ($processedCount > 0) ? ($totalAmount / $processedCount) : 0;

            $result[] = [
                'label' => $monthNames['short'],
                'value' => $totalAmount,
                'full_name' => $monthNames['full'],
                'count' => $transactionCount,
                'average' => $avgAmount,
                'max' => $maxAmount,
                'min' => $minAmount,
                'processed_count' => $processedCount,
                'avg_cost_per_carpet' => $avgCostPerCarpet
            ];
        }
        return $result;
    }

    private function getYearlyData(): array
    {
        $costData = DB::table('processing_costs')
            ->selectRaw('
                EXTRACT(YEAR FROM cost_date) as year,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count,
                AVG(amount) as avg_amount,
                SUM(CASE WHEN EXTRACT(MONTH FROM cost_date) BETWEEN 1 AND 3 THEN amount ELSE 0 END) as q1_amount,
                SUM(CASE WHEN EXTRACT(MONTH FROM cost_date) BETWEEN 4 AND 6 THEN amount ELSE 0 END) as q2_amount,
                SUM(CASE WHEN EXTRACT(MONTH FROM cost_date) BETWEEN 7 AND 9 THEN amount ELSE 0 END) as q3_amount,
                SUM(CASE WHEN EXTRACT(MONTH FROM cost_date) BETWEEN 10 AND 12 THEN amount ELSE 0 END) as q4_amount
            ')
            ->groupByRaw('EXTRACT(YEAR FROM cost_date)')
            ->orderByRaw('EXTRACT(YEAR FROM cost_date)')
            ->get()
            ->keyBy('year');

        $carpetCountData = OrderCarpet::selectRaw('
                EXTRACT(YEAR FROM measured_at) as year,
                COUNT(*) as processed_count
            ')
            ->whereNotIn('status', [
                OrderCarpetStatus::PENDING->value,
                OrderCarpetStatus::PICKED_UP->value,
                OrderCarpetStatus::AT_LAUNDRY->value,
                OrderCarpetStatus::MEASURED->value
            ])
            ->whereNotNull('measured_at')
            ->groupByRaw('EXTRACT(YEAR FROM measured_at)')
            ->get()
            ->keyBy('year');

        $result = [];
        foreach ($costData as $year => $costItem) {
            $carpetItem = $carpetCountData->get($year);

            $totalAmount = (float) $costItem->total_amount;
            $transactionCount = (int) $costItem->transaction_count;
            $avgAmount = (float) $costItem->avg_amount;
            $q1Amount = (float) $costItem->q1_amount;
            $q2Amount = (float) $costItem->q2_amount;
            $q3Amount = (float) $costItem->q3_amount;
            $q4Amount = (float) $costItem->q4_amount;
            $processedCount = $carpetItem ? (int) $carpetItem->processed_count : 0;

            $avgCostPerCarpet = ($processedCount > 0) ? ($totalAmount / $processedCount) : 0;

            $result[] = [
                'label' => (string) (int) $year,
                'value' => $totalAmount,
                'full_name' => (string) (int) $year,
                'count' => $transactionCount,
                'average' => $avgAmount,
                'quarters' => [
                    'q1' => $q1Amount,
                    'q2' => $q2Amount,
                    'q3' => $q3Amount,
                    'q4' => $q4Amount,
                ],
                'processed_count' => $processedCount,
                'avg_cost_per_carpet' => $avgCostPerCarpet
            ];
        }
        return $result;
    }

    private function getCostTypeData(): array
    {
        $currentMonth = Carbon::now();
        $data = DB::table('processing_costs')
            ->selectRaw('
                type,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count,
                AVG(amount) as avg_amount,
                ROUND(
                    (SUM(amount) / NULLIF(
                        (SELECT SUM(amount) FROM processing_costs
                         WHERE EXTRACT(YEAR FROM cost_date) = ?
                         AND EXTRACT(MONTH FROM cost_date) = ?), 0
                    )) * 100, 2
                ) as percentage
            ', [$currentMonth->year, $currentMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM cost_date) = ?', [$currentMonth->year])
            ->whereRaw('EXTRACT(MONTH FROM cost_date) = ?', [$currentMonth->month])
            ->groupBy('type')
            ->orderByRaw('SUM(amount) DESC')
            ->get()
            ->toArray();

        return array_map(function ($item) {
            try {
                $costType = CostType::from($item->type);
                $label = method_exists($costType, 'getLabel') ? $costType->getLabel() : $item->type;
            } catch (\Exception $e) {
                $label = $item->type;
            }
            return [
                'type' => $item->type,
                'label' => $label,
                'value' => (float) $item->total_amount,
                'count' => (int) $item->transaction_count,
                'average' => (float) $item->avg_amount,
                'percentage' => (float) ($item->percentage ?? 0),
            ];
        }, $data);
    }

    private function getMonthlyComparison(): array
    {
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();
        $result = DB::table('processing_costs')
            ->selectRaw('
                SUM(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN amount ELSE 0 END) as current_month,
                SUM(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN amount ELSE 0 END) as previous_month,
                COUNT(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN 1 END) as current_count,
                COUNT(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN 1 END) as previous_count,
                ROUND(
                    ((SUM(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN amount ELSE 0 END) -
                      SUM(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN amount ELSE 0 END)) /
                     NULLIF(SUM(CASE WHEN EXTRACT(YEAR FROM cost_date) = ? AND EXTRACT(MONTH FROM cost_date) = ? THEN amount ELSE 0 END), 0)) * 100, 2
                ) as percentage_change
            ', [
                $currentMonth->year,
                $currentMonth->month,
                $previousMonth->year,
                $previousMonth->month,
                $currentMonth->year,
                $currentMonth->month,
                $previousMonth->year,
                $previousMonth->month,
                $currentMonth->year,
                $currentMonth->month,
                $previousMonth->year,
                $previousMonth->month,
                $previousMonth->year,
                $previousMonth->month,
            ])
            ->first();

        return [
            'current_month' => (float) ($result->current_month ?? 0),
            'previous_month' => (float) ($result->previous_month ?? 0),
            'current_count' => (int) ($result->current_count ?? 0),
            'previous_count' => (int) ($result->previous_count ?? 0),
            'percentage_change' => (float) ($result->percentage_change ?? 0),
        ];
    }

    private function getChartData(): array
    {
        return [
            'weekly' => $this->weeklyData,
            'monthly' => $this->monthlyData,
            'yearly' => $this->yearlyData,
            'costTypes' => $this->costTypeData
        ];
    }

    private function getTrend(array $data): string
    {
        if (count($data) < 2) return 'Stabilne';
        $values = array_column($data, 'value');
        $recent = array_slice($values, -3);
        $earlier = array_slice($values, 0, -3);
        $recentAvg = count($recent) > 0 ? array_sum($recent) / count($recent) : 0;
        $earlierAvg = count($earlier) > 0 ? array_sum($earlier) / count($earlier) : 0;
        if ($recentAvg > $earlierAvg * 1.05) return 'Trend rosnący';
        if ($recentAvg < $earlierAvg * 0.95) return 'Trend malejący';
        return 'Stabilne';
    }

    private function getYearOverYearChange(): string
    {
        if (count($this->yearlyData) < 2) return '0%';
        $currentYear = end($this->yearlyData);
        $previousYear = prev($this->yearlyData);
        if (!$currentYear || !$previousYear || $previousYear['value'] == 0) {
            return '0%';
        }
        $change = (($currentYear['value'] - $previousYear['value']) / $previousYear['value']) * 100;
        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('updateCharts', [
            'tab' => $this->activeTab,
            'data' => $this->getChartData()
        ]);
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dane zostały odświeżone'
        ]);
    }

    public function render()
    {
        $monthlyComparison = $this->getMonthlyComparison();
        return view('livewire.processing-costs.charts', [
            'weeklyTrend' => $this->getTrend($this->weeklyData),
            'monthlyTrend' => $this->getTrend($this->monthlyData),
            'yearlyChange' => $this->getYearOverYearChange(),
            'chartData' => $this->getChartData(),
            'monthlyComparison' => $monthlyComparison,
            'totalCurrentMonth' => $monthlyComparison['current_month'],
            'totalPreviousMonth' => $monthlyComparison['previous_month'],
            'percentageChange' => $monthlyComparison['percentage_change'],
        ]);
    }
}
