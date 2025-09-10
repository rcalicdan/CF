<?php

namespace App\ActionService;

use Carbon\Carbon;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ComplaintCsvReportService
{
    public function generateComplaintCsvReport(int $days = 30): string
    {
        $startDate = Carbon::now()->subDays($days);

        // Get orders with complaint status
        $complaintOrders = Order::with(['client', 'driver.user', 'orderCarpets.services'])
            ->where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = Writer::createFromString();
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');

        $headers = [
            'ID Zamówienia',
            'Data Utworzenia',
            'Status Zamówienia',
            'Priorytet',
            'Kategoria',
            'Klient - Imię',
            'Klient - Nazwisko',
            'Klient - Telefon',
            'Klient - Email',
            'Klient - Adres',
            'Kierowca',
            'Data Planowana',
            'Całkowita Kwota',
            'Liczba Dywanów',
            'Dywany - Kody',
            'Dywany - Wymiary',
            'Dywany - Powierzchnia',
            'Usługi',
            'Data Aktualizacji',
            'Miesiąc',
            'Kwartał',
            'Rok'
        ];

        $csv->insertOne($headers);

        $records = [];
        foreach ($complaintOrders as $order) {
            $client = $order->client;
            $driver = $order->driver?->user;

            // Get carpet information
            $carpetCodes = $order->orderCarpets->pluck('reference_code')->filter()->join(', ');
            $carpetDimensions = $order->orderCarpets->map(function ($carpet) {
                return $carpet->width . 'x' . $carpet->height;
            })->join(', ');
            $totalArea = $order->orderCarpets->sum('total_area');

            // Get services information
            $services = $order->orderCarpets->flatMap(function ($carpet) {
                return $carpet->services->pluck('name');
            })->unique()->join(', ');

            $priority = $this->determinePriority($order);
            $category = $this->determineCategory($order);

            $records[] = [
                $order->id,
                $order->created_at->format('d.m.Y H:i'),
                $this->getOrderStatusLabel($order->status),
                $priority['label'],
                $category,
                $client?->first_name ?? '',
                $client?->last_name ?? '',
                $client?->phone_number ?? '',
                $client?->email ?? '',
                $client?->full_address ?? '',
                $driver?->full_name ?? '',
                $order->schedule_date?->format('d.m.Y H:i') ?? '',
                $this->formatNumber($order->total_amount ?? 0),
                $order->orderCarpets->count(),
                $carpetCodes,
                $carpetDimensions,
                $this->formatNumber($totalArea),
                $services,
                $order->updated_at->format('d.m.Y H:i'),
                $this->getPolishMonth($order->created_at) . ' ' . $order->created_at->year,
                'Q' . $order->created_at->quarter . ' ' . $order->created_at->year,
                $order->created_at->year
            ];
        }

        $csv->insertAll($records);

        $filename = 'raport_skarg_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    /**
     * Generate summary statistics CSV
     */
    public function generateComplaintSummaryReport(int $days = 30): string
    {
        $csv = Writer::createFromString();
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');

        $stats = $this->getComplaintStatistics($days);
        $categoryStats = $this->getCategoryStats($days);
        $statusStats = $this->getStatusStats($days);
        $monthlyStats = $this->getMonthlyStats();

        $csv->insertOne(['RAPORT STATYSTYK SKARG']);
        $csv->insertOne(['Okres:', 'Ostatnie ' . $days . ' dni']);
        $csv->insertOne(['Wygenerowano:', Carbon::now()->format('d.m.Y H:i')]);
        $csv->insertOne(['']); 

        $csv->insertOne(['OGÓLNE STATYSTYKI']);
        $csv->insertOne(['Metryka', 'Wartość']);
        $csv->insertAll([
            ['Całkowita liczba skarg', $stats['total']],
            ['Aktywne skargi', $stats['active']],
            ['Nowe skargi', $stats['pending']],
            ['Skargi w trakcie', $stats['processing']],
            ['Rozwiązane skargi', $stats['completed']],
            ['Anulowane skargi', $stats['cancelled']],
            ['Współczynnik rozwiązań (%)', $this->formatNumber($stats['resolution_rate'])],
            ['Zmiana względem poprzedniego okresu', $stats['period_change']],
            ['Średnia wartość zamówienia', $this->formatNumber($stats['avg_order_value'])],
        ]);

        $csv->insertOne(['']); 

        $csv->insertOne(['ROZKŁAD WEDŁUG STATUSU']);
        $csv->insertOne(['Status', 'Liczba', 'Procent (%)']);
        $total = array_sum($statusStats);
        foreach ($statusStats as $status => $count) {
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            $csv->insertOne([
                $this->getOrderStatusLabel($status),
                $count,
                $this->formatNumber($percentage)
            ]);
        }

        $csv->insertOne(['']);

        $csv->insertOne(['ROZKŁAD WEDŁUG KATEGORII']);
        $csv->insertOne(['Kategoria', 'Liczba', 'Procent (%)']);
        $totalCategories = array_sum($categoryStats);
        foreach ($categoryStats as $category => $count) {
            $percentage = $totalCategories > 0 ? ($count / $totalCategories) * 100 : 0;
            $csv->insertOne([
                $this->getCategoryLabel($category),
                $count,
                $this->formatNumber($percentage)
            ]);
        }

        $csv->insertOne(['']);

        $csv->insertOne(['STATYSTYKI MIESIĘCZNE']);
        $csv->insertOne(['Miesiąc', 'Nowe skargi', 'Rozwiązane skargi', 'Współczynnik rozwiązań (%)']);
        foreach ($monthlyStats as $month) {
            $csv->insertOne([
                $month['month'],
                $month['new_count'],
                $month['completed_count'],
                $this->formatNumber($month['resolution_rate'])
            ]);
        }

        $filename = 'raport_statystyk_skarg_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    /**
     * Generate weekly trend CSV
     */
    public function generateWeeklyTrendReport(): string
    {
        $csv = Writer::createFromString();
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');

        $weeklyTrend = $this->getWeeklyTrend();

        $csv->insertOne(['TREND TYGODNIOWY SKARG']);
        $csv->insertOne(['Dzień', 'Nowe skargi', 'Rozwiązane skargi', 'Zmiana netto']);

        foreach ($weeklyTrend['days'] as $index => $day) {
            $newCount = $weeklyTrend['new_complaints'][$index] ?? 0;
            $completedCount = $weeklyTrend['completed_complaints'][$index] ?? 0;
            $netChange = $newCount - $completedCount;

            $csv->insertOne([
                $day,
                $newCount,
                $completedCount,
                $netChange >= 0 ? '+' . $netChange : $netChange
            ]);
        }

        $filename = 'trend_tygodniowy_skarg_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    private function getComplaintStatistics(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $totalComplaints = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)->count();

        $pendingComplaints = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->where('status', OrderStatus::PENDING->value)->count();

        $processingComplaints = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->where('status', OrderStatus::PROCESSING->value)->count();

        $completedComplaints = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->where('status', OrderStatus::COMPLETED->value)->count();

        $cancelledComplaints = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->where('status', OrderStatus::CANCELED->value)->count();

        $activeComplaints = $pendingComplaints + $processingComplaints;
        $resolutionRate = $totalComplaints > 0 ? round(($completedComplaints / $totalComplaints) * 100, 1) : 0;

        // Previous period comparison
        $previousStartDate = Carbon::now()->subDays($days * 2);
        $previousEndDate = Carbon::now()->subDays($days);
        $previousPeriodTotal = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $periodChange = $totalComplaints - $previousPeriodTotal;

        // Average order value for complaints
        $avgOrderValue = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->avg('total_amount') ?? 0;

        return [
            'total' => $totalComplaints,
            'active' => $activeComplaints,
            'completed' => $completedComplaints,
            'cancelled' => $cancelledComplaints,
            'pending' => $pendingComplaints,
            'processing' => $processingComplaints,
            'resolution_rate' => $resolutionRate,
            'period_change' => $periodChange,
            'avg_order_value' => $avgOrderValue,
        ];
    }

    private function getCategoryStats(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $complaintOrders = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->with(['orderCarpets.services'])
            ->get();

        $categories = [
            'damage' => 0,
            'delay' => 0,
            'quality' => 0,
            'service' => 0,
            'other' => 0
        ];

        foreach ($complaintOrders as $order) {
            $category = $this->determineCategory($order);
            $categoryKey = strtolower($category);
            
            if (isset($categories[$categoryKey])) {
                $categories[$categoryKey]++;
            } else {
                $categories['other']++;
            }
        }

        return $categories;
    }

    private function getStatusStats(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        return [
            OrderStatus::PENDING->value => Order::where('is_complaint', true)
                ->where('created_at', '>=', $startDate)
                ->where('status', OrderStatus::PENDING->value)->count(),
            OrderStatus::PROCESSING->value => Order::where('is_complaint', true)
                ->where('created_at', '>=', $startDate)
                ->where('status', OrderStatus::PROCESSING->value)->count(),
            OrderStatus::COMPLETED->value => Order::where('is_complaint', true)
                ->where('created_at', '>=', $startDate)
                ->where('status', OrderStatus::COMPLETED->value)->count(),
            OrderStatus::CANCELED->value => Order::where('is_complaint', true)
                ->where('created_at', '>=', $startDate)
                ->where('status', OrderStatus::CANCELED->value)->count(),
        ];
    }

    private function getMonthlyStats(): array
    {
        $performance = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $newCount = Order::where('is_complaint', true)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            
            $completedCount = Order::where('is_complaint', true)
                ->where('status', OrderStatus::COMPLETED->value)
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count();

            $resolutionRate = $newCount > 0 ? ($completedCount / $newCount) * 100 : 0;

            $performance[] = [
                'month' => $this->getPolishMonth($date) . ' ' . $date->year,
                'new_count' => $newCount,
                'completed_count' => $completedCount,
                'resolution_rate' => $resolutionRate,
            ];
        }

        return $performance;
    }

    private function getWeeklyTrend(): array
    {
        $days = [];
        $newComplaints = [];
        $completedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $this->getPolishDayName($date) . ' ' . $date->format('d.m');

            $newCount = Order::where('is_complaint', true)
                ->whereDate('created_at', $date)->count();
            
            $completedCount = Order::where('is_complaint', true)
                ->where('status', OrderStatus::COMPLETED->value)
                ->whereDate('updated_at', $date)
                ->count();

            $newComplaints[] = $newCount;
            $completedComplaints[] = $completedCount;
        }

        return [
            'days' => $days,
            'new_complaints' => $newComplaints,
            'completed_complaints' => $completedComplaints,
        ];
    }

    private function determinePriority($order): array
    {
        // Priority based on order total amount and carpet count
        $totalAmount = $order->total_amount ?? 0;
        $carpetCount = $order->orderCarpets->count();

        if ($totalAmount > 1000 || $carpetCount > 5) {
            return ['level' => 'high', 'label' => 'Wysoki'];
        } elseif ($totalAmount > 500 || $carpetCount > 2) {
            return ['level' => 'medium', 'label' => 'Średni'];
        } else {
            return ['level' => 'low', 'label' => 'Niski'];
        }
    }

    private function determineCategory($order): string
    {
        // Categorize based on carpet status and services
        $hasDamagedCarpets = $order->orderCarpets()
            ->where('status', OrderCarpetStatus::COMPLAINT->value)
            ->exists();

        if ($hasDamagedCarpets) {
            return 'damage';
        }

        // Check if it's a delay issue (scheduled date passed)
        if ($order->schedule_date && $order->schedule_date->isPast() && 
            !in_array($order->status, [OrderStatus::COMPLETED->value])) {
            return 'delay';
        }

        // Check service types
        $services = $order->orderCarpets->flatMap(function ($carpet) {
            return $carpet->services->pluck('name');
        });

        if ($services->contains(function ($name) {
            return str_contains(strtolower($name), 'pranie') || 
                   str_contains(strtolower($name), 'czyszczenie');
        })) {
            return 'quality';
        }

        return 'other';
    }

    private function getOrderStatusLabel(string $status): string
    {
        return match ($status) {
            OrderStatus::PENDING->value => 'Oczekujące',
            OrderStatus::PROCESSING->value => 'W trakcie',
            OrderStatus::COMPLETED->value => 'Ukończone',
            OrderStatus::CANCELED->value => 'Anulowane',
            default => 'Nieznany',
        };
    }

    private function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'damage' => 'Uszkodzenia',
            'delay' => 'Opóźnienia',
            'quality' => 'Jakość',
            'service' => 'Usługi',
            'other' => 'Inne',
            default => ucfirst($category),
        };
    }

    private function getPolishMonth(Carbon $date): string
    {
        $months = [
            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
        ];

        return $months[$date->month];
    }

    private function getPolishDayName(Carbon $date): string
    {
        $days = [
            0 => 'Niedziela', 1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa',
            4 => 'Czwartek', 5 => 'Piątek', 6 => 'Sobota'
        ];

        return $days[$date->dayOfWeek];
    }

    private function formatNumber(float $number): string
    {
        return number_format($number, 2, ',', ' ');
    }

    public function generateComplaintCsvReportForMonth(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // Get orders with complaint status for the month
        $complaintOrders = Order::with(['client', 'driver.user', 'orderCarpets.services'])
            ->where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = Writer::createFromString();
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');

        $headers = [
            'ID Zamówienia',
            'Data Utworzenia',
            'Status Zamówienia',
            'Priorytet',
            'Kategoria',
            'Klient - Imię',
            'Klient - Nazwisko',
            'Klient - Telefon',
            'Klient - Email',
            'Klient - Adres',
            'Kierowca',
            'Data Planowana',
            'Całkowita Kwota',
            'Liczba Dywanów',
            'Dywany - Kody',
            'Dywany - Wymiary',
            'Dywany - Powierzchnia',
            'Usługi',
            'Data Aktualizacji',
            'Miesiąc',
            'Kwartał',
            'Rok'
        ];
        $csv->insertOne($headers);

        $records = [];
        foreach ($complaintOrders as $order) {
            $client = $order->client;
            $driver = $order->driver?->user;

            // Get carpet information
            $carpetCodes = $order->orderCarpets->pluck('reference_code')->filter()->join(', ');
            $carpetDimensions = $order->orderCarpets->map(function ($carpet) {
                return $carpet->width . 'x' . $carpet->height;
            })->join(', ');
            $totalArea = $order->orderCarpets->sum('total_area');

            // Get services information
            $services = $order->orderCarpets->flatMap(function ($carpet) {
                return $carpet->services->pluck('name');
            })->unique()->join(', ');

            $priority = $this->determinePriority($order);
            $category = $this->determineCategory($order);

            $records[] = [
                $order->id,
                $order->created_at->format('d.m.Y H:i'),
                $this->getOrderStatusLabel($order->status),
                $priority['label'],
                $category,
                $client?->first_name ?? '',
                $client?->last_name ?? '',
                $client?->phone_number ?? '',
                $client?->email ?? '',
                $client?->full_address ?? '',
                $driver?->full_name ?? '',
                $order->schedule_date?->format('d.m.Y H:i') ?? '',
                $this->formatNumber($order->total_amount ?? 0),
                $order->orderCarpets->count(),
                $carpetCodes,
                $carpetDimensions,
                $this->formatNumber($totalArea),
                $services,
                $order->updated_at->format('d.m.Y H:i'),
                $this->getPolishMonth($order->created_at) . ' ' . $order->created_at->year,
                'Q' . $order->created_at->quarter . ' ' . $order->created_at->year,
                $order->created_at->year
            ];
        }

        $csv->insertAll($records);

        $filename = 'raport_skarg_' . $month . '_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    public function generateComplaintSummaryReportForMonth(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $csv = Writer::createFromString();
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');

        $stats = $this->getComplaintStatisticsForMonth($startDate, $endDate);
        $categoryStats = $this->getCategoryStatsForMonth($startDate, $endDate);
        $statusStats = $this->getStatusStatsForMonth($startDate, $endDate);

        $monthlyStats = [[
            'month' => $this->getPolishMonth($date) . ' ' . $date->year,
            'new_count' => $stats['total'], 
            'completed_count' => $stats['completed'],
            'resolution_rate' => $stats['resolution_rate']
        ]];

        $csv->insertOne(['RAPORT STATYSTYK SKARG - ' . $this->getPolishMonth($date) . ' ' . $date->year]);
        $csv->insertOne(['Wygenerowano:', Carbon::now()->format('d.m.Y H:i')]);
        $csv->insertOne(['']);

        $csv->insertOne(['OGÓLNE STATYSTYKI']);
        $csv->insertOne(['Metryka', 'Wartość']);
        $csv->insertAll([
            ['Całkowita liczba skarg', $stats['total']],
            ['Aktywne skargi', $stats['active']],
            ['Nowe skargi', $stats['pending']],
            ['Skargi w trakcie', $stats['processing']],
            ['Rozwiązane skargi', $stats['completed']],
            ['Anulowane skargi', $stats['cancelled']],
            ['Współczynnik rozwiązań (%)', $this->formatNumber($stats['resolution_rate'])],
            ['Zmiana względem poprzedniego miesiąca', $stats['period_change']],
            ['Średnia wartość zamówienia', $this->formatNumber($stats['avg_order_value'])],
        ]);

        $csv->insertOne(['']);
        $csv->insertOne(['ROZKŁAD WEDŁUG STATUSU']);
        $csv->insertOne(['Status', 'Liczba', 'Procent (%)']);
        $total = array_sum($statusStats);
        foreach ($statusStats as $status => $count) {
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            $csv->insertOne([
                $this->getOrderStatusLabel($status),
                $count,
                $this->formatNumber($percentage)
            ]);
        }

        $csv->insertOne(['']);
        $csv->insertOne(['ROZKŁAD WEDŁUG KATEGORII']);
        $csv->insertOne(['Kategoria', 'Liczba', 'Procent (%)']);
        $totalCategories = array_sum($categoryStats);
        foreach ($categoryStats as $category => $count) {
            $percentage = $totalCategories > 0 ? ($count / $totalCategories) * 100 : 0;
            $csv->insertOne([
                $this->getCategoryLabel($category),
                $count,
                $this->formatNumber($percentage)
            ]);
        }

        $csv->insertOne(['']);
        $csv->insertOne(['STATYSTYKI MIESIĘCZNE']);
        $csv->insertOne(['Miesiąc', 'Nowe skargi', 'Rozwiązane skargi', 'Współczynnik rozwiązań (%)']);
        foreach ($monthlyStats as $monthData) { 
            $csv->insertOne([
                $monthData['month'],
                $monthData['new_count'],
                $monthData['completed_count'],
                $this->formatNumber($monthData['resolution_rate'])
            ]);
        }

        $filename = 'raport_statystyk_skarg_' . $month . '_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    private function getComplaintStatisticsForMonth(Carbon $startDate, Carbon $endDate): array
    {
        $totalComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])->count();

        $pendingComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', OrderStatus::PENDING->value)->count();

        $processingComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', OrderStatus::PROCESSING->value)->count();

        $completedComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', OrderStatus::COMPLETED->value)->count();

        $cancelledComplaints = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', OrderStatus::CANCELED->value)->count();

        $activeComplaints = $pendingComplaints + $processingComplaints;
        $resolutionRate = $totalComplaints > 0 ? round(($completedComplaints / $totalComplaints) * 100, 1) : 0;

        $previousMonth = $startDate->copy()->subMonth();
        $previousMonthStart = $previousMonth->startOfMonth();
        $previousMonthEnd = $previousMonth->endOfMonth();
        $previousPeriodTotal = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $periodChange = $totalComplaints - $previousPeriodTotal;

        $avgOrderValue = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('total_amount') ?? 0;

        return [
            'total' => $totalComplaints,
            'active' => $activeComplaints,
            'completed' => $completedComplaints,
            'cancelled' => $cancelledComplaints,
            'pending' => $pendingComplaints,
            'processing' => $processingComplaints,
            'resolution_rate' => $resolutionRate,
            'period_change' => $periodChange,
            'avg_order_value' => $avgOrderValue,
        ];
    }

    private function getCategoryStatsForMonth(Carbon $startDate, Carbon $endDate): array
    {
        $complaintOrders = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['orderCarpets.services'])
            ->get();

        $categories = [
            'damage' => 0,
            'delay' => 0,
            'quality' => 0,
            'service' => 0,
            'other' => 0
        ];

        foreach ($complaintOrders as $order) {
            $category = $this->determineCategory($order);
            $categoryKey = strtolower($category);
            if (isset($categories[$categoryKey])) {
                $categories[$categoryKey]++;
            } else {
                $categories['other']++;
            }
        }

        return $categories;
    }

    private function getStatusStatsForMonth(Carbon $startDate, Carbon $endDate): array
    {
        return [
            OrderStatus::PENDING->value => Order::where('is_complaint', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', OrderStatus::PENDING->value)->count(),
            OrderStatus::PROCESSING->value => Order::where('is_complaint', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', OrderStatus::PROCESSING->value)->count(),
            OrderStatus::COMPLETED->value => Order::where('is_complaint', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', OrderStatus::COMPLETED->value)->count(),
            OrderStatus::CANCELED->value => Order::where('is_complaint', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', OrderStatus::CANCELED->value)->count(),
        ];
    }
}