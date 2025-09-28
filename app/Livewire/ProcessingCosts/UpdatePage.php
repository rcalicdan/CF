<?php

namespace App\Livewire\ProcessingCosts;

use App\ActionService\ProcessingCostService;
use App\Enums\CostType;
use App\Models\ProcessingCost;
use Livewire\Component;

class UpdatePage extends Component
{
    public ProcessingCost $processingCost;
    public $name = '';
    public $type = '';
    public $amount = '';
    public $cost_date = '';

    protected ProcessingCostService $processingCostService;

    public function boot(ProcessingCostService $processingCostService)
    {
        $this->processingCostService = $processingCostService;
    }

    public function mount(ProcessingCost $processingCost)
    {
        $this->processingCost = $processingCost;

        $this->name = $processingCost->name;
        $this->type = $processingCost->type->value;
        $this->amount = $processingCost->amount;
        $this->cost_date = $processingCost->cost_date->format('Y-m-d');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_column(CostType::cases(), 'value')),
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
        ];
    }

    public function validationAttributes()
    {
        return [
            'cost_date' => 'cost date',
        ];
    }

    public function update()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'amount' => $this->amount,
            'cost_date' => $this->cost_date,
        ];

        try {
            $this->processingCostService->updateProcessingCostInformation($this->processingCost, $data);

            session()->flash('success', 'Koszt przetwarzania został pomyślnie zaktualizowany!');

            return redirect()->route('processing-costs.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas aktualizacji kosztu przetwarzania. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        return view('livewire.processing-costs.update-page', [
            'typeOptions' => CostType::options()
        ]);
    }
}
