<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CarpetService extends Pivot
{
    protected $table = 'carpet_services';

    protected $fillable = [
        'order_carpet_id',
        'service_id',
        'total_price',
    ];

    public function orderCarpet()
    {
        return $this->belongsTo(OrderCarpet::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
