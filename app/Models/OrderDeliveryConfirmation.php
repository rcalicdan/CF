<?php

namespace App\Models;

use App\ActionService\EnumTranslationService;
use App\Enums\OrderDeliveryConfirmationType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property string $confirmation_type
 * @property string|null $signature_url
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereConfirmationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereSignatureUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class OrderDeliveryConfirmation extends Model
{
    protected $fillable = [
        'order_id',
        'confirmation_type',
        'signature_url',
        'confirmation_data',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getConfirmationTypeLabelAttribute()
    {
        return $this->confirmation_type ? EnumTranslationService::translate(OrderDeliveryConfirmationType::from($this->confirmation_type)) : '';
    }
}
