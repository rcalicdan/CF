<?php

namespace App\ActionService;

use TCPDF;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\Complaint;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class MonthlyClientReportService
{
    public function generateMonthlyPdfReport(int $month, int $year): string
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $clients = $this->getClientsWithOrdersInPeriod($startOfMonth, $endOfMonth);
        
        if ($clients->isEmpty()) {
            throw new \Exception('No clients with orders found for the selected period.');
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Raport Miesięczny Klientów - ' . $startOfMonth->format('F Y'));
        $pdf->SetSubject('Raport miesięczny wszystkich klientów');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();
        $this->addPdfTitleSection($pdf, $startOfMonth);
        $this->addPdfSummarySection($pdf, $clients, $startOfMonth, $endOfMonth);

        foreach ($clients as $client) {
            $this->addClientDetailsToPdf($pdf, $client, $startOfMonth, $endOfMonth);
        }

        $filename = 'raport_miesięczny_klientów_' . $startOfMonth->format('Y-m') . '_' . Carbon::now()->format('H-i') . '.pdf';
        $pdfContent = $pdf->Output('', 'S');
        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }

    public function generateMonthlyCsvReport(int $month, int $year): string
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $clients = $this->getClientsWithOrdersInPeriod($startOfMonth, $endOfMonth);
        
        if ($clients->isEmpty()) {
            throw new \Exception('No clients with orders found for the selected period.');
        }

        $csv = Writer::createFromString();
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');

        // Headers
        $headers = [
            'ID Klienta',
            'Imię i Nazwisko',
            'Email',
            'Telefon',
            'Adres',
            'ID Zamówienia',
            'Data Zamówienia',
            'Status Zamówienia',
            'Kwota Zamówienia (zł)',
            'ID Dywanu',
            'Kod Dywanu',
            'Wymiary Dywanu',
            'Powierzchnia (m²)',
            'Status Dywanu',
            'Cena Dywanu (zł)',
            'Usługi',
            'ID Skargi',
            'Opis Skargi',
            'Status Skargi',
            'ID Płatności',
            'Kwota Płatności (zł)',
            'Metoda Płatności',
            'Status Płatności',
            'Data Płatności'
        ];

        $csv->insertOne($headers);

        $records = [];
        foreach ($clients as $client) {
            $orders = $client->orders()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->with(['orderCarpets.services', 'orderCarpets.complaint', 'orderPayment'])
                ->get();

            foreach ($orders as $order) {
                if ($order->orderCarpets->isNotEmpty()) {
                    foreach ($order->orderCarpets as $carpet) {
                        $services = $carpet->services->pluck('name')->implode(', ');
                        
                        $records[] = [
                            $client->id,
                            $client->full_name,
                            $client->email ?? '',
                            $client->phone_number,
                            $client->full_address ?? '',
                            $order->id,
                            $order->created_at->format('d.m.Y H:i'),
                            ucfirst($order->status ?? ''),
                            $this->formatNumber($order->total_amount ?? 0),
                            $carpet->id,
                            $carpet->reference_code ?? '',
                            ($carpet->width ?? 0) . 'x' . ($carpet->height ?? 0) . 'm',
                            $this->formatNumber($carpet->total_area ?? 0),
                            ucfirst($carpet->status ?? ''),
                            $this->formatNumber($carpet->total_price ?? 0),
                            $services,
                            $carpet->complaint?->id ?? '',
                            $carpet->complaint?->complaint_details ?? '',
                            $carpet->complaint?->status ? ucfirst($carpet->complaint->status) : '',
                            $order->orderPayment?->id ?? '',
                            $order->orderPayment ? $this->formatNumber($order->orderPayment->amount_paid) : '',
                            $order->orderPayment?->payment_method ?? '',
                            $order->orderPayment?->status ? ucfirst($order->orderPayment->status) : '',
                            $order->orderPayment?->paid_at?->format('d.m.Y H:i') ?? ''
                        ];
                    }
                } else {
                    // Order without carpets
                    $records[] = [
                        $client->id,
                        $client->full_name,
                        $client->email ?? '',
                        $client->phone_number,
                        $client->full_address ?? '',
                        $order->id,
                        $order->created_at->format('d.m.Y H:i'),
                        ucfirst($order->status ?? ''),
                        $this->formatNumber($order->total_amount ?? 0),
                        '', '', '', '', '', '', '', '', '', '',
                        $order->orderPayment?->id ?? '',
                        $order->orderPayment ? $this->formatNumber($order->orderPayment->amount_paid) : '',
                        $order->orderPayment?->payment_method ?? '',
                        $order->orderPayment?->status ? ucfirst($order->orderPayment->status) : '',
                        $order->orderPayment?->paid_at?->format('d.m.Y H:i') ?? ''
                    ];
                }
            }
        }

        $csv->insertAll($records);

        $filename = 'raport_miesięczny_klientów_' . $startOfMonth->format('Y-m') . '_' . Carbon::now()->format('H-i') . '.csv';
        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    private function getClientsWithOrdersInPeriod(Carbon $startDate, Carbon $endDate)
    {
        return Client::whereHas('orders', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->with(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->with(['orderCarpets.services', 'orderCarpets.complaint', 'orderPayment', 'driver.user']);
        }])
        ->get();
    }

    private function addPdfTitleSection(TCPDF $pdf, Carbon $period): void
    {
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->Cell(0, 15, 'Raport Miesięczny Klientów', 0, 1, 'C');

        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, $period->format('F Y'), 0, 1, 'C');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 8, 'Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);
    }

    private function addPdfSummarySection(TCPDF $pdf, $clients, Carbon $startDate, Carbon $endDate): void
    {
        $totalOrders = 0;
        $totalCarpets = 0;
        $totalAmount = 0;
        $totalComplaints = 0;

        foreach ($clients as $client) {
            foreach ($client->orders as $order) {
                $totalOrders++;
                $totalCarpets += $order->orderCarpets->count();
                $totalAmount += $order->total_amount ?? 0;
                $totalComplaints += $order->orderCarpets->whereNotNull('complaint')->count();
            }
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Podsumowanie okresu', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(240, 240, 240);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.6;
        $col2_width = $pageWidth * 0.4;

        $summaryData = [
            ['Liczba klientów', number_format($clients->count(), 0, ',', ' ')],
            ['Całkowita liczba zamówień', number_format($totalOrders, 0, ',', ' ')],
            ['Całkowita liczba dywanów', number_format($totalCarpets, 0, ',', ' ')],
            ['Całkowita wartość zamówień', number_format($totalAmount, 2, ',', ' ') . ' zł'],
            ['Liczba skarg', number_format($totalComplaints, 0, ',', ' ')],
            ['Średnia wartość zamówienia', $totalOrders > 0 ? number_format($totalAmount / $totalOrders, 2, ',', ' ') . ' zł' : '0,00 zł'],
        ];

        foreach ($summaryData as $row) {
            $pdf->Cell($col1_width, 8, $row[0], 1, 0, 'L', true);
            $pdf->Cell($col2_width, 8, $row[1], 1, 1, 'R');
        }
        $pdf->Ln(5);
    }

    private function addClientDetailsToPdf(TCPDF $pdf, Client $client, Carbon $startDate, Carbon $endDate): void
    {
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Klient: ' . $client->full_name . ' (ID: ' . $client->id . ')', 0, 1, 'L');

        $orders = $client->orders()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['orderCarpets.services', 'orderCarpets.complaint', 'orderPayment'])
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(245, 245, 245);

        $clientTotalAmount = $orders->sum('total_amount');
        $clientTotalCarpets = $orders->sum(fn($order) => $order->orderCarpets->count());

        $pdf->Cell(0, 6, 'Zamówień: ' . $orders->count() . ' | Dywany: ' . $clientTotalCarpets . ' | Wartość: ' . number_format($clientTotalAmount, 2, ',', ' ') . ' zł', 1, 1, 'L', true);

        foreach ($orders as $order) {
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->Cell(0, 6, 'Zamówienie #' . $order->id . ' (' . $order->created_at->format('d.m.Y') . ') - ' . ucfirst($order->status ?? ''), 0, 1, 'L');

            if ($order->orderCarpets->isNotEmpty()) {
                foreach ($order->orderCarpets as $carpet) {
                    $pdf->SetFont('dejavusans', '', 8);
                    $services = $carpet->services->pluck('name')->implode(', ');
                    $complaintText = $carpet->complaint ? ' [SKARGA: ' . $carpet->complaint->status . ']' : '';
                    
                    $pdf->Cell(0, 5, 
                        '  • Dywan: ' . ($carpet->reference_code ?? 'N/A') . 
                        ' | ' . ($carpet->width ?? 0) . 'x' . ($carpet->height ?? 0) . 'm' .
                        ' | ' . number_format($carpet->total_price ?? 0, 2, ',', ' ') . ' zł' .
                        ($services ? ' | Usługi: ' . $services : '') .
                        $complaintText, 
                        0, 1, 'L'
                    );
                }
            }

            if ($order->orderPayment) {
                $pdf->SetFont('dejavusans', '', 8);
                $pdf->Cell(0, 5, 
                    '  Płatność: ' . number_format($order->orderPayment->amount_paid, 2, ',', ' ') . ' zł' .
                    ' | ' . ($order->orderPayment->payment_method ?? 'N/A') .
                    ' | ' . ucfirst($order->orderPayment->status ?? '') .
                    ($order->orderPayment->paid_at ? ' | ' . $order->orderPayment->paid_at->format('d.m.Y') : ''), 
                    0, 1, 'L'
                );
            }
        }
        $pdf->Ln(3);
    }

    private function formatNumber(float $number): string
    {
        return number_format($number, 2, ',', ' ');
    }
}