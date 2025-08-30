<?php

namespace App\ActionService;

use Carbon\Carbon;
use App\Models\Complaint;
use App\Models\OrderCarpet;
use App\Enums\ComplaintStatus;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use League\Csv\Reader;

class ComplaintCsvReportService
{
    public function generateComplaintCsvReport(int $days = 30): string
    {
        $startDate = Carbon::now()->subDays($days);

        $complaints = Complaint::with(['orderCarpet.order.client', 'orderCarpet.order.driver.user'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = Writer::createFromString();

        $csv->setOutputBOM(Writer::BOM_UTF8);

        $csv->setDelimiter(';');

        $headers = [
            'ID Skargi',
            'Data Utworzenia',
            'Status',
            'Priorytet',
            'Kategoria',
            'Opis Skargi',
            'ID Zamówienia',
            'Kod Dywanu',
            'Klient - Imię',
            'Klient - Nazwisko',
            'Klient - Telefon',
            'Klient - Email',
            'Klient - Adres',
            'Kierowca',
            'Wymiary Dywanu',
            'Powierzchnia (m²)',
            'Data Zmierzenia',
            'Status Dywanu',
            'Uwagi do Dywanu',
            'Data Aktualizacji Skargi',
            'Czas Rozwiązania (dni)',
            'Miesiąc',
            'Kwartał',
            'Rok'
        ];

        $csv->insertOne($headers);

        $records = [];
        foreach ($complaints as $complaint) {
            $orderCarpet = $complaint->orderCarpet;
            $order = $orderCarpet?->order;
            $client = $order?->client;
            $driver = $order?->driver?->user;

            $resolutionTime = '';
            if (in_array($complaint->status, [ComplaintStatus::RESOLVED->value, ComplaintStatus::CLOSED->value])) {
                $resolutionTime = $complaint->created_at->diffInDays($complaint->updated_at);
            }

            $priority = $this->determinePriority($complaint);
            $category = $this->determineCategory($complaint);

            $records[] = [
                $complaint->id,
                $complaint->created_at->format('d.m.Y H:i'),
                $this->getStatusLabel($complaint->status),
                $priority['label'],
                $category,
                $this->cleanText($complaint->complaint_details),
                $order?->id ?? '',
                $orderCarpet?->reference_code ?? '',
                $client?->first_name ?? '',
                $client?->last_name ?? '',
                $client?->phone_number ?? '',
                $client?->email ?? '',
                $client?->full_address ?? '',
                $driver?->full_name ?? '',
                $orderCarpet ? ($orderCarpet->width . 'x' . $orderCarpet->height) : '',
                $orderCarpet?->total_area ? $this->formatNumber($orderCarpet->total_area) : '',
                $orderCarpet?->measured_at?->format('d.m.Y H:i') ?? '',
                $orderCarpet?->status ? $this->getCarpetStatusLabel($orderCarpet->status) : '',
                $this->cleanText($orderCarpet?->remarks ?? ''),
                $complaint->updated_at->format('d.m.Y H:i'),
                $resolutionTime,
                $this->getPolishMonth($complaint->created_at) . ' ' . $complaint->created_at->year,
                'Q' . $complaint->created_at->quarter . ' ' . $complaint->created_at->year,
                $complaint->created_at->year
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
            ['Nowe skargi', $stats['open']],
            ['Skargi w trakcie', $stats['in_progress']],
            ['Rozwiązane skargi', $stats['resolved']],
            ['Odrzucone skargi', $stats['rejected']],
            ['Zamknięte skargi', $stats['closed']],
            ['Współczynnik rozwiązań (%)', $this->formatNumber($stats['resolution_rate'])],
            ['Zmiana względem poprzedniego okresu', $stats['period_change']],
            ['Średni czas rozwiązania (dni)', $this->formatNumber($stats['avg_resolution_time'])],
        ]);

        $csv->insertOne(['']); 

        $csv->insertOne(['ROZKŁAD WEDŁUG STATUSU']);
        $csv->insertOne(['Status', 'Liczba', 'Procent (%)']);
        $total = array_sum($statusStats);
        foreach ($statusStats as $status => $count) {
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            $csv->insertOne([
                $this->getStatusLabel($status),
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
                $month['resolved_count'],
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
            $resolvedCount = $weeklyTrend['resolved_complaints'][$index] ?? 0;
            $netChange = $newCount - $resolvedCount;

            $csv->insertOne([
                $day,
                $newCount,
                $resolvedCount,
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
        $periodChange = $totalComplaints - $previousPeriodTotal;

        return [
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
    }

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

    private function getStatusStats(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        return [
            ComplaintStatus::OPEN->value => Complaint::where('created_at', '>=', $startDate)->where('status', ComplaintStatus::OPEN->value)->count(),
            ComplaintStatus::IN_PROGRESS->value => Complaint::where('created_at', '>=', $startDate)->where('status', ComplaintStatus::IN_PROGRESS->value)->count(),
            ComplaintStatus::RESOLVED->value => Complaint::where('created_at', '>=', $startDate)->where('status', ComplaintStatus::RESOLVED->value)->count(),
            ComplaintStatus::REJECTED->value => Complaint::where('created_at', '>=', $startDate)->where('status', ComplaintStatus::REJECTED->value)->count(),
            ComplaintStatus::CLOSED->value => Complaint::where('created_at', '>=', $startDate)->where('status', ComplaintStatus::CLOSED->value)->count(),
        ];
    }

    private function getMonthlyStats(): array
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
                'month' => $this->getPolishMonth($date) . ' ' . $date->year,
                'new_count' => $newCount,
                'resolved_count' => $resolvedCount,
                'resolution_rate' => $resolutionRate,
            ];
        }

        return $performance;
    }

    private function getWeeklyTrend(): array
    {
        $days = [];
        $newComplaints = [];
        $resolvedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $this->getPolishDayName($date) . ' ' . $date->format('d.m');

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

    private function determineCategory($complaint): string
    {
        $details = mb_strtolower($complaint->complaint_details);

        if (str_contains($details, 'uszkodz') || str_contains($details, 'zniszcz') || str_contains($details, 'rozdarcie')) {
            return 'Uszkodzenia';
        } elseif (str_contains($details, 'opóźnien') || str_contains($details, 'późno') || str_contains($details, 'czas')) {
            return 'Opóźnienia';
        } elseif (str_contains($details, 'jakość') || str_contains($details, 'pranie') || str_contains($details, 'czyszczenie')) {
            return 'Jakość';
        } elseif (str_contains($details, 'komunikacja') || str_contains($details, 'kontakt') || str_contains($details, 'informacj')) {
            return 'Komunikacja';
        } else {
            return 'Inne';
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

    private function getCarpetStatusLabel(string $status): string
    {
        return match ($status) {
            OrderCarpetStatus::COMPLAINT->value => 'Skarga',
            'pending' => 'Oczekujący',
            'processing' => 'W trakcie',
            'completed' => 'Ukończony',
            default => ucfirst($status),
        };
    }

    private function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'damage' => 'Uszkodzenia',
            'delay' => 'Opóźnienia',
            'quality' => 'Jakość',
            'communication' => 'Komunikacja',
            'other' => 'Inne',
            default => ucfirst($category),
        };
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

    private function getPolishDayName(Carbon $date): string
    {
        $days = [
            0 => 'Niedziela',
            1 => 'Poniedziałek',
            2 => 'Wtorek',
            3 => 'Środa',
            4 => 'Czwartek',
            5 => 'Piątek',
            6 => 'Sobota'
        ];

        return $days[$date->dayOfWeek];
    }

    private function formatNumber(float $number): string
    {
        return number_format($number, 2, ',', ' ');
    }

    private function cleanText(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $text)));
    }
}
