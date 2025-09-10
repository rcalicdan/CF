<?php

namespace App\ActionService;

use TCPDF;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientPdfReportService
{
    public function generateClientReport(Client $client, $dateFrom = null, $dateTo = null): string
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('System');

        // Update title based on date filter
        $titleSuffix = '';
        if ($dateFrom || $dateTo) {
            $titleSuffix = ' - ';
            if ($dateFrom && $dateTo) {
                $titleSuffix .= $dateFrom->format('d.m.Y') . ' - ' . $dateTo->format('d.m.Y');
            } elseif ($dateFrom) {
                $titleSuffix .= 'od ' . $dateFrom->format('d.m.Y');
            } else {
                $titleSuffix .= 'do ' . $dateTo->format('d.m.Y');
            }
        }

        $pdf->SetTitle('Raport Klienta - ' . $client->full_name . $titleSuffix);
        $pdf->SetSubject('Raport szczegółowy klienta');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        // Get client data with date filters
        $clientData = $this->getClientData($client, $dateFrom, $dateTo);

        // Title Section
        $this->addTitleSection($pdf, $client, $dateFrom, $dateTo);

        // Client Information Section
        $this->addClientInformationSection($pdf, $client);

        // Date Range Section (if filtered)
        if ($dateFrom || $dateTo) {
            $this->addDateRangeSection($pdf, $dateFrom, $dateTo);
        }

        // Summary Statistics Section
        $this->addSummarySection($pdf, $clientData['stats']);

        // Orders Overview Section
        $this->addOrdersOverviewSection($pdf, $clientData['orders']);

        // Monthly Performance Section
        $this->addMonthlyPerformanceSection($pdf, $clientData['monthlyPerformance']);

        // Carpets Summary Section
        $this->addCarpetsSummarySection($pdf, $clientData['carpets']);

        // Revenue Analysis Section
        $this->addRevenueAnalysisSection($pdf, $clientData['revenueAnalysis']);

        $periodSuffix = '';
        if ($dateFrom || $dateTo) {
            $periodSuffix = '_' . ($dateFrom ? $dateFrom->format('Y-m-d') : 'do') .
                '_' . ($dateTo ? $dateTo->format('Y-m-d') : 'od');
        }

        $filename = 'raport_klienta_' . $client->id . $periodSuffix . '_' . Carbon::now()->format('Y-m-d_H-i') . '.pdf';
        $pdfContent = $pdf->Output('', 'S');
        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }

    private function getClientData(Client $client, $dateFrom = null, $dateTo = null): array
    {
        $ordersQuery = $client->orders();
        $carpetsQuery = OrderCarpet::whereHas('order', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        });

        if ($dateFrom) {
            $ordersQuery->where('created_at', '>=', $dateFrom);
            $carpetsQuery->whereHas('order', function ($query) use ($client, $dateFrom) {
                $query->where('client_id', $client->id)
                    ->where('created_at', '>=', $dateFrom);
            });
        }

        if ($dateTo) {
            $ordersQuery->where('created_at', '<=', $dateTo);
            $carpetsQuery->whereHas('order', function ($query) use ($client, $dateTo) {
                $query->where('client_id', $client->id)
                    ->where('created_at', '<=', $dateTo);
            });
        }

        $stats = [
            'total_orders' => $ordersQuery->count(),
            'completed_orders' => (clone $ordersQuery)->where('status', 'completed')->count(),
            'total_carpets' => $carpetsQuery->count(),
            'completed_carpets' => (clone $carpetsQuery)->where('status', 'completed')->count(),
            'total_spent' => $ordersQuery->sum('total_amount') ?? 0,
            'avg_order_value' => $ordersQuery->avg('total_amount') ?? 0,
            'total_area' => $carpetsQuery->sum('total_area') ?? 0,
        ];

        $orders = $ordersQuery->with(['orderCarpets'])
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'date' => $order->created_at->format('d.m.Y'),
                    'status' => $order->status ?? 'pending',
                    'carpets_count' => $order->orderCarpets->count(),
                    'total_amount' => $order->total_amount ?? 0,
                ];
            });

        $monthlyPerformance = $client->orders()
            ->when($dateFrom, fn($query) => $query->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->where('created_at', '<=', $dateTo))
            ->when(!$dateFrom && !$dateTo, fn($query) => $query->where('created_at', '>=', Carbon::now()->subMonths(12)))
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
                DB::raw('COALESCE(AVG(total_amount), 0) as avg_amount')
            )
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'orders_count' => $item->orders_count,
                    'total_amount' => $item->total_amount,
                    'avg_amount' => $item->avg_amount,
                ];
            });

        $carpets = $carpetsQuery->with(['order', 'services'])
            ->latest('created_at')
            ->take(15)
            ->get()
            ->map(function ($carpet) {
                return [
                    'reference_code' => $carpet->reference_code ?? 'N/A',
                    'order_id' => $carpet->order->id,
                    'dimensions' => ($carpet->width ?? 0) . 'x' . ($carpet->height ?? 0),
                    'area' => $carpet->total_area ?? 0,
                    'status' => $carpet->status ?? 'pending',
                    'total_price' => $carpet->total_price ?? 0,
                    'created_date' => $carpet->created_at->format('d.m.Y'),
                ];
            });

        $revenueAnalysis = [
            'completed' => (clone $ordersQuery)->where('status', 'completed')->sum('total_amount') ?? 0,
            'pending' => (clone $ordersQuery)->where('status', 'pending')->sum('total_amount') ?? 0,
            'in_progress' => (clone $ordersQuery)->where('status', 'in_progress')->sum('total_amount') ?? 0,
        ];

        return [
            'stats' => $stats,
            'orders' => $orders,
            'monthlyPerformance' => $monthlyPerformance,
            'carpets' => $carpets,
            'revenueAnalysis' => $revenueAnalysis,
        ];
    }

    private function addTitleSection(TCPDF $pdf, Client $client, $dateFrom = null, $dateTo = null): void
    {
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->Cell(0, 15, 'Raport Klienta', 0, 1, 'C');

        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, $client->full_name, 0, 1, 'C');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 8, 'ID Klienta: #' . $client->id, 0, 1, 'C');
        $pdf->Cell(0, 8, 'Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i'), 0, 1, 'C');

        // Add period info if filtered
        if ($dateFrom || $dateTo) {
            $periodText = 'Okres: ';
            if ($dateFrom && $dateTo) {
                $periodText .= $dateFrom->format('d.m.Y') . ' - ' . $dateTo->format('d.m.Y');
            } elseif ($dateFrom) {
                $periodText .= 'od ' . $dateFrom->format('d.m.Y');
            } else {
                $periodText .= 'do ' . $dateTo->format('d.m.Y');
            }
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(0, 8, $periodText, 0, 1, 'C');
        }

        $pdf->Ln(5);
    }

    private function addDateRangeSection(TCPDF $pdf, $dateFrom = null, $dateTo = null): void
    {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Zakres dat raportu', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.3;
        $col2_width = $pageWidth * 0.7;

        $pdf->Cell($col1_width, 8, 'Data od', 1, 0, 'L', true);
        $pdf->Cell($col2_width, 8, $dateFrom ? $dateFrom->format('d.m.Y') : 'Nie określono', 1, 1, 'L');

        $pdf->Cell($col1_width, 8, 'Data do', 1, 0, 'L', true);
        $pdf->Cell($col2_width, 8, $dateTo ? $dateTo->format('d.m.Y') : 'Nie określono', 1, 1, 'L');

        $pdf->Ln(5);
    }

    private function addClientInformationSection(TCPDF $pdf, Client $client): void
    {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Informacje o kliencie', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetDrawColor(200, 200, 200);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.3;
        $col2_width = $pageWidth * 0.7;

        $clientInfo = [
            ['Imię i nazwisko', $client->full_name ?? 'N/A'],
            ['Email', $client->email ?? 'Nie podano'],
            ['Telefon', $client->phone_number ?? 'Nie podano'],
            ['Adres', $client->full_address ?? 'Nie podano'],
            ['Data rejestracji', $client->created_at->format('d.m.Y')],
            ['Ostatnia aktualizacja', $client->updated_at->format('d.m.Y H:i')],
        ];

        foreach ($clientInfo as $info) {
            $pdf->Cell($col1_width, 8, $info[0], 1, 0, 'L', true);
            $pdf->Cell($col2_width, 8, $info[1], 1, 1, 'L');
        }
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
            ['Całkowita liczba zamówień', number_format($stats['total_orders'], 0, ',', ' ')],
            ['Ukończone zamówienia', number_format($stats['completed_orders'], 0, ',', ' ')],
            ['Całkowita liczba dywanów', number_format($stats['total_carpets'], 0, ',', ' ')],
            ['Ukończone dywany', number_format($stats['completed_carpets'], 0, ',', ' ')],
            ['Całkowity wydatek', number_format($stats['total_spent'], 2, ',', ' ') . ' zł'],
            ['Średnia wartość zamówienia', number_format($stats['avg_order_value'], 2, ',', ' ') . ' zł'],
            ['Całkowita powierzchnia', number_format($stats['total_area'], 2, ',', ' ') . ' m²'],
            ['Współczynnik ukończenia zamówień', number_format($stats['total_orders'] > 0 ? ($stats['completed_orders'] / $stats['total_orders']) * 100 : 0, 1, ',', ' ') . '%'],
        ];

        foreach ($summaryData as $row) {
            $pdf->Cell($col1_width, 8, $row[0], 1, 0, 'L', true);
            $pdf->Cell($col2_width, 8, $row[1], 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addOrdersOverviewSection(TCPDF $pdf, $orders): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Ostatnie zamówienia (10 najnowszych)', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.12, // ID
            $pageWidth * 0.18, // Date
            $pageWidth * 0.20, // Status
            $pageWidth * 0.15, // Carpets
            $pageWidth * 0.35, // Amount
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'ID', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Data', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Dywany', 1, 0, 'C', true);
        $pdf->Cell($col_widths[4], 7, 'Kwota (zł)', 1, 1, 'C', true);

        foreach ($orders as $order) {
            $pdf->Cell($col_widths[0], 6, '#' . $order['id'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $order['date'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, ucfirst($order['status']), 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, $order['carpets_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[4], 6, number_format($order['total_amount'], 2, ',', ' '), 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addMonthlyPerformanceSection(TCPDF $pdf, $monthlyData): void
    {
        if ($monthlyData->isEmpty()) {
            return;
        }

        if ($pdf->GetY() > 230) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Wydajność miesięczna', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.25, // Month
            $pageWidth * 0.25, // Orders
            $pageWidth * 0.25, // Total Amount
            $pageWidth * 0.25, // Avg Amount
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'Miesiąc', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Zamówienia', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Suma (zł)', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Średnia (zł)', 1, 1, 'C', true);

        foreach ($monthlyData as $month) {
            $pdf->Cell($col_widths[0], 6, $month['month'], 1, 0, 'C');
            $pdf->Cell($col_widths[1], 6, $month['orders_count'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, number_format($month['total_amount'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col_widths[3], 6, number_format($month['avg_amount'], 2, ',', ' '), 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addCarpetsSummarySection(TCPDF $pdf, $carpets): void
    {
        if ($carpets->isEmpty()) {
            return;
        }

        if ($pdf->GetY() > 200) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Ostatnie dywany (15 najnowszych)', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col_widths = [
            $pageWidth * 0.15, // Reference
            $pageWidth * 0.10, // Order ID
            $pageWidth * 0.15, // Dimensions
            $pageWidth * 0.12, // Area
            $pageWidth * 0.15, // Status
            $pageWidth * 0.15, // Price
            $pageWidth * 0.18, // Date
        ];

        // Header
        $pdf->Cell($col_widths[0], 7, 'Kod', 1, 0, 'C', true);
        $pdf->Cell($col_widths[1], 7, 'Zam. ID', 1, 0, 'C', true);
        $pdf->Cell($col_widths[2], 7, 'Wymiary', 1, 0, 'C', true);
        $pdf->Cell($col_widths[3], 7, 'Obszar m²', 1, 0, 'C', true);
        $pdf->Cell($col_widths[4], 7, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col_widths[5], 7, 'Cena (zł)', 1, 0, 'C', true);
        $pdf->Cell($col_widths[6], 7, 'Data', 1, 1, 'C', true);

        foreach ($carpets as $carpet) {
            $pdf->Cell($col_widths[0], 6, $carpet['reference_code'], 1, 0, 'L');
            $pdf->Cell($col_widths[1], 6, '#' . $carpet['order_id'], 1, 0, 'C');
            $pdf->Cell($col_widths[2], 6, $carpet['dimensions'] . 'm', 1, 0, 'C');
            $pdf->Cell($col_widths[3], 6, number_format($carpet['area'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col_widths[4], 6, ucfirst($carpet['status']), 1, 0, 'C');
            $pdf->Cell($col_widths[5], 6, number_format($carpet['total_price'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col_widths[6], 6, $carpet['created_date'], 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    private function addRevenueAnalysisSection(TCPDF $pdf, array $revenueAnalysis): void
    {
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Analiza przychodów według statusu', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.5;
        $col2_width = $pageWidth * 0.25;
        $col3_width = $pageWidth * 0.25;

        $total = array_sum($revenueAnalysis);

        $pdf->Cell($col1_width, 8, 'Status', 1, 0, 'C', true);
        $pdf->Cell($col2_width, 8, 'Kwota (zł)', 1, 0, 'C', true);
        $pdf->Cell($col3_width, 8, 'Procent', 1, 1, 'C', true);

        $statusLabels = [
            'completed' => 'Ukończone',
            'in_progress' => 'W trakcie',
            'pending' => 'Oczekujące',
        ];

        foreach ($revenueAnalysis as $status => $amount) {
            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
            $pdf->Cell($col1_width, 7, $statusLabels[$status] ?? ucfirst($status), 1, 0, 'L');
            $pdf->Cell($col2_width, 7, number_format($amount, 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($col3_width, 7, number_format($percentage, 1, ',', ' ') . '%', 1, 1, 'R');
        }

        // Total row
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell($col1_width, 7, 'RAZEM', 1, 0, 'L');
        $pdf->Cell($col2_width, 7, number_format($total, 2, ',', ' '), 1, 0, 'R');
        $pdf->Cell($col3_width, 7, '100.0%', 1, 1, 'R');
    }
}
