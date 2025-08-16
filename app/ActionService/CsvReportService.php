<?php

namespace App\ActionService;

use Carbon\Carbon;
use League\Csv\Writer;
use League\Csv\EscapeFormula;
use Illuminate\Support\Facades\Storage;

class CsvReportService
{
    public function generateProcessingCostsReport(array $data): string
    {
        $filename = 'raport_kosztow_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        $csv = Writer::createFromString('');

        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->addFormatter(new EscapeFormula());

        $csv->insertOne(['Raport kosztów przetwarzania - Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i')]);
        $csv->insertOne([]);

        $csv->insertOne(['PODSUMOWANIE']);
        $csv->insertOne(['Okres', 'Kwota (zł)', 'Transakcje']);
        $csv->insertOne([
            'Bieżący miesiąc',
            number_format($data['monthlyComparison']['current_month'], 2, ',', ''),
            $data['monthlyComparison']['current_count']
        ]);
        $csv->insertOne([
            'Poprzedni miesiąc',
            number_format($data['monthlyComparison']['previous_month'], 2, ',', ''),
            $data['monthlyComparison']['previous_count']
        ]);
        $csv->insertOne([
            'Zmiana',
            ($data['monthlyComparison']['percentage_change'] >= 0 ? '+' : '') .
                number_format($data['monthlyComparison']['percentage_change'], 1, ',', '') . '%',
            ''
        ]);
        $csv->insertOne([]);

        $csv->insertOne(['KOSZTY TYGODNIOWE']);
        $csv->insertOne(['Dzień', 'Kwota (zł)', 'Transakcje', 'Średnia (zł)', 'Liczba Dywanów', 'Średni Koszt/Dywan (zł)']);
        foreach ($data['weeklyData'] as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 2, ',', ''),
                $item['count'],
                number_format($item['average'], 2, ',', ''),
                $item['processed_count'],
                number_format($item['avg_cost_per_carpet'], 2, ',', '')
            ]);
        }
        $csv->insertOne([]);

        $csv->insertOne(['KOSZTY MIESIĘCZNE']);
        $csv->insertOne(['Miesiąc', 'Kwota (zł)', 'Transakcje', 'Średnia (zł)', 'Min (zł)', 'Max (zł)', 'Liczba Dywanów', 'Średni Koszt/Dywan (zł)']);
        foreach ($data['monthlyData'] as $item) {
            $csv->insertOne([
                $item['full_name'],
                number_format($item['value'], 2, ',', ''),
                $item['count'],
                number_format($item['average'], 2, ',', ''),
                number_format($item['min'], 2, ',', ''),
                number_format($item['max'], 2, ',', ''),
                $item['processed_count'],
                number_format($item['avg_cost_per_carpet'], 2, ',', '')
            ]);
        }
        $csv->insertOne([]);

        $csv->insertOne(['KOSZTY ROCZNE']);
        $csv->insertOne(['Rok', 'Kwota (zł)', 'Transakcje', 'Średnia (zł)', 'Q1 (zł)', 'Q2 (zł)', 'Q3 (zł)', 'Q4 (zł)', 'Liczba Dywanów', 'Średni Koszt/Dywan (zł)']);
        foreach ($data['yearlyData'] as $item) {
            $csv->insertOne([
                $item['label'],
                number_format($item['value'], 2, ',', ''),
                $item['count'],
                number_format($item['average'], 2, ',', ''),
                number_format($item['quarters']['q1'], 2, ',', ''),
                number_format($item['quarters']['q2'], 2, ',', ''),
                number_format($item['quarters']['q3'], 2, ',', ''),
                number_format($item['quarters']['q4'], 2, ',', ''),
                $item['processed_count'],
                number_format($item['avg_cost_per_carpet'], 2, ',', '')
            ]);
        }
        $csv->insertOne([]);

        $csv->insertOne(['KOSZTY WG TYPÓW (bieżący miesiąc)']);
        $csv->insertOne(['Typ', 'Kwota (zł)', 'Transakcje', 'Procent (%)']);
        foreach ($data['costTypeData'] as $item) {
            $csv->insertOne([
                $item['label'],
                number_format($item['value'], 2, ',', ''),
                $item['count'],
                number_format($item['percentage'], 1, ',', '')
            ]);
        }

        $csvContent = $csv->toString();
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }
}
