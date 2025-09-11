<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteOptimization extends Model
{
    protected $fillable = [
        'driver_id',
        'optimization_date',
        'optimization_result',
        'order_sequence',
        'total_distance',
        'total_time',
        'estimated_fuel_cost',
        'carbon_footprint',
        'is_manual_edit',
        'manual_modifications'
    ];

    protected $casts = [
        'optimization_result' => 'array',
        'order_sequence' => 'array',
        'manual_modifications' => 'array',
        'is_manual_edit' => 'boolean',
        'optimization_date' => 'date'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}