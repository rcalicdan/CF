<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_price',
        'is_area_based',
    ];

    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class, 'service_price_lists')
            ->using(ServicePriceList::class)
            ->withPivot(['id', 'price'])
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(OrderService::class, 'order_services')
            ->using(OrderService::class)
            ->withPivot(['id', 'quantity', 'total_price'])
            ->withTimestamps();
    }

    public function orderCarpets()
    {
        return $this->belongsToMany(OrderCarpet::class, 'carpet_services', 'service_id', 'carpet_id')
            ->using(CarpetService::class)
            ->withPivot(['id', 'total_price'])
            ->withTimestamps();
    }
}
