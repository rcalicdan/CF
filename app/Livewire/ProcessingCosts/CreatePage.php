<?php

namespace App\Livewire\ProcessingCosts;

use App\ActionService\ProcessingCostService;
use App\Enums\CostType;
use App\Models\ProcessingCost;
use Livewire\Component;

class CreatePage extends Component
{
    public $name = '';
    public $type = '';
    public $amount = '';
    public $cost_date = '';

    protected ProcessingCostService $processingCostService;

    public function boot(ProcessingCostService $processingCostService)
    {
        $this->processingCostService = $processingCostService;
    }

    public function mount()
    {
        $this->type = CostType::ENERGY->value;
        $this->cost_date = now()->format('Y-m-d');
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

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'amount' => $this->amount,
            'cost_date' => $this->cost_date,
        ];

        try {
            $this->processingCostService->storeNewProcessingCost($data);

            session()->flash('success', 'Koszt przetwarzania został pomyślnie utworzony!');

            return redirect()->route('processing-costs.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas tworzenia kosztu przetwarzania. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        return view('livewire.processing-costs.create-page', [
            'typeOptions' => CostType::options()
        ]);
    }
}
