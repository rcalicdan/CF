<?php

namespace App\ActionService;

use Carbon\Carbon;
use League\Csv\Writer;
use League\Csv\EscapeFormula;
use Illuminate\Support\Facades\Storage;

class LaundryThroughputCsvReportService
{
    public function generateLaundryThroughputReport(array $data): string
    {
        $filename = 'raport_przepustowosci_pralni_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        $csv = Writer::createFromString('');

        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->addFormatter(new EscapeFormula());

        // Header
        $csv->insertOne(['Raport wydajności pralni - Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i')]);
        $csv->insertOne([]);

        // Summary section
        $this->addSummarySection($csv, $data['summary']);

        // Daily data section
        $this->addDailyDataSection($csv, $data['dailyData']);

        // Weekly data section  
        $this->addWeeklyDataSection($csv, $data['weeklyData']);

        // Monthly data section
        $this->addMonthlyDataSection($csv, $data['monthlyData']);

        // Revenue sections
        $this->addRevenueSection($csv, $data);

        // Client performance section
        $this->addClientPerformanceSection($csv, $data['clientPerformance']);

        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    private function addSummarySection(Writer $csv, array $summary): void
    {
        $csv->insertOne(['PODSUMOWANIE']);
        $csv->insertOne(['Metryka', 'Bieżący miesiąc', 'Poprzedni miesiąc', 'Zmiana (%)']);
        
        $csv->insertOne([
            'Liczba dywanów',
            number_format($summary['current_month_carpets'], 0, ',', ' '),
            number_format($summary['previous_month_carpets'], 0, ',', ' '),
            ($summary['carpets_change_percentage'] >= 0 ? '+' : '') . 
                number_format($summary['carpets_change_percentage'], 1, ',', ' ') . '%'
        ]);
        
        $csv->insertOne([
            'Przychód (zł)',
            number_format($summary['current_month_revenue'], 2, ',', ' '),
            number_format($summary['previous_month_revenue'], 2, ',', ' '),
            ($summary['revenue_change_percentage'] >= 0 ? '+' : '') . 
                number_format($summary['revenue_change_percentage'], 1, ',', ' ') . '%'
        ]);
        
        $csv->insertOne([
            'Średnia wartość zamówienia (zł)',
            number_format($summary['avg_order_value'], 2, ',', ' '),
            '',
            ''
        ]);
        
        $csv->insertOne([
            'Trend tygodniowy',
            $summary['weekly_trend'],
            '',
            ''
        ]);
        
        $csv->insertOne([
            'Trend miesięczny',
            $summary['monthly_trend'],
            '',
            ''
        ]);
        
        $csv->insertOne([
            'Zmiana roczna',
            $summary['yearly_change'],
            '',
            ''
        ]);
        
        $csv->insertOne([]);
    }

    private function addDailyDataSection(Writer $csv, array $dailyData): void
    {
        $csv->insertOne(['ANALIZA DZIENNA (ostatnie 30 dni)']);
        $csv->insertOne([
            'Data',
            'Przetworzone dywany',
            'Ukończone dywany',
            'Wskaźnik ukończenia (%)',
            'Średnia powierzchnia (m²)',
            'Całkowita powierzchnia (m²)',
            'Średni rozmiar dywanu (m²)',
            'Całkowita powierzchnia dywanów (m²)',
            'Szacowana waga (kg)'
        ]);
        
        foreach ($dailyData as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 0, ',', ' '),
                number_format($item['completed_count'], 0, ',', ' '),
                number_format($item['completion_rate'], 1, ',', ' '),
                number_format($item['avg_area'], 2, ',', ' '),
                number_format($item['total_area'], 2, ',', ' '),
                number_format($item['avg_carpet_size'], 2, ',', ' '),
                number_format($item['total_carpet_area'], 2, ',', ' '),
                number_format($item['weight_estimate'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }

    private function addWeeklyDataSection(Writer $csv, array $weeklyData): void
    {
        $csv->insertOne(['ANALIZA TYGODNIOWA (ostatnie 12 tygodni)']);
        $csv->insertOne([
            'Okres',
            'Przetworzone dywany',
            'Ukończone dywany',
            'Wskaźnik ukończenia (%)',
            'Średnia powierzchnia (m²)',
            'Całkowita powierzchnia (m²)',
            'Średni rozmiar dywanu (m²)',
            'Całkowita powierzchnia dywanów (m²)',
            'Szacowana waga (kg)'
        ]);
        
        foreach ($weeklyData as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 0, ',', ' '),
                number_format($item['completed_count'], 0, ',', ' '),
                number_format($item['completion_rate'], 1, ',', ' '),
                number_format($item['avg_area'], 2, ',', ' '),
                number_format($item['total_area'], 2, ',', ' '),
                number_format($item['avg_carpet_size'], 2, ',', ' '),
                number_format($item['total_carpet_area'], 2, ',', ' '),
                number_format($item['weight_estimate'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }

    private function addMonthlyDataSection(Writer $csv, array $monthlyData): void
    {
        $csv->insertOne(['ANALIZA MIESIĘCZNA (ostatnie 12 miesięcy)']);
        $csv->insertOne([
            'Miesiąc',
            'Przetworzone dywany',
            'Ukończone dywany',
            'Wskaźnik ukończenia (%)',
            'Średnia powierzchnia (m²)',
            'Całkowita powierzchnia (m²)',
            'Średni rozmiar dywanu (m²)',
            'Całkowita powierzchnia dywanów (m²)',
            'Szacowana waga (kg)'
        ]);
        
        foreach ($monthlyData as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 0, ',', ' '),
                number_format($item['completed_count'], 0, ',', ' '),
                number_format($item['completion_rate'], 1, ',', ' '),
                number_format($item['avg_area'], 2, ',', ' '),
                number_format($item['total_area'], 2, ',', ' '),
                number_format($item['avg_carpet_size'], 2, ',', ' '),
                number_format($item['total_carpet_area'], 2, ',', ' '),
                number_format($item['weight_estimate'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }

    private function addRevenueSection(Writer $csv, array $data): void
    {
        // Daily revenue
        $csv->insertOne(['PRZYCHODY DZIENNE (ostatnie 30 dni)']);
        $csv->insertOne([
            'Data',
            'Przychód (zł)',
            'Liczba zamówień',
            'Średnia wartość zamówienia (zł)'
        ]);
        
        foreach ($data['revenueDaily'] as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 2, ',', ' '),
                number_format($item['order_count'], 0, ',', ' '),
                number_format($item['avg_order_value'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);

        // Weekly revenue
        $csv->insertOne(['PRZYCHODY TYGODNIOWE (ostatnie 12 tygodni)']);
        $csv->insertOne([
            'Okres',
            'Przychód (zł)',
            'Liczba zamówień',
            'Średnia wartość zamówienia (zł)'
        ]);
        
        foreach ($data['revenueWeekly'] as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 2, ',', ' '),
                number_format($item['order_count'], 0, ',', ' '),
                number_format($item['avg_order_value'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);

        // Monthly revenue
        $csv->insertOne(['PRZYCHODY MIESIĘCZNE (ostatnie 12 miesięcy)']);
        $csv->insertOne([
            'Miesiąc',
            'Przychód (zł)',
            'Liczba zamówień',
            'Średnia wartość zamówienia (zł)'
        ]);
        
        foreach ($data['revenueMonthly'] as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 2, ',', ' '),
                number_format($item['order_count'], 0, ',', ' '),
                number_format($item['avg_order_value'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }

    private function addClientPerformanceSection(Writer $csv, array $clientData): void
    {
        $csv->insertOne(['NAJLEPSI KLIENCI (ostatnie 12 miesięcy - TOP 20)']);
        $csv->insertOne([
            'Nazwa klienta',
            'Miasto',
            'Liczba zamówień',
            'Liczba dywanów',
            'Całkowita powierzchnia (m²)',
            'Całkowity przychód (zł)',
            'Średnia wartość zamówienia (zł)',
            'Średnia powierzchnia dywanu (m²)',
            'Szacowana waga (kg)'
        ]);
        
        foreach ($clientData as $client) {
            $csv->insertOne([
                $client['client_name'],
                $client['city'],
                number_format($client['order_count'], 0, ',', ' '),
                number_format($client['carpet_count'], 0, ',', ' '),
                number_format($client['total_area'], 2, ',', ' '),
                number_format($client['total_revenue'], 2, ',', ' '),
                number_format($client['avg_order_value'], 2, ',', ' '),
                number_format($client['avg_carpet_area'], 2, ',', ' '),
                number_format($client['weight_estimate'], 2, ',', ' ')
            ]);
        }
        $csv->insertOne([]);
    }
}