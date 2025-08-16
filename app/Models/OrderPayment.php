<?php

namespace App\Models;

use App\ActionService\EnumTranslationService;
use App\Enums\OrderPaymentStatus;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'order_payments';

    protected $fillable = [
        'order_id',
        'amount_paid',
        'payment_method',
        'status',
        'paid_at',
    ];

    public function getStatusLabelAttribute()
    {
        return $this->status ? EnumTranslationService::translate(OrderPaymentStatus::from($this->status)) : '';
    }

    public function casts()
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
