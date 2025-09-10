<?php

namespace App\Models;

use App\ActionService\EnumTranslationService;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_carpet_id
 * @property string $complaint_details
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderCarpet $orderCarpet
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereComplaintDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereOrderCarpetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_carpet_id',
        'complaint_details',
        'status',
    ];

    public function orderCarpet()
    {
        return $this->belongsTo(OrderCarpet::class, 'order_carpet_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? EnumTranslationService::translate(ComplaintStatus::from($this->status)) : '';
    }

    public function getClientNameAttribute():string
    {
        return $this->orderCarpet->order->client->full_name;
    }
}
