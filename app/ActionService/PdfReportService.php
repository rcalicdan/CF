<?php

namespace App\ActionService;

use TCPDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PdfReportService
{
    public function generateProcessingCostsReport(array $data): string
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Analiza kosztów przetwarzania');
        $pdf->SetSubject('Raport kosztów');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10); 
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Cell(0, 10, 'Analiza kosztów przetwarzania', 0, 1, 'C');
        $pdf->Ln(3);
        
        // Generation Date
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 10, 'Wygenerowano: ' . Carbon::now()->format('d.m.Y H:i'), 0, 1, 'R');
        $pdf->Ln(3);

        // --- Summary Section ---
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Podsumowanie', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 10);

        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.1);

        // Calculate dynamic column widths for summary
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $col1_width = $pageWidth * 0.55; // Period
        $col2_width = $pageWidth * 0.25; // Amount
        $col3_width = $pageWidth * 0.20; // Transactions

        $pdf->Cell($col1_width, 8, 'Okres', 1, 0, 'C', true);
        $pdf->Cell($col2_width, 8, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($col3_width, 8, 'Transakcje', 1, 1, 'C', true);

        $pdf->Cell($col1_width, 8, 'Bieżący miesiąc', 1, 0, 'L');
        $pdf->Cell($col2_width, 8, number_format($data['monthlyComparison']['current_month'], 2, ',', ' ') . ' zł', 1, 0, 'R');
        $pdf->Cell($col3_width, 8, $data['monthlyComparison']['current_count'], 1, 1, 'R');

        $pdf->Cell($col1_width, 8, 'Poprzedni miesiąc', 1, 0, 'L');
        $pdf->Cell($col2_width, 8, number_format($data['monthlyComparison']['previous_month'], 2, ',', ' ') . ' zł', 1, 0, 'R');
        $pdf->Cell($col3_width, 8, $data['monthlyComparison']['previous_count'], 1, 1, 'R');

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell($col1_width, 8, 'Zmiana', 1, 0, 'L');
        $pdf->Cell($col2_width, 8, ($data['monthlyComparison']['percentage_change'] >= 0 ? '+' : '') .
                   number_format($data['monthlyComparison']['percentage_change'], 1, ',', ' ') . '%', 1, 0, 'R');
        $pdf->Cell($col3_width, 8, '', 1, 1, 'R');
        $pdf->Ln(3); 


        // --- Detailed Data Section ---
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, 'Szczegółowe dane', 0, 1, 'L');
        $pdf->Ln(1);

        // --- Weekly Data Section ---
        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->Cell(0, 10, 'Koszty tygodniowe', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 9);
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $num_weekly_cols = 6; 
        $col_width_weekly = $pageWidth / $num_weekly_cols;
        $day_col_width = $col_width_weekly * 1.2; 
        $amount_col_width = $col_width_weekly * 1.0;
        $trans_col_width = $col_width_weekly * 0.8; 
        $avg_col_width = $col_width_weekly * 1.0;
        $carpets_col_width = $col_width_weekly * 0.8; 
        $avg_cost_col_width = $col_width_weekly * 1.2;
        $total_weekly_width = $day_col_width + $amount_col_width + $trans_col_width + $avg_col_width + $carpets_col_width + $avg_cost_col_width;
        if ($total_weekly_width > $pageWidth) {
             $scale_factor = $pageWidth / $total_weekly_width;
             $day_col_width *= $scale_factor;
             $amount_col_width *= $scale_factor;
             $trans_col_width *= $scale_factor;
             $avg_col_width *= $scale_factor;
             $carpets_col_width *= $scale_factor;
             $avg_cost_col_width *= $scale_factor;
        }


        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($day_col_width, 7, 'Dzień', 1, 0, 'C', true);
        $pdf->Cell($amount_col_width, 7, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($trans_col_width, 7, 'Trans.', 1, 0, 'C', true);
        $pdf->Cell($avg_col_width, 7, 'Średnia', 1, 0, 'C', true);
        $pdf->Cell($carpets_col_width, 7, 'Dywanów', 1, 0, 'C', true);
        $pdf->Cell($avg_cost_col_width, 7, 'Śr. koszt/dyw.', 1, 1, 'C', true);

        foreach ($data['weeklyData'] as $item) {
            $pdf->SetFont('dejavusans', '', 8); 
            
            $pdf->Cell($day_col_width, 6, $item['full_name'], 1, 0, 'L');
            $pdf->Cell($amount_col_width, 6, number_format($item['value'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($trans_col_width, 6, $item['count'], 1, 0, 'R');
            $pdf->Cell($avg_col_width, 6, number_format($item['average'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($carpets_col_width, 6, $item['processed_count'], 1, 0, 'R');
            $pdf->Cell($avg_cost_col_width, 6, number_format($item['avg_cost_per_carpet'], 2, ',', ' ') . ' zł', 1, 1, 'R'); 
        }
        $pdf->Ln(2); 

        // --- Monthly Data Section ---
        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->Cell(0, 10, 'Koszty miesięczne', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 9);

         // Calculate dynamic column widths for monthly data
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $num_monthly_cols = 8; // Month, Amount, Transactions, Avg, Min, Max, Carpets, Avg Cost/Carpet
        $month_col_width = ($pageWidth * 0.15);
        $amount_col_width = ($pageWidth * 0.12);
        $trans_col_width = ($pageWidth * 0.10);
        $avg_col_width = ($pageWidth * 0.12);
        $min_col_width = ($pageWidth * 0.12);
        $max_col_width = ($pageWidth * 0.12);
        $carpets_col_width = ($pageWidth * 0.10);
        $avg_cost_col_width = ($pageWidth * 0.17); 
        $total_monthly_width = $month_col_width + $amount_col_width + $trans_col_width + $avg_col_width + $min_col_width + $max_col_width + $carpets_col_width + $avg_cost_col_width;
        if ($total_monthly_width > $pageWidth) {
             $scale_factor = $pageWidth / $total_monthly_width;
             $month_col_width *= $scale_factor;
             $amount_col_width *= $scale_factor;
             $trans_col_width *= $scale_factor;
             $avg_col_width *= $scale_factor;
             $min_col_width *= $scale_factor;
             $max_col_width *= $scale_factor;
             $carpets_col_width *= $scale_factor;
             $avg_cost_col_width *= $scale_factor;
        }

        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($month_col_width, 7, 'Miesiąc', 1, 0, 'C', true);
        $pdf->Cell($amount_col_width, 7, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($trans_col_width, 7, 'Trans.', 1, 0, 'C', true);
        $pdf->Cell($avg_col_width, 7, 'Średnia', 1, 0, 'C', true);
        $pdf->Cell($min_col_width, 7, 'Min', 1, 0, 'C', true);
        $pdf->Cell($max_col_width, 7, 'Max', 1, 0, 'C', true);
        $pdf->Cell($carpets_col_width, 7, 'Dywanów', 1, 0, 'C', true);
        $pdf->Cell($avg_cost_col_width, 7, 'Śr. koszt/dyw.', 1, 1, 'C', true);

        foreach ($data['monthlyData'] as $item) {
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->Cell($month_col_width, 6, $item['full_name'], 1, 0, 'L');
            $pdf->Cell($amount_col_width, 6, number_format($item['value'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($trans_col_width, 6, $item['count'], 1, 0, 'R');
            $pdf->Cell($avg_col_width, 6, number_format($item['average'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($min_col_width, 6, number_format($item['min'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($max_col_width, 6, number_format($item['max'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($carpets_col_width, 6, $item['processed_count'], 1, 0, 'R');
            $pdf->Cell($avg_cost_col_width, 6, number_format($item['avg_cost_per_carpet'], 2, ',', ' ') . ' zł', 1, 1, 'R');
        }
        $pdf->Ln(2);

        // --- Yearly Data Section ---
        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->Cell(0, 10, 'Koszty roczne', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 9);

        // Calculate dynamic column widths for yearly data
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $num_yearly_cols = 8; // Year, Amount, Transactions, Avg, Q1, Q2, Carpets, Avg Cost/Carpet
        $year_col_width = ($pageWidth * 0.10);
        $amount_col_width = ($pageWidth * 0.12);
        $trans_col_width = ($pageWidth * 0.10);
        $avg_col_width = ($pageWidth * 0.12);
        $q1_col_width = ($pageWidth * 0.12);
        $q2_col_width = ($pageWidth * 0.12);
        $carpets_col_width = ($pageWidth * 0.10);
        $avg_cost_col_width = ($pageWidth * 0.22); 
        $total_yearly_width = $year_col_width + $amount_col_width + $trans_col_width + $avg_col_width + $q1_col_width + $q2_col_width + $carpets_col_width + $avg_cost_col_width;
        if ($total_yearly_width > $pageWidth) {
             $scale_factor = $pageWidth / $total_yearly_width;
             $year_col_width *= $scale_factor;
             $amount_col_width *= $scale_factor;
             $trans_col_width *= $scale_factor;
             $avg_col_width *= $scale_factor;
             $q1_col_width *= $scale_factor;
             $q2_col_width *= $scale_factor;
             $carpets_col_width *= $scale_factor;
             $avg_cost_col_width *= $scale_factor;
        }


        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($year_col_width, 7, 'Rok', 1, 0, 'C', true);
        $pdf->Cell($amount_col_width, 7, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($trans_col_width, 7, 'Trans.', 1, 0, 'C', true);
        $pdf->Cell($avg_col_width, 7, 'Średnia', 1, 0, 'C', true);
        $pdf->Cell($q1_col_width, 7, 'Q1', 1, 0, 'C', true);
        $pdf->Cell($q2_col_width, 7, 'Q2', 1, 0, 'C', true);
        $pdf->Cell($carpets_col_width, 7, 'Dywanów', 1, 0, 'C', true);
        $pdf->Cell($avg_cost_col_width, 7, 'Śr. koszt/dyw.', 1, 1, 'C', true);

        foreach ($data['yearlyData'] as $item) {
             $pdf->SetFont('dejavusans', '', 8);
            $pdf->Cell($year_col_width, 6, $item['label'], 1, 0, 'L');
            $pdf->Cell($amount_col_width, 6, number_format($item['value'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($trans_col_width, 6, $item['count'], 1, 0, 'R');
            $pdf->Cell($avg_col_width, 6, number_format($item['average'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($q1_col_width, 6, number_format($item['quarters']['q1'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($q2_col_width, 6, number_format($item['quarters']['q2'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($carpets_col_width, 6, $item['processed_count'], 1, 0, 'R');
            $pdf->Cell($avg_cost_col_width, 6, number_format($item['avg_cost_per_carpet'], 2, ',', ' ') . ' zł', 1, 1, 'R');
        }
        $pdf->Ln(2);

        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->Cell(0, 10, 'Koszty według typu (bieżący miesiąc)', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 10);

        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $num_costtype_cols = 4; 
        $col_width_costtype = $pageWidth / $num_costtype_cols;
        $type_col_width = $col_width_costtype * 1.5; 
        $amount_col_width = $col_width_costtype * 1.0;
        $trans_col_width = $col_width_costtype * 1.0;
        $percent_col_width = $col_width_costtype * 0.5; 
        $total_costtype_width = $type_col_width + $amount_col_width + $trans_col_width + $percent_col_width;
        if ($total_costtype_width > $pageWidth) {
             $scale_factor = $pageWidth / $total_costtype_width;
             $type_col_width *= $scale_factor;
             $amount_col_width *= $scale_factor;
             $trans_col_width *= $scale_factor;
             $percent_col_width *= $scale_factor;
        }

        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($type_col_width, 8, 'Typ', 1, 0, 'C', true);
        $pdf->Cell($amount_col_width, 8, 'Kwota', 1, 0, 'C', true);
        $pdf->Cell($trans_col_width, 8, 'Transakcje', 1, 0, 'C', true);
        $pdf->Cell($percent_col_width, 8, 'Procent', 1, 1, 'C', true);

        foreach ($data['costTypeData'] as $item) {
            $pdf->Cell($type_col_width, 7, $item['label'], 1, 0, 'L'); // Slightly smaller row height
            $pdf->Cell($amount_col_width, 7, number_format($item['value'], 2, ',', ' ') . ' zł', 1, 0, 'R');
            $pdf->Cell($trans_col_width, 7, $item['count'], 1, 0, 'R');
            $pdf->Cell($percent_col_width, 7, number_format($item['percentage'], 1, ',', ' ') . '%', 1, 1, 'R');
        }

        $filename = 'raport_kosztow_' . Carbon::now()->format('Y-m-d_H-i') . '.pdf';

        $pdfContent = $pdf->Output('', 'S');

        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }
}