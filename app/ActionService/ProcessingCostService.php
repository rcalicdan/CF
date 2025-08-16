<?php

namespace App\ActionService;

use App\Models\ProcessingCost;

class ProcessingCostService
{
    public function getAllProcessingCosts()
    {
        $processingCosts = ProcessingCost::query()
            ->when(request('name'), function ($query) {
                $query->where('name', 'like', '%'.request('name').'%');
            })
            ->when(request('type'), function ($query) {
                $query->where('type', request('type'));
            })
            ->when(request('cost_date'), function ($query) {
                $query->whereDate('cost_date', request('cost_date'));
            })
            ->orderBy('cost_date', 'desc')
            ->paginate(30);

        return $processingCosts;
    }

    public function getProcessingCostInformation(ProcessingCost $processingCost)
    {
        return $processingCost;
    }

    public function storeNewProcessingCost(array $data)
    {
        $processingCost = ProcessingCost::create($data);

        return $processingCost;
    }

    public function updateProcessingCostInformation(ProcessingCost $processingCost, array $data)
    {
        $processingCost->update($data);

        return $processingCost;
    }

    public function deleteProcessingCostInformation(ProcessingCost $processingCost)
    {
        $processingCost->delete();

        return true;
    }
}