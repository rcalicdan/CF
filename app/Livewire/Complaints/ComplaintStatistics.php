<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintStatus;
use App\Enums\OrderCarpetStatus;
use App\Models\Complaint;
use App\Models\OrderCarpet;
use Livewire\Component;
use Carbon\Carbon;

class ComplaintStatistics extends Component
{
    public $selectedPeriod = '7';
    public $complaintStats = [];
    public $weeklyTrend = [];
    public $categoryStats = [];
    public $recentComplaints = [];
    
    protected $listeners = ['refreshStats' => 'loadStats'];

    public function mount()
    {
        $this->loadStats();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->complaintStats = $this->getComplaintStats();
        $this->weeklyTrend = $this->getWeeklyTrend();
        $this->categoryStats = $this->getCategoryStats();
        $this->recentComplaints = $this->getRecentComplaints();
    }

    private function getComplaintStats()
    {
        $totalComplaints = Complaint::count();
        $openComplaints = Complaint::where('status', ComplaintStatus::OPEN->value)->count();
        $inProgressComplaints = Complaint::where('status', ComplaintStatus::IN_PROGRESS->value)->count();
        $resolvedComplaints = Complaint::where('status', ComplaintStatus::RESOLVED->value)->count();
        
        $activeComplaints = $openComplaints + $inProgressComplaints;
        $resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 0;

        // Get previous week stats for comparison
        $previousWeekStart = Carbon::now()->subWeeks(2)->startOfWeek();
        $previousWeekEnd = Carbon::now()->subWeeks(1)->endOfWeek();
        $previousWeekTotal = Complaint::whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])->count();
        
        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentWeekTotal = Complaint::where('created_at', '>=', $currentWeekStart)->count();
        
        $weeklyChange = $previousWeekTotal > 0 ? $currentWeekTotal - $previousWeekTotal : 0;

        return [
            'total' => $totalComplaints,
            'active' => $activeComplaints,
            'resolved' => $resolvedComplaints,
            'resolution_rate' => $resolutionRate,
            'weekly_change' => $weeklyChange,
            'open' => $openComplaints,
            'in_progress' => $inProgressComplaints,
        ];
    }

    private function getWeeklyTrend()
    {
        $days = [];
        $newComplaints = [];
        $resolvedComplaints = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D');
            
            $newCount = Complaint::whereDate('created_at', $date)->count();
            $resolvedCount = Complaint::where('status', ComplaintStatus::RESOLVED->value)
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

    private function getCategoryStats()
    {
        $carpetsWithComplaints = OrderCarpet::where('status', OrderCarpetStatus::COMPLAINT->value)
            ->with(['complaint', 'order.client'])
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
                $details = strtolower($carpet->complaint->complaint_details);
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

    private function getRecentComplaints()
    {
        return Complaint::with(['orderCarpet.order.client'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'details' => $complaint->complaint_details,
                    'status' => $complaint->status,
                    'created_at' => $complaint->created_at,
                    'client_name' => $complaint->orderCarpet?->order?->client?->full_name ?? 'N/A',
                    'order_id' => $complaint->orderCarpet?->order?->id ?? 'N/A',
                    'carpet_qr' => $complaint->orderCarpet?->reference_code ?? 'N/A',
                    'priority' => $this->determinePriority($complaint),
                ];
            });
    }

    private function determinePriority($complaint)
    {
        $details = strtolower($complaint->complaint_details);
        
        if (str_contains($details, 'uszkodz') || str_contains($details, 'zniszcz')) {
            return ['level' => 'high', 'label' => 'Wysoki'];
        } elseif (str_contains($details, 'opóźnien') || str_contains($details, 'komunikacja')) {
            return ['level' => 'medium', 'label' => 'Średni'];
        } else {
            return ['level' => 'low', 'label' => 'Niski'];
        }
    }

    public function getStatusColor($status)
    {
        return match($status) {
            ComplaintStatus::OPEN->value => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Nowa'],
            ComplaintStatus::IN_PROGRESS->value => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'W trakcie'],
            ComplaintStatus::RESOLVED->value => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Rozwiązana'],
            ComplaintStatus::REJECTED->value => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Odrzucona'],
            ComplaintStatus::CLOSED->value => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Zamknięta'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Nieznany'],
        };
    }

    public function render()
    {
        return view('livewire.complaints.complaint-statistics');
    }
}