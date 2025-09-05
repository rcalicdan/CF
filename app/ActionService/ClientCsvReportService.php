<?php

namespace App\ActionService;

use Carbon\Carbon;
use League\Csv\Writer;
use League\Csv\EscapeFormula;
use App\Models\Client;
use App\Models\OrderCarpet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientCsvReportService
{
    public function generateClientCsvReport(Client $client): string
    {
        $filename = 'raport_klienta_csv_' . $client->id . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        $csv = Writer::createFromString('');

        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->addFormatter(new EscapeFormula());

        // Get client data
        $clientData = $this->getClientData($client);

        // Header
        $csv->insertOne(['Raport Klienta - ' . $client->full_name]);
        $csv->insertOne(['ID Klienta: #' . $client->id]);
        $csv->insertOne(['Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i')]);
        $csv->insertOne([]);

        // Client Information section
        $this->addClientInformationSection($csv, $client);

        // Summary Statistics section
        $this->addSummarySection($csv, $clientData['stats']);

        // Orders Overview section
        $this->addOrdersOverviewSection($csv, $clientData['orders']);

        // Monthly Performance section
        $this->addMonthlyPerformanceSection($csv, $clientData['monthlyPerformance']);

        // Carpets Summary section
        $this->addCarpetsSummarySection($csv, $clientData['carpets']);

        // Revenue Analysis section
        $this->addRevenueAnalysisSection($csv, $clientData['revenueAnalysis']);

        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    private function getClientData(Client $client): array
    {
        // Basic stats
        $stats = [
            'total_orders' => $client->orders()->count(),
            'completed_orders' => $client->orders()->where('status', 'completed')->count(),
            'total_carpets' => OrderCarpet::whereHas('order', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->count(),
            'completed_carpets' => OrderCarpet::whereHas('order', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->where('status', 'completed')->count(),
            'total_spent' => $client->orders()->sum('total_amount') ?? 0,
            'avg_order_value' => $client->orders()->avg('total_amount') ?? 0,
            'total_area' => OrderCarpet::whereHas('order', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->sum('total_area') ?? 0,
        ];

        // Recent orders (last 20 for CSV)
        $orders = $client->orders()
            ->with(['orderCarpets'])
            ->latest('created_at')
            ->take(20)
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

        // Monthly performance (last 12 months)
        $monthlyPerformance = $client->orders()
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
                DB::raw('COALESCE(AVG(total_amount), 0) as avg_amount')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
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

        // Carpets data (last 25 for CSV)
        $carpets = OrderCarpet::whereHas('order', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })
            ->with(['order', 'services'])
            ->latest('created_at')
            ->take(25)
            ->get()
            ->map(function ($carpet) {
                return [
                    'reference_code' => $carpet->reference_code ?? 'N/A',
                    'order_id' => $carpet->order->id,
                    'width' => $carpet->width ?? 0,
                    'height' => $carpet->height ?? 0,
                    'area' => $carpet->total_area ?? 0,
                    'status' => $carpet->status ?? 'pending',
                    'total_price' => $carpet->total_price ?? 0,
                    'created_date' => $carpet->created_at->format('d.m.Y'),
                ];
            });

        // Revenue analysis by status
        $revenueAnalysis = [
            'completed' => $client->orders()->where('status', 'completed')->sum('total_amount') ?? 0,
            'pending' => $client->orders()->where('status', 'pending')->sum('total_amount') ?? 0,
            'in_progress' => $client->orders()->where('status', 'in_progress')->sum('total_amount') ?? 0,
        ];

        return [
            'stats' => $stats,
            'orders' => $orders,
            'monthlyPerformance' => $monthlyPerformance,
            'carpets' => $carpets,
            'revenueAnalysis' => $revenueAnalysis,
        ];
    }

    private function addClientInformationSection(Writer $csv, Client $client): void
    {
        $csv->insertOne(['INFORMACJE O KLIENCIE']);
        $csv->insertOne(['Pole', 'Wartość']);
        
        $csv->insertOne(['Imię i nazwisko', $client->full_name ?? 'N/A']);
        $csv->insertOne(['Email', $client->email ?? 'Nie podano']);
        $csv->insertOne(['Telefon', $client->phone_number ?? 'Nie podano']);
        $csv->insertOne(['Adres', $client->full_address ?? 'Nie podano']);
        $csv->insertOne(['Data rejestracji', $client->created_at->format('d.m.Y')]);
        $csv->insertOne(['Ostatnia aktualizacja', $client->updated_at->format('d.m.Y H:i')]);
        $csv->insertOne([]);
    }

    private function addSummarySection(Writer $csv, array $stats): void
    {
        $csv->insertOne(['PODSUMOWANIE STATYSTYK']);
        $csv->insertOne(['Metryka', 'Wartość']);
        
        $csv->insertOne(['Całkowita liczba zamówień', number_format($stats['total_orders'], 0, ',', ' ')]);
        $csv->insertOne(['Ukończone zamówienia', number_format($stats['completed_orders'], 0, ',', ' ')]);
        $csv->insertOne(['Całkowita liczba dywanów', number_format($stats['total_carpets'], 0, ',', ' ')]);
        $csv->insertOne(['Ukończone dywany', number_format($stats['completed_carpets'], 0, ',', ' ')]);
        $csv->insertOne(['Całkowity wydatek (zł)', number_format($stats['total_spent'], 2, ',', ' ')]);
        $csv->insertOne(['Średnia wartość zamówienia (zł)', number_format($stats['avg_order_value'], 2, ',', ' ')]);
        $csv->insertOne(['Całkowita powierzchnia (m²)', number_format($stats['total_area'], 2, ',', ' ')]);
        $csv->insertOne(['Współczynnik ukończenia zamówień (%)', 
            number_format($stats['total_orders'] > 0 ? ($stats['completed_orders'] / $stats['total_orders']) * 100 : 0, 1, ',', ' ')
        ]);
        $csv->insertOne([]);
    }

    private function addOrdersOverviewSection(Writer $csv, $orders): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        $csv->insertOne(['OSTATNIE ZAMÓWIENIA (20 najnowszych)']);
        $csv->insertOne(['ID', 'Data', 'Status', 'Liczba dywanów', 'Kwota (zł)']);
        
        foreach ($orders as $order) {
            $csv->insertOne([
                '#' . $order['id'],
                $order['date'],
                ucfirst($order['status']),
                $order['carpets_count'],
                number_format($order['total_amount'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }

    private function addMonthlyPerformanceSection(Writer $csv, $monthlyData): void
    {
        if ($monthlyData->isEmpty()) {
            return;
        }

        $csv->insertOne(['WYDAJNOŚĆ MIESIĘCZNA (ostatnie 12 miesięcy)']);
        $csv->insertOne(['Miesiąc', 'Zamówienia', 'Suma (zł)', 'Średnia (zł)']);
        
        foreach ($monthlyData as $month) {
            $csv->insertOne([
                $month['month'],
                $month['orders_count'],
                number_format($month['total_amount'], 2, ',', ' '),
                number_format($month['avg_amount'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }

    private function addCarpetsSummarySection(Writer $csv, $carpets): void
    {
        if ($carpets->isEmpty()) {
            return;
        }

        $csv->insertOne(['OSTATNIE DYWANY (25 najnowszych)']);
        $csv->insertOne([
            'Kod referencyjny',
            'ID Zamówienia',
            'Szerokość (m)',
            'Wysokość (m)',
            'Obszar (m²)',
            'Status',
            'Cena (zł)',
            'Data'
        ]);
        
        foreach ($carpets as $carpet) {
            $csv->insertOne([
                $carpet['reference_code'],
                '#' . $carpet['order_id'],
                number_format($carpet['width'], 2, ',', ' '),
                number_format($carpet['height'], 2, ',', ' '),
                number_format($carpet['area'], 2, ',', ' '),
                ucfirst($carpet['status']),
                number_format($carpet['total_price'], 2, ',', ' '),
                $carpet['created_date']
            ]);
        }
        $csv->insertOne([]);
    }

    private function addRevenueAnalysisSection(Writer $csv, array $revenueAnalysis): void
    {
        $csv->insertOne(['ANALIZA PRZYCHODÓW WEDŁUG STATUSU']);
        $csv->insertOne(['Status', 'Kwota (zł)', 'Procent (%)']);
        
        $total = array_sum($revenueAnalysis);
        
        $statusLabels = [
            'completed' => 'Ukończone',
            'in_progress' => 'W trakcie',
            'pending' => 'Oczekujące',
        ];

        foreach ($revenueAnalysis as $status => $amount) {
            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
            $csv->insertOne([
                $statusLabels[$status] ?? ucfirst($status),
                number_format($amount, 2, ',', ' '),
                number_format($percentage, 1, ',', ' ')
            ]);
        }

        // Total row
        $csv->insertOne(['RAZEM', number_format($total, 2, ',', ' '), '100.0']);
        $csv->insertOne([]);
    }
}