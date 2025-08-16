<?php

namespace App\Models;

use App\ActionService\EnumTranslationService;
use App\Enums\CostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessingCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'cost_date',
    ];

    protected function casts(): array
    {
        return [
            'cost_date' => 'date',
            'amount' => 'decimal:2',
            'type' => CostType::class,
        ];
    }

    public function getTypeLabelAttribute(): string
    {
       return $this->type ? EnumTranslationService::translate($this->type) : '';
    }
}