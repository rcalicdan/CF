<?php

namespace App\ActionService;

use TCPDF;
use Carbon\Carbon;
use App\Models\Complaint;
use App\Models\OrderCarpet;
use App\Enums\ComplaintStatus;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Facades\DB;
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
        $this->addCategoryAnalysisSection($pdf, $complaintData['categoryStats']);

        // Weekly Trend Section
        $this->addWeeklyTrendSection($pdf, $complaintData['weeklyTrend']);

        // Recent Complaints Section
        $this->addRecentComplaintsSection($pdf, $complaintData['recentComplaints']);

        // Monthly Performance Section
        $this->addMonthlyPerformanceSection($pdf, $complaintData['monthlyPerformance']);

        // Resolution Analysis Section
        $this->addResolutionAnalysisSection($pdf, $complaintData['resolutionAnalysis']);

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
        $totalComplaints = Complaint::where('created_at', '>=', $startDate)->count();
        $openComplaints = Complaint::where('created_at', '>=', $startDate)
            ->where('status', ComplaintStatus::OPEN->value)->count();
        $inProgressComplaints = Complaint::where('created_at', '>=', $startDate)
            ->where('status', ComplaintStatus::IN_PROGRESS->value)->count();
        $resolvedComplaints = Complaint::where('created_at', '>=', $startDate)
            ->where('status', ComplaintStatus::RESOLVED->value)->count();
        $rejectedComplaints = Complaint::where('created_at', '>=', $startDate)
            ->where('status', ComplaintStatus::REJECTED->value)->count();
        $closedComplaints = Complaint::where('created_at', '>=', $startDate)
            ->where('status', ComplaintStatus::CLOSED->value)->count();

        $activeComplaints = $openComplaints + $inProgressComplaints;
        $resolutionRate = $totalComplaints > 0 ? round((($resolvedComplaints + $closedComplaints) / $totalComplaints) * 100, 1) : 0;

        // Previous period comparison
        $previousStartDate = Carbon::now()->subDays($days * 2);
        $previousEndDate = Carbon::now()->subDays($days);
        $previousPeriodTotal = Complaint::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $periodChange = $previousPeriodTotal > 0 ? $totalComplaints - $previousPeriodTotal : 0;

        $stats = [
            'total' => $totalComplaints,
            'active' => $activeComplaints,
            'resolved' => $resolvedComplaints,
            'rejected' => $rejectedComplaints,
            'closed' => $closedComplaints,
            'open' => $openComplaints,
            'in_progress' => $inProgressComplaints,
            'resolution_rate' => $resolutionRate,
            'period_change' => $periodChange,
            'avg_resolution_time' => $this->getAverageResolutionTime($days),
        ];

        // Status distribution
        $statusDistribution = [
            'open' => $openComplaints,
            'in_progress' => $inProgressComplaints,
            'resolved' => $resolvedComplaints,
            'rejected' => $rejectedComplaints,
            'closed' => $closedComplaints,
        ];

        // Category statistics
        $categoryStats = $this->getCategoryStats($days);

        // Weekly trend (last 7 days)
        $weeklyTrend = $this->getWeeklyTrend();

        // Recent complaints
        $recentComplaints = Complaint::with(['orderCarpet.order.client'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'details' => mb_substr($complaint->complaint_details, 0, 50) . '...',
                    'status' => $complaint->status,
                    'created_at' => $complaint->created_at->format('d.m.Y'),
                    'client_name' => $complaint->orderCarpet?->order?->client?->full_name ?? 'N/A',
                    'order_id' => $complaint->orderCarpet?->order?->id ?? 'N/A',
                    'carpet_ref' => $complaint->orderCarpet?->reference_code ?? 'N/A',
                    'priority' => $this->determinePriority($complaint),
                ];
            });

        // Monthly performance
        $monthlyPerformance = $this->getMonthlyPerformance();

        // Resolution analysis
        $resolutionAnalysis = $this->getResolutionAnalysis($days);

        // Priority analysis
        $priorityAnalysis = $this->getPriorityAnalysis($days);

        return [
            'stats' => $stats,
            'statusDistribution' => $statusDistribution,
            'categoryStats' => $categoryStats,
            'weeklyTrend' => $weeklyTrend,
            'recentComplaints' => $recentComplaints,
            'monthlyPerformance' => $monthlyPerformance,
            'resolutionAnalysis' => $resolutionAnalysis,
            'priorityAnalysis' => $priorityAnalysis,
        ];
    }

    private function addTitleSection(TCPDF $pdf, int $days): void
    {
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->Cell(0, 15, 'Raport Statystyk Skarg', 0, 1, 'C');

        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Analiza Jakości Serwisu i Efektywności', 0, 1, 'C');

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
            ['Nowe skargi', number_format($stats['open'], 0, ',', ' ')],
            ['Skargi w trakcie', number_format($stats['in_progress'], 0, ',', ' ')],
            ['Rozwiązane skargi', number_format($stats['resolved'], 0, ',', ' ')],
            ['Odrzucone skargi', number_format($stats['rejected'], 0, ',', ' ')],
            ['Zamknięte skargi', number_format($stats['closed'], 0, ',', ' ')],
            ['Współczynnik rozwiązań', number_format($stats['resolution_rate'], 1, ',', ' ') . '%'],
            ['Zmiana względem poprzedniego okresu', ($stats['period_change'] >= 0 ? '+' : '') . number_format($stats['period_change'], 0, ',', ' ')],
            ['Średni czas rozwiązania', number_format($stats['avg_resolution_time'], 1, ',', ' ') . ' dni'],
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
            'open' => 'Nowe',
            'in_progress' => 'W trakcie',
            'resolved' => 'Rozwiązane',
            'rejected' => 'Odrzucone',
            'closed' => 'Zamknięte',
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
            'communication' => 'Komunikacja',
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
            $pageWidth * 0.25, // Resolved
            $pageWidth * 0.25, // Net Change
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'Dzień', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Nowe', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Rozwiązane', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Zmiana netto', 1, 1, 'C', true);

        foreach ($weeklyTrend['days'] as $index => $day) {
            $newCount = $weeklyTrend['new_complaints'][$index] ?? 0;
            $resolvedCount = $weeklyTrend['resolved_complaints'][$index] ?? 0;
            $netChange = $newCount - $resolvedCount;

            $pdf->Cell($col_widths[0], 6, $day, 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $newCount, 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $resolvedCount, 1, 0, 'C');
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
            $pageWidth * 0.35, // Details
            $pageWidth * 0.15, // Status
            $pageWidth * 0.15, // Client
            $pageWidth * 0.15, // Priority
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'ID', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Data', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Opis', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col_widths[4], 7, 'Klient', 1, 0, 'C', true);
        $pdf->Cell($col_widths[5], 7, 'Priorytet', 1, 1, 'C', true);

        foreach ($recentComplaints as $complaint) {
            $pdf->Cell($col_widths[0], 6, '#' . $complaint['id'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $complaint['created_at'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $complaint['details'], 1, 0, 'L');
            $pdf->Cell($col_widths[3], 6, $this->getStatusLabel($complaint['status']), 1, 0, 'C');
            $pdf->Cell($col_widths[4], 6, mb_substr($complaint['client_name'], 0, 15), 1, 0, 'L');
            $pdf->Cell($col_widths[5], 6, $complaint['priority']['label'], 1, 1, 'C');
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
            $pageWidth * 0.25, // Resolved
            $pageWidth * 0.25, // Resolution Rate
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'Miesiąc', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Nowe', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Rozwiązane', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Wsp. rozw.', 1, 1, 'C', true);

        foreach ($monthlyPerformance as $month) {
            $pdf->Cell($col_widths[0], 6, $month['month'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $month['new_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $month['resolved_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, number_format($month['resolution_rate'], 1) . '%', 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    private function addResolutionAnalysisSection(TCPDF $pdf, array $resolutionAnalysis): void
    {
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Analiza czasu rozwiązywania', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.6;
        $col2_width = $pageWidth * 0.4;

        $resolutionData = [
            ['Średni czas rozwiązania', number_format($resolutionAnalysis['avg_resolution_time'], 1, ',', ' ') . ' dni'],
            ['Najkrótszy czas', number_format($resolutionAnalysis['min_resolution_time'], 1, ',', ' ') . ' dni'],
            ['Najdłuższy czas', number_format($resolutionAnalysis['max_resolution_time'], 1, ',', ' ') . ' dni'],
            ['Skargi rozwiązane < 1 dzień', number_format($resolutionAnalysis['same_day'], 0, ',', ' ')],
            ['Skargi rozwiązane 1-3 dni', number_format($resolutionAnalysis['one_to_three_days'], 0, ',', ' ')],
            ['Skargi rozwiązane > 3 dni', number_format($resolutionAnalysis['over_three_days'], 0, ',', ' ')],
        ];

        foreach ($resolutionData as $row) {
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

    // Helper methods
    private function getCategoryStats(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);
        $carpetsWithComplaints = OrderCarpet::where('status', OrderCarpetStatus::COMPLAINT->value)
            ->whereHas('complaint', function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->with(['complaint'])
            ->get();

        $categories = [
            'damage' => 0,
            'delay' => 0,
            'quality' => 0,
            'communication' => 0,
            'other' => 0
        ];

        foreach ($carpetsWithComplaints as $carpet) {
            if ($carpet->complaint) {
                $details = mb_strtolower($carpet->complaint->complaint_details);
                if (str_contains($details, 'uszkodz') || str_contains($details, 'zniszcz') || str_contains($details, 'rozdarcie')) {
                    $categories['damage']++;
                } elseif (str_contains($details, 'opóźnien') || str_contains($details, 'późno') || str_contains($details, 'czas')) {
                    $categories['delay']++;
                } elseif (str_contains($details, 'jakość') || str_contains($details, 'pranie') || str_contains($details, 'czyszczenie')) {
                    $categories['quality']++;
                } elseif (str_contains($details, 'komunikacja') || str_contains($details, 'kontakt') || str_contains($details, 'informacj')) {
                    $categories['communication']++;
                } else {
                    $categories['other']++;
                }
            }
        }

        return $categories;
    }

    private function getWeeklyTrend(): array
    {
        $days = [];
        $newComplaints = [];
        $resolvedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D d.m');

            $newCount = Complaint::whereDate('created_at', $date)->count();
            $resolvedCount = Complaint::whereIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value
            ])
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

    private function getMonthlyPerformance(): array
    {
        $performance = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $newCount = Complaint::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $resolvedCount = Complaint::whereIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value
            ])
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count();

            $resolutionRate = $newCount > 0 ? ($resolvedCount / $newCount) * 100 : 0;

            $performance[] = [
                'month' => $date->format('M Y'),
                'new_count' => $newCount,
                'resolved_count' => $resolvedCount,
                'resolution_rate' => $resolutionRate,
            ];
        }

        return $performance;
    }

    private function getAverageResolutionTime(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);

        $resolvedComplaints = Complaint::whereIn('status', [
            ComplaintStatus::RESOLVED->value,
            ComplaintStatus::CLOSED->value
        ])
            ->where('created_at', '>=', $startDate)
            ->get();

        if ($resolvedComplaints->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($resolvedComplaints as $complaint) {
            $totalDays += $complaint->created_at->diffInDays($complaint->updated_at);
        }

        return round($totalDays / $resolvedComplaints->count(), 1);
    }

    private function getResolutionAnalysis(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $resolvedComplaints = Complaint::whereIn('status', [
            ComplaintStatus::RESOLVED->value,
            ComplaintStatus::CLOSED->value
        ])
            ->where('created_at', '>=', $startDate)
            ->get();

        if ($resolvedComplaints->isEmpty()) {
            return [
                'avg_resolution_time' => 0,
                'min_resolution_time' => 0,
                'max_resolution_time' => 0,
                'same_day' => 0,
                'one_to_three_days' => 0,
                'over_three_days' => 0,
            ];
        }

        $resolutionTimes = [];
        $sameDayCount = 0;
        $oneToThreeDaysCount = 0;
        $overThreeDaysCount = 0;

        foreach ($resolvedComplaints as $complaint) {
            $resolutionTime = $complaint->created_at->diffInDays($complaint->updated_at);
            $resolutionTimes[] = $resolutionTime;

            if ($resolutionTime == 0) {
                $sameDayCount++;
            } elseif ($resolutionTime <= 3) {
                $oneToThreeDaysCount++;
            } else {
                $overThreeDaysCount++;
            }
        }

        return [
            'avg_resolution_time' => count($resolutionTimes) > 0 ? round(array_sum($resolutionTimes) / count($resolutionTimes), 1) : 0,
            'min_resolution_time' => count($resolutionTimes) > 0 ? min($resolutionTimes) : 0,
            'max_resolution_time' => count($resolutionTimes) > 0 ? max($resolutionTimes) : 0,
            'same_day' => $sameDayCount,
            'one_to_three_days' => $oneToThreeDaysCount,
            'over_three_days' => $overThreeDaysCount,
        ];
    }

    private function getPriorityAnalysis(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $complaints = Complaint::where('created_at', '>=', $startDate)->get();

        $priorities = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($complaints as $complaint) {
            $priority = $this->determinePriority($complaint);
            $priorities[$priority['level']]++;
        }

        return $priorities;
    }

    private function determinePriority($complaint): array
    {
        $details = mb_strtolower($complaint->complaint_details);

        if (str_contains($details, 'uszkodz') || str_contains($details, 'zniszcz')) {
            return ['level' => 'high', 'label' => 'Wysoki'];
        } elseif (str_contains($details, 'opóźnien') || str_contains($details, 'komunikacja')) {
            return ['level' => 'medium', 'label' => 'Średni'];
        } else {
            return ['level' => 'low', 'label' => 'Niski'];
        }
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            ComplaintStatus::OPEN->value => 'Nowa',
            ComplaintStatus::IN_PROGRESS->value => 'W trakcie',
            ComplaintStatus::RESOLVED->value => 'Rozwiązana',
            ComplaintStatus::REJECTED->value => 'Odrzucona',
            ComplaintStatus::CLOSED->value => 'Zamknięta',
            default => 'Nieznany',
        };
    }
}
