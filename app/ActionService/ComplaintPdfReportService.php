<?php

namespace App\ActionService;

use TCPDF;
use Carbon\Carbon;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Facades\Storage;

class ComplaintPdfReportService
{
    public function generateComplaintReport(int $days = 30): string
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Raport Statystyk Skarg');
        $pdf->SetSubject('Analiza jakości serwisu i efektywności pracowników');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        // Get complaint data
        $complaintData = $this->getComplaintData($days);

        // Title Section
        $this->addTitleSection($pdf, $days);

        // Summary Statistics Section
        $this->addSummarySection($pdf, $complaintData['stats']);

        // Status Distribution Section
        $this->addStatusDistributionSection($pdf, $complaintData['statusDistribution']);

        // Category Analysis Section
        // $this->addCategoryAnalysisSection($pdf, $complaintData['categoryStats']);

        // Weekly Trend Section
        $this->addWeeklyTrendSection($pdf, $complaintData['weeklyTrend']);

        // Recent Complaints Section
        $this->addRecentComplaintsSection($pdf, $complaintData['recentComplaints']);

        // Monthly Performance Section
        $this->addMonthlyPerformanceSection($pdf, $complaintData['monthlyPerformance']);

        // Order Value Analysis Section
        $this->addOrderValueAnalysisSection($pdf, $complaintData['orderValueAnalysis']);

        // Priority Analysis Section
        $this->addPriorityAnalysisSection($pdf, $complaintData['priorityAnalysis']);

        $filename = 'raport_statystyk_skarg_' . Carbon::now()->format('Y-m-d_H-i') . '.pdf';
        $pdfContent = $pdf->Output('', 'S');
        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }

    private function getComplaintData(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        // Basic stats
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
        $periodChange = $previousPeriodTotal > 0 ? $totalComplaints - $previousPeriodTotal : 0;

        $avgOrderValue = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->avg('total_amount') ?? 0;

        $stats = [
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

        // Status distribution
        $statusDistribution = [
            'pending' => $pendingComplaints,
            'processing' => $processingComplaints,
            'completed' => $completedComplaints,
            'cancelled' => $cancelledComplaints,
        ];

        // Category statistics
        $categoryStats = $this->getCategoryStats($days);

        // Weekly trend (last 7 days)
        $weeklyTrend = $this->getWeeklyTrend();

        // Recent complaints
        $recentComplaints = Order::with(['client', 'driver.user', 'orderCarpets'])
            ->where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d.m.Y'),
                    'client_name' => $order->client?->full_name ?? 'N/A',
                    'driver_name' => $order->driver?->user?->full_name ?? 'N/A',
                    'carpet_count' => $order->orderCarpets->count(),
                    'total_amount' => $order->total_amount ?? 0,
                    'priority' => $this->determinePriority($order),
                ];
            });

        // Monthly performance
        $monthlyPerformance = $this->getMonthlyPerformance();

        // Order value analysis
        $orderValueAnalysis = $this->getOrderValueAnalysis($days);

        // Priority analysis
        $priorityAnalysis = $this->getPriorityAnalysis($days);

        return [
            'stats' => $stats,
            'statusDistribution' => $statusDistribution,
            'categoryStats' => $categoryStats,
            'weeklyTrend' => $weeklyTrend,
            'recentComplaints' => $recentComplaints,
            'monthlyPerformance' => $monthlyPerformance,
            'orderValueAnalysis' => $orderValueAnalysis,
            'priorityAnalysis' => $priorityAnalysis,
        ];
    }

    private function addTitleSection(TCPDF $pdf, int $days): void
    {
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->Cell(0, 15, 'Raport Statystyk Skarg', 0, 1, 'C');

        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Analiza Zamówień ze Skargami', 0, 1, 'C');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 8, 'Okres: Ostatnie ' . $days . ' dni', 0, 1, 'C');
        $pdf->Cell(0, 8, 'Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);
    }

    private function addSummarySection(TCPDF $pdf, array $stats): void
    {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Podsumowanie statystyk', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(240, 240, 240);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.6;
        $col2_width = $pageWidth * 0.4;

        $summaryData = [
            ['Całkowita liczba skarg', number_format($stats['total'], 0, ',', ' ')],
            ['Aktywne skargi', number_format($stats['active'], 0, ',', ' ')],
            ['Oczekujące skargi', number_format($stats['pending'], 0, ',', ' ')],
            ['Skargi w trakcie', number_format($stats['processing'], 0, ',', ' ')],
            ['Ukończone skargi', number_format($stats['completed'], 0, ',', ' ')],
            ['Anulowane skargi', number_format($stats['cancelled'], 0, ',', ' ')],
            ['Współczynnik rozwiązań', number_format($stats['resolution_rate'], 1, ',', ' ') . '%'],
            ['Zmiana względem poprzedniego okresu', ($stats['period_change'] >= 0 ? '+' : '') . number_format($stats['period_change'], 0, ',', ' ')],
            ['Średnia wartość zamówienia', number_format($stats['avg_order_value'], 2, ',', ' ') . ' zł'],
        ];

        foreach ($summaryData as $row) {
            $pdf->Cell($col1_width, 8, $row[0], 1, 0, 'L', true);
            $pdf->Cell($col2_width, 8, $row[1], 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addStatusDistributionSection(TCPDF $pdf, array $statusDistribution): void
    {
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Rozkład według statusu', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.5;
        $col2_width = $pageWidth * 0.25;
        $col3_width = $pageWidth * 0.25;

        $total = array_sum($statusDistribution);

        // Header
        $pdf->Cell($col1_width, 8, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col2_width, 8, 'Liczba', 1, 0, 'C', true);
        $pdf->Cell($col3_width, 8, 'Procent', 1, 1, 'C', true);

        $statusLabels = [
            'pending' => 'Oczekujące',
            'processing' => 'W trakcie',
            'completed' => 'Ukończone',
            'cancelled' => 'Anulowane',
        ];

        foreach ($statusDistribution as $status => $count) {
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            $pdf->Cell($col1_width, 7, $statusLabels[$status] ?? ucfirst($status), 1, 0, 'L');
            $pdf->Cell($col2_width, 7, number_format($count, 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col3_width, 7, number_format($percentage, 1, ',', ' ') . '%', 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addCategoryAnalysisSection(TCPDF $pdf, array $categoryStats): void
    {
        if ($pdf->GetY() > 230) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Analiza kategorii skarg', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.5;
        $col2_width = $pageWidth * 0.25;
        $col3_width = $pageWidth * 0.25;

        $total = array_sum($categoryStats);

        // Header
        $pdf->Cell($col1_width, 8, 'Kategoria', 1, 0, 'C', true);
        $pdf->Cell($col2_width, 8, 'Liczba', 1, 0, 'C', true);
        $pdf->Cell($col3_width, 8, 'Procent', 1, 1, 'C', true);

        $categoryLabels = [
            'damage' => 'Uszkodzenia',
            'delay' => 'Opóźnienia',
            'quality' => 'Jakość',
            'service' => 'Usługi',
            'other' => 'Inne',
        ];

        foreach ($categoryStats as $category => $count) {
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            $pdf->Cell($col1_width, 7, $categoryLabels[$category] ?? ucfirst($category), 1, 0, 'L');
            $pdf->Cell($col2_width, 7, number_format($count, 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col3_width, 7, number_format($percentage, 1, ',', ' ') . '%', 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addWeeklyTrendSection(TCPDF $pdf, array $weeklyTrend): void
    {
        if ($pdf->GetY() > 200) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Trend tygodniowy (ostatnie 7 dni)', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.25, // Day
            $pageWidth * 0.25, // New
            $pageWidth * 0.25, // Completed
            $pageWidth * 0.25, // Net Change
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'Dzień', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Nowe', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Ukończone', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Zmiana netto', 1, 1, 'C', true);

        foreach ($weeklyTrend['days'] as $index => $day) {
            $newCount = $weeklyTrend['new_complaints'][$index] ?? 0;
            $completedCount = $weeklyTrend['completed_complaints'][$index] ?? 0;
            $netChange = $newCount - $completedCount;

            $pdf->Cell($col_widths[0], 6, $day, 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $newCount, 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $completedCount, 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, ($netChange >= 0 ? '+' : '') . $netChange, 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    private function addRecentComplaintsSection(TCPDF $pdf, $recentComplaints): void
    {
        if ($recentComplaints->isEmpty()) {
            return;
        }

        if ($pdf->GetY() > 180) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Najnowsze skargi (10 ostatnich)', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.08, // ID
            $pageWidth * 0.12, // Date
            $pageWidth * 0.15, // Status
            $pageWidth * 0.20, // Client
            $pageWidth * 0.15, // Driver
            $pageWidth * 0.10, // Carpets
            $pageWidth * 0.12, // Amount
            $pageWidth * 0.08, // Priority
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'ID', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Data', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Klient', 1, 0, 'C', true);
        $pdf->Cell($col_widths[4], 7, 'Kierowca', 1, 0, 'C', true);
        $pdf->Cell($col_widths[5], 7, 'Dywany', 1, 0, 'C', true);
        $pdf->Cell($col_widths[6], 7, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($col_widths[7], 7, 'Prior.', 1, 1, 'C', true);

        foreach ($recentComplaints as $complaint) {
            $pdf->Cell($col_widths[0], 6, '#' . $complaint['id'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $complaint['created_at'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $this->getOrderStatusLabel($complaint['status']), 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, mb_substr($complaint['client_name'], 0, 15), 1, 0, 'L');
            $pdf->Cell($col_widths[4], 6, mb_substr($complaint['driver_name'], 0, 12), 1, 0, 'L');
            $pdf->Cell($col_widths[5], 6, $complaint['carpet_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[6], 6, number_format($complaint['total_amount'], 0) . ' zł', 1, 0, 'R');
            $pdf->Cell($col_widths[7], 6, substr($complaint['priority']['label'], 0, 3), 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    private function addMonthlyPerformanceSection(TCPDF $pdf, array $monthlyPerformance): void
    {
        if (empty($monthlyPerformance)) {
            return;
        }

        if ($pdf->GetY() > 220) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Wydajność miesięczna (ostatnie 6 miesięcy)', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.25, // Month
            $pageWidth * 0.25, // New
            $pageWidth * 0.25, // Completed
            $pageWidth * 0.25, // Resolution Rate
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'Miesiąc', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Nowe', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Ukończone', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Wsp. rozw.', 1, 1, 'C', true);

        foreach ($monthlyPerformance as $month) {
            $pdf->Cell($col_widths[0], 6, $month['month'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $month['new_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $month['completed_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, number_format($month['resolution_rate'], 1) . '%', 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    private function addOrderValueAnalysisSection(TCPDF $pdf, array $orderValueAnalysis): void
    {
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Analiza wartości zamówień', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.6;
        $col2_width = $pageWidth * 0.4;

        $valueData = [
            ['Średnia wartość zamówienia', number_format($orderValueAnalysis['avg_order_value'], 2, ',', ' ') . ' zł'],
            ['Najniższa wartość', number_format($orderValueAnalysis['min_order_value'], 2, ',', ' ') . ' zł'],
            ['Najwyższa wartość', number_format($orderValueAnalysis['max_order_value'], 2, ',', ' ') . ' zł'],
            ['Zamówienia < 500 zł', number_format($orderValueAnalysis['low_value'], 0, ',', ' ')],
            ['Zamówienia 500-1000 zł', number_format($orderValueAnalysis['medium_value'], 0, ',', ' ')],
            ['Zamówienia > 1000 zł', number_format($orderValueAnalysis['high_value'], 0, ',', ' ')],
        ];

        foreach ($valueData as $row) {
            $pdf->Cell($col1_width, 8, $row[0], 1, 0, 'L', true);
            $pdf->Cell($col2_width, 8, $row[1], 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addPriorityAnalysisSection(TCPDF $pdf, array $priorityAnalysis): void
    {
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Analiza priorytetów', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.5;
        $col2_width = $pageWidth * 0.25;
        $col3_width = $pageWidth * 0.25;

        $total = array_sum($priorityAnalysis);

        // Header
        $pdf->Cell($col1_width, 8, 'Priorytet', 1, 0, 'C', true);
        $pdf->Cell($col2_width, 8, 'Liczba', 1, 0, 'C', true);
        $pdf->Cell($col3_width, 8, 'Procent', 1, 1, 'C', true);

        $priorityLabels = [
            'high' => 'Wysoki',
            'medium' => 'Średni',
            'low' => 'Niski',
        ];

        foreach ($priorityAnalysis as $priority => $count) {
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            $pdf->Cell($col1_width, 7, $priorityLabels[$priority] ?? ucfirst($priority), 1, 0, 'L');
            $pdf->Cell($col2_width, 7, number_format($count, 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col3_width, 7, number_format($percentage, 1, ',', ' ') . '%', 1, 1, 'R');
        }
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

    private function getWeeklyTrend(): array
    {
        $days = [];
        $newComplaints = [];
        $completedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D d.m');

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

    private function getMonthlyPerformance(): array
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
                'month' => $date->format('M Y'),
                'new_count' => $newCount,
                'completed_count' => $completedCount,
                'resolution_rate' => $resolutionRate,
            ];
        }

        return $performance;
    }

    private function getOrderValueAnalysis(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $complaintOrders = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('total_amount')
            ->get();

        if ($complaintOrders->isEmpty()) {
            return [
                'avg_order_value' => 0,
                'min_order_value' => 0,
                'max_order_value' => 0,
                'low_value' => 0,
                'medium_value' => 0,
                'high_value' => 0,
            ];
        }

        $amounts = $complaintOrders->pluck('total_amount');

        $lowValueCount = $complaintOrders->where('total_amount', '<', 500)->count();
        $mediumValueCount = $complaintOrders->whereBetween('total_amount', [500, 1000])->count();
        $highValueCount = $complaintOrders->where('total_amount', '>', 1000)->count();

        return [
            'avg_order_value' => $amounts->avg(),
            'min_order_value' => $amounts->min(),
            'max_order_value' => $amounts->max(),
            'low_value' => $lowValueCount,
            'medium_value' => $mediumValueCount,
            'high_value' => $highValueCount,
        ];
    }

    private function getPriorityAnalysis(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $complaintOrders = Order::where('is_complaint', true)
            ->where('created_at', '>=', $startDate)
            ->with(['orderCarpets'])
            ->get();

        $priorities = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($complaintOrders as $order) {
            $priority = $this->determinePriority($order);
            $priorities[$priority['level']]++;
        }

        return $priorities;
    }

    private function determinePriority($order): array
    {
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
        $hasDamagedCarpets = $order->orderCarpets()
            ->where('status', OrderCarpetStatus::COMPLAINT->value)
            ->exists();

        if ($hasDamagedCarpets) {
            return 'damage';
        }

        if (
            $order->schedule_date && $order->schedule_date->isPast() &&
            !in_array($order->status, [OrderStatus::COMPLETED->value])
        ) {
            return 'delay';
        }

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

    public function generateComplaintReportForMonth(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Raport Statystyk Skarg - ' . $date->format('F Y')); // Use full month name
        $pdf->SetSubject('Analiza jakości serwisu i efektywności pracowników');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        // Get complaint data for the month
        $complaintData = $this->getComplaintDataForMonth($startDate, $endDate);

        // Title Section (Modified for Month)
        $this->addTitleSectionForMonth($pdf, $month, $date);

        // Summary Statistics Section
        $this->addSummarySection($pdf, $complaintData['stats']);

        // Status Distribution Section
        $this->addStatusDistributionSection($pdf, $complaintData['statusDistribution']);

        // Category Analysis Section
        // $this->addCategoryAnalysisSection($pdf, $complaintData['categoryStats']);

        // Daily Trend Section (Modified for Month)
        $this->addDailyTrendSectionForMonth($pdf, $complaintData['dailyTrend']);

        // Recent Complaints Section (Modified for Month)
        $this->addRecentComplaintsSectionForMonth($pdf, $complaintData['recentComplaints']);

        // Monthly Performance Section (Not applicable for single month, maybe omit or show single month data)
        // $this->addMonthlyPerformanceSection($pdf, $complaintData['monthlyPerformance']);

        // Order Value Analysis Section
        $this->addOrderValueAnalysisSection($pdf, $complaintData['orderValueAnalysis']);

        // Priority Analysis Section
        $this->addPriorityAnalysisSection($pdf, $complaintData['priorityAnalysis']);

        $filename = 'raport_statystyk_skarg_' . $month . '_' . Carbon::now()->format('Y-m-d_H-i') . '.pdf';
        $pdfContent = $pdf->Output('', 'S');
        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }

    /**
     * Fetch complaint data for a specific month.
     *
     * @param Carbon $startDate Start of the month.
     * @param Carbon $endDate End of the month.
     * @return array
     */
    private function getComplaintDataForMonth(Carbon $startDate, Carbon $endDate): array
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
        $periodChange = $previousPeriodTotal > 0 ? $totalComplaints - $previousPeriodTotal : 0;

        $avgOrderValue = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('total_amount') ?? 0;

        $stats = [
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

        $statusDistribution = [
            'pending' => $pendingComplaints,
            'processing' => $processingComplaints,
            'completed' => $completedComplaints,
            'cancelled' => $cancelledComplaints,
        ];

        $categoryStats = $this->getCategoryStatsForMonth($startDate, $endDate);

        $dailyTrend = $this->getDailyTrendForMonth($startDate, $endDate);

        $recentComplaints = Order::with(['client', 'driver.user', 'orderCarpets'])
            ->where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d.m.Y'),
                    'client_name' => $order->client?->full_name ?? 'N/A',
                    'driver_name' => $order->driver?->user?->full_name ?? 'N/A',
                    'carpet_count' => $order->orderCarpets->count(),
                    'total_amount' => $order->total_amount ?? 0,
                    'priority' => $this->determinePriority($order),
                ];
            });

        $orderValueAnalysis = $this->getOrderValueAnalysisForMonth($startDate, $endDate);

        $priorityAnalysis = $this->getPriorityAnalysisForMonth($startDate, $endDate);

        return [
            'stats' => $stats,
            'statusDistribution' => $statusDistribution,
            'categoryStats' => $categoryStats,
            'dailyTrend' => $dailyTrend, 
            'recentComplaints' => $recentComplaints,
            'monthlyPerformance' => [], 
            'orderValueAnalysis' => $orderValueAnalysis,
            'priorityAnalysis' => $priorityAnalysis,
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

    private function getDailyTrendForMonth(Carbon $startDate, Carbon $endDate): array
    {
        $days = [];
        $newComplaints = [];
        $completedComplaints = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $days[] = $currentDate->format('d.m'); 

            $newCount = Order::where('is_complaint', true)
                ->whereDate('created_at', $currentDate)->count();

            $completedCount = Order::where('is_complaint', true)
                ->where('status', OrderStatus::COMPLETED->value)
                ->whereDate('updated_at', $currentDate) 
                ->count();

            $newComplaints[] = $newCount;
            $completedComplaints[] = $completedCount;

            $currentDate->addDay();
        }

        return [
            'days' => $days,
            'new_complaints' => $newComplaints,
            'completed_complaints' => $completedComplaints,
        ];
    }

    private function getOrderValueAnalysisForMonth(Carbon $startDate, Carbon $endDate): array
    {
        $complaintOrders = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('total_amount')
            ->get();

        if ($complaintOrders->isEmpty()) {
            return [
                'avg_order_value' => 0,
                'min_order_value' => 0,
                'max_order_value' => 0,
                'low_value' => 0,
                'medium_value' => 0,
                'high_value' => 0,
            ];
        }

        $amounts = $complaintOrders->pluck('total_amount');
        $lowValueCount = $complaintOrders->where('total_amount', '<', 500)->count();
        $mediumValueCount = $complaintOrders->whereBetween('total_amount', [500, 1000])->count();
        $highValueCount = $complaintOrders->where('total_amount', '>', 1000)->count();

        return [
            'avg_order_value' => $amounts->avg(),
            'min_order_value' => $amounts->min(),
            'max_order_value' => $amounts->max(),
            'low_value' => $lowValueCount,
            'medium_value' => $mediumValueCount,
            'high_value' => $highValueCount,
        ];
    }

    private function getPriorityAnalysisForMonth(Carbon $startDate, Carbon $endDate): array
    {
        $complaintOrders = Order::where('is_complaint', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['orderCarpets'])
            ->get();

        $priorities = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($complaintOrders as $order) {
            $priority = $this->determinePriority($order);
            $priorities[$priority['level']]++;
        }

        return $priorities;
    }


    private function addTitleSectionForMonth(TCPDF $pdf, string $month, Carbon $date): void
    {
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->Cell(0, 15, 'Raport Statystyk Skarg', 0, 1, 'C');
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Analiza Zamówień ze Skargami - ' . $this->getPolishMonth($date) . ' ' . $date->year, 0, 1, 'C'); // Use Polish month name
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 8, 'Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);
    }

    private function addDailyTrendSectionForMonth(TCPDF $pdf, array $dailyTrend): void
    {
         if ($pdf->GetY() > 200) {
            $pdf->AddPage();
        }
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Trend dzienny (wybrany miesiąc)', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(245, 245, 245);
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.25, // Day
            $pageWidth * 0.25, // New
            $pageWidth * 0.25, // Completed
            $pageWidth * 0.25, // Net Change
        ];
      
        $pdf->Cell($col_widths[0], 7, 'Dzień', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Nowe', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Ukończone', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Zmiana netto', 1, 1, 'C', true);
        foreach ($dailyTrend['days'] as $index => $day) {
            $newCount = $dailyTrend['new_complaints'][$index] ?? 0;
            $completedCount = $dailyTrend['completed_complaints'][$index] ?? 0;
            $netChange = $newCount - $completedCount;
            $pdf->Cell($col_widths[0], 6, $day, 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $newCount, 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $completedCount, 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, ($netChange >= 0 ? '+' : '') . $netChange, 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    private function addRecentComplaintsSectionForMonth(TCPDF $pdf, $recentComplaints): void
    {
        if ($recentComplaints->isEmpty()) {
            return;
        }
        if ($pdf->GetY() > 180) {
            $pdf->AddPage();
        }
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Najnowsze skargi (10 ostatnich w miesiącu)', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetFillColor(245, 245, 245);
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.08, // ID
            $pageWidth * 0.12, // Date
            $pageWidth * 0.15, // Status
            $pageWidth * 0.20, // Client
            $pageWidth * 0.15, // Driver
            $pageWidth * 0.10, // Carpets
            $pageWidth * 0.12, // Amount
            $pageWidth * 0.08, // Priority
        ];
        $pdf->Cell($col_widths[0], 7, 'ID', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Data', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Klient', 1, 0, 'C', true);
        $pdf->Cell($col_widths[4], 7, 'Kierowca', 1, 0, 'C', true);
        $pdf->Cell($col_widths[5], 7, 'Dywany', 1, 0, 'C', true);
        $pdf->Cell($col_widths[6], 7, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($col_widths[7], 7, 'Prior.', 1, 1, 'C', true);
        foreach ($recentComplaints as $complaint) {
            $pdf->Cell($col_widths[0], 6, '#' . $complaint['id'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $complaint['created_at'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $this->getOrderStatusLabel($complaint['status']), 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, mb_substr($complaint['client_name'], 0, 15), 1, 0, 'L');
            $pdf->Cell($col_widths[4], 6, mb_substr($complaint['driver_name'], 0, 12), 1, 0, 'L');
            $pdf->Cell($col_widths[5], 6, $complaint['carpet_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[6], 6, number_format($complaint['total_amount'], 0) . ' zł', 1, 0, 'R');
            $pdf->Cell($col_widths[7], 6, substr($complaint['priority']['label'], 0, 3), 1, 1, 'C');
        }
        $pdf->Ln(5);
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
}
