<?php

namespace App\Livewire\Complaints;

use App\ActionService\ComplaintCsvReportService;
use App\ActionService\ComplaintPdfReportService;
use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;
use Carbon\Carbon;

class ComplaintStatistics extends Component
{
    public $selectedPeriod = '7';
    public $selectedMonth = null;
    public $periodType = 'days';
    public $complaintStats = [];
    public $weeklyTrend = [];
    public $categoryStats = [];
    public $recentComplaints = [];
    public $availableMonths = [];

    protected $listeners = ['refreshStats' => 'loadStats'];

    public function mount()
    {
        $this->availableMonths = $this->getAvailableMonths();
        $this->loadStats();
    }

    public function updatedSelectedPeriod()
    {
        $this->periodType = 'days';
        $this->selectedMonth = null;
        $this->loadStats();
    }

    public function updatedSelectedMonth()
    {
        $this->periodType = 'month';
        $this->selectedPeriod = '30';
        $this->loadStats();
    }

    public function updatedPeriodType()
    {
        if ($this->periodType === 'days') {
            $this->selectedMonth = null;
        } else {
            $this->selectedMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        }
        $this->loadStats();
    }

    public function generatePdfReport()
    {
        $reportService = new ComplaintPdfReportService();

        if ($this->periodType === 'month' && $this->selectedMonth) {
            $filename = $reportService->generateComplaintReportForMonth($this->selectedMonth);
        } else {
            $filename = $reportService->generateComplaintReport((int)$this->selectedPeriod);
        }

        return response()->download(storage_path('app/public/' . $filename))
            ->deleteFileAfterSend();
    }

    public function generateCsvReport()
    {
        $reportService = new ComplaintCsvReportService();

        if ($this->periodType === 'month' && $this->selectedMonth) {
            $filename = $reportService->generateComplaintCsvReportForMonth($this->selectedMonth);
        } else {
            $filename = $reportService->generateComplaintCsvReport((int)$this->selectedPeriod);
        }

        return response()->download(storage_path('app/public/' . $filename))
            ->deleteFileAfterSend();
    }

    public function generateSummaryCsvReport()
    {
        $reportService = new ComplaintCsvReportService();

        if ($this->periodType === 'month' && $this->selectedMonth) {
            $filename = $reportService->generateComplaintSummaryReportForMonth($this->selectedMonth);
        } else {
            $filename = $reportService->generateComplaintSummaryReport((int)$this->selectedPeriod);
        }

        return response()->download(storage_path('app/public/' . $filename))
            ->deleteFileAfterSend();
    }

    public function generateWeeklyTrendCsv()
    {
        $reportService = new ComplaintCsvReportService();
        $filename = $reportService->generateWeeklyTrendReport();

        return response()->download(storage_path('app/public/' . $filename))
            ->deleteFileAfterSend();
    }

    public function loadStats()
    {
        if ($this->periodType === 'month' && $this->selectedMonth) {
            $this->complaintStats = $this->getComplaintStatsForMonth();
            $this->weeklyTrend = $this->getMonthlyDailyTrend();
            $this->categoryStats = $this->getCategoryStatsForMonth();
            $this->recentComplaints = $this->getRecentComplaintsForMonth();
        } else {
            $this->complaintStats = $this->getComplaintStats();
            $this->weeklyTrend = $this->getWeeklyTrend();
            $this->categoryStats = $this->getCategoryStats();
            $this->recentComplaints = $this->getRecentComplaints();
        }
    }

    private function getComplaintStats()
    {
        $totalComplaints = Order::where('is_complaint', true)->count();
        
        // Map order statuses to complaint equivalents
        $pendingComplaints = Order::where('is_complaint', true)
            ->where('status', OrderStatus::PENDING->value)
            ->count();
            
        $processingComplaints = Order::where('is_complaint', true)
            ->whereIn('status', [OrderStatus::ACCEPTED->value, OrderStatus::PROCESSING->value])
            ->count();
            
        $resolvedComplaints = Order::where('is_complaint', true)
            ->whereIn('status', [OrderStatus::COMPLETED->value, OrderStatus::DELIVERED->value])
            ->count();

        $activeComplaints = $pendingComplaints + $processingComplaints;
        $resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 0;

        // Get previous week stats for comparison
        $previousWeekStart = Carbon::now()->subWeeks(2)->startOfWeek();
        $previousWeekEnd = Carbon::now()->subWeeks(1)->endOfWeek();
        $previousWeekTotal = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
            ->count();

        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentWeekTotal = Order::where('is_complaint', true)
            ->where('created_at', '>=', $currentWeekStart)
            ->count();

        $weeklyChange = $previousWeekTotal > 0 ? $currentWeekTotal - $previousWeekTotal : 0;

        return [
            'total' => $totalComplaints,
            'active' => $activeComplaints,
            'resolved' => $resolvedComplaints,
            'resolution_rate' => $resolutionRate,
            'weekly_change' => $weeklyChange,
            'open' => $pendingComplaints,
            'in_progress' => $processingComplaints,
        ];
    }

    private function getComplaintStatsForMonth()
    {
        $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $totalComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
            
        $pendingComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', OrderStatus::PENDING->value)
            ->count();
            
        $processingComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [OrderStatus::ACCEPTED->value, OrderStatus::PROCESSING->value])
            ->count();
            
        $resolvedComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [OrderStatus::COMPLETED->value, OrderStatus::DELIVERED->value])
            ->count();

        $activeComplaints = $pendingComplaints + $processingComplaints;
        $resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 0;

        // Get previous month stats for comparison
        $previousMonth = $date->copy()->subMonth();
        $previousMonthStart = $previousMonth->startOfMonth();
        $previousMonthEnd = $previousMonth->endOfMonth();
        $previousMonthTotal = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $monthlyChange = $previousMonthTotal > 0 ? $totalComplaints - $previousMonthTotal : 0;

        return [
            'total' => $totalComplaints,
            'active' => $activeComplaints,
            'resolved' => $resolvedComplaints,
            'resolution_rate' => $resolutionRate,
            'weekly_change' => $monthlyChange,
            'open' => $pendingComplaints,
            'in_progress' => $processingComplaints,
        ];
    }

    private function getMonthlyDailyTrend()
    {
        $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $days = [];
        $newComplaints = [];
        $resolvedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $currentDate = $endOfMonth->copy()->subDays($i);

            if ($currentDate->gte($startOfMonth) && $currentDate->lte($endOfMonth)) {
                $days[] = $currentDate->format('d');

                $newCount = Order::where('is_complaint', true)
                    ->whereDate('created_at', $currentDate)
                    ->count();
                    
                $resolvedCount = Order::where('is_complaint', true)
                    ->whereIn('status', [OrderStatus::COMPLETED->value, OrderStatus::DELIVERED->value])
                    ->whereDate('updated_at', $currentDate)
                    ->count();

                $newComplaints[] = $newCount;
                $resolvedComplaints[] = $resolvedCount;
            }
        }

        return [
            'days' => $days,
            'new_complaints' => $newComplaints,
            'resolved_complaints' => $resolvedComplaints,
        ];
    }

    private function getCategoryStatsForMonth()
    {
        $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $complaintOrders = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with(['client', 'orderCarpets'])
            ->get();

        return $this->categorizeOrders($complaintOrders);
    }

    private function getRecentComplaintsForMonth()
    {
        $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        return Order::where('is_complaint', true)
            ->with(['client', 'driver.user', 'orderCarpets'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($order) {
                return $this->mapOrderToComplaint($order);
            });
    }

    private function getWeeklyTrend()
    {
        $days = [];
        $newComplaints = [];
        $resolvedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D');

            $newCount = Order::where('is_complaint', true)
                ->whereDate('created_at', $date)
                ->count();
                
            $resolvedCount = Order::where('is_complaint', true)
                ->whereIn('status', [OrderStatus::COMPLETED->value, OrderStatus::DELIVERED->value])
                ->whereDate('updated_at', $date)
                ->count();

            $newComplaints[] = $newCount;
            $resolvedComplaints[] = $resolvedCount;
        }

        return [
            'days' => $days,
            'new_complaints' => $newComplaints,
            'resolved_complaints' => $resolvedComplaints,
        ];
    }

    private function getCategoryStats()
    {
        $complaintOrders = Order::where('is_complaint', true)
            ->with(['client', 'orderCarpets'])
            ->get();

        return $this->categorizeOrders($complaintOrders);
    }

    private function categorizeOrders($orders)
    {
        $categories = [
            'damage' => 0,
            'delay' => 0,
            'quality' => 0,
            'communication' => 0,
            'other' => 0
        ];

        foreach ($orders as $order) {
            // Categorize based on order status and other factors
            // You can enhance this logic based on your business needs
            switch ($order->status) {
                case OrderStatus::UNDELIVERED->value:
                    $categories['delay']++;
                    break;
                case OrderStatus::CANCELED->value:
                    // If order was canceled, it might be due to quality issues
                    $categories['quality']++;
                    break;
                case OrderStatus::PENDING->value:
                    // Pending orders might have communication issues
                    $categories['communication']++;
                    break;
                default:
                    // Distribute other statuses
                    $categories['other']++;
                    break;
            }
        }

        return $categories;
    }

    private function getRecentComplaints()
    {
        return Order::where('is_complaint', true)
            ->with(['client', 'driver.user', 'orderCarpets'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($order) {
                return $this->mapOrderToComplaint($order);
            });
    }

    private function mapOrderToComplaint($order)
    {
        return [
            'id' => $order->id,
            'details' => $this->generateComplaintDetails($order),
            'status' => $order->status,
            'created_at' => $order->created_at,
            'client_name' => $order->client?->full_name ?? 'N/A',
            'order_id' => $order->id,
            'carpet_qr' => $order->orderCarpets?->first()?->reference_code ?? 'N/A',
            'priority' => $this->determinePriority($order),
            'total_amount' => $order->total_amount,
        ];
    }

    private function generateComplaintDetails($order)
    {
        // Generate complaint details based on order status and information
        switch ($order->status) {
            case OrderStatus::UNDELIVERED->value:
                return "Zamówienie nie zostało dostarczone w terminie - Klient: {$order->client?->full_name}";
            case OrderStatus::CANCELED->value:
                return "Zamówienie zostało anulowane - możliwe problemy z jakością usługi";
            case OrderStatus::PENDING->value:
                return "Skarga dotycząca opóźnień w realizacji zamówienia";
            default:
                return "Skarga klienta dotycząca zamówienia #{$order->id} - Status: {$order->status_label}";
        }
    }

    private function determinePriority($order)
    {
        // Determine priority based on order status, value, and age
        $daysSinceCreated = $order->created_at->diffInDays(now());
        
        if ($order->status === OrderStatus::UNDELIVERED->value || $daysSinceCreated > 7) {
            return ['level' => 'high', 'label' => 'Wysoki'];
        } elseif ($order->total_amount > 500 || $daysSinceCreated > 3) {
            return ['level' => 'medium', 'label' => 'Średni'];
        } else {
            return ['level' => 'low', 'label' => 'Niski'];
        }
    }

    public function getStatusColor($status)
    {
        return match ($status) {
            OrderStatus::PENDING->value => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Oczekuje'],
            OrderStatus::ACCEPTED->value => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Zaakceptowane'],
            OrderStatus::PROCESSING->value => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'W realizacji'],
            OrderStatus::COMPLETED->value => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Ukończone'],
            OrderStatus::DELIVERED->value => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Dostarczone'],
            OrderStatus::UNDELIVERED->value => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Niedostarczone'],
            OrderStatus::CANCELED->value => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Anulowane'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Nieznany'],
        };
    }

    public function getAvailableMonths()
    {
        $months = collect();

        $currentDate = Carbon::now();
        for ($i = 0; $i < 12; $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $value = $date->format('Y-m');
            $label = $this->getPolishMonth($date) . ' ' . $date->year;

            $months->push([
                'value' => $value,
                'label' => $label
            ]);
        }

        $months = $months->unique('value')->sortByDesc('value');

        return $months->values()->toArray();
    }

    private function getPolishMonth(Carbon $date): string
    {
        $months = [
            1 => 'Styczeń',
            2 => 'Luty',
            3 => 'Marzec',
            4 => 'Kwiecień',
            5 => 'Maj',
            6 => 'Czerwiec',
            7 => 'Lipiec',
            8 => 'Sierpień',
            9 => 'Wrzesień',
            10 => 'Październik',
            11 => 'Listopad',
            12 => 'Grudzień'
        ];

        return $months[$date->month];
    }

    public function render()
    {
        return view('livewire.complaints.complaint-statistics');
    }
}