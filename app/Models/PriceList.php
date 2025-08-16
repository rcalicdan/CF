<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location_postal_code',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_price_lists')
            ->using(ServicePriceList::class)
            ->withPivot(['id', 'price'])
            ->withTimestamps();
    }
}
