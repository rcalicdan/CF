<?php

namespace App\ActionService;

use App\Enums\ComplaintStatus;
use App\Enums\OrderCarpetStatus;
use App\Models\Complaint;
use App\Models\OrderCarpet;

class ComplaintService
{
    public function getAllComplaints()
    {
        return Complaint::with('orderCarpet', 'orderCarpet.order.client', 'orderCarpet.order.driver.user')
            ->when(request('order_carpet_id'), function ($query) {
                $query->where('order_carpet_id', request('order_carpet_id'));
            })
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('complaint_details'), function ($query) {
                $query->where('complaint_details', 'like', '%'.request('complaint_details').'%');
            })
            ->paginate(10);
    }

    public function showComplaint(Complaint $complaint)
    {
        $result = $complaint::with('orderCarpet', 'orderCarpet.order.clients', 'orderCarpet.order.driver.user')->find($complaint->id);

        return $result;
    }

    public function storeComplaintThroughCarpets(OrderCarpet $orderCarpet, array $data)
    {
        \DB::transaction(function () use ($orderCarpet, $data) {
            if ($orderCarpet->status === OrderCarpetStatus::COMPLAINT->value) {
                throw new \Exception('Carpet already in complaint status');
            }

            $orderCarpet->complaint()->create([
                'complaint_details' => $data['complaint_details'],
                'status' => ComplaintStatus::OPEN->value,
            ]);

            $orderCarpet->update([
                'status' => OrderCarpetStatus::COMPLAINT->value,
            ]);
        });
    }

    public function updateComplaint(Complaint $complaint, array $data)
    {
        \DB::transaction(function () use ($complaint, $data) {

            $complaint->update($data);

            // Get the related OrderCarpet and Order
            $orderCarpet = $complaint->orderCarpet;

            $complaintStatus = $data['status'] ?? $complaint->status;

            switch ($complaintStatus) {
                case ComplaintStatus::CLOSED->value:
                    $orderCarpet->update([
                        'status' => OrderCarpetStatus::COMPLETED->value,
                    ]);
                    break;

                case ComplaintStatus::RESOLVED->value:
                    $orderCarpet->update([
                        'status' => OrderCarpetStatus::COMPLETED->value,
                    ]);
                    break;

                case ComplaintStatus::IN_PROGRESS->value:
                    $orderCarpet->update([
                        'status' => OrderCarpetStatus::UNDER_REVIEW->value,
                    ]);
                    break;

                case ComplaintStatus::REJECTED->value:
                    $orderCarpet->update([
                        'status' => OrderCarpetStatus::COMPLETED->value,
                    ]);
                    break;

                case ComplaintStatus::OPEN->value:
                    break;

                default:
                    throw new \Exception('Invalid complaint status provided.');
            }
        });
    }
}
