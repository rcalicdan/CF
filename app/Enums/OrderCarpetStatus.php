<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum OrderCarpetStatus: string
{
    use TranslatableEnums;

    case PENDING = 'pending';
    case PICKED_UP = 'picked up';
    case AT_LAUNDRY = 'at laundry';
    case MEASURED = 'measured';
    case COMPLETED = 'completed';
    case WAITING = 'waiting';
    case DELIVERED = 'delivered';
    case NOT_DELIVERED = 'not delivered';
    case RETURNED = 'returned';
    case COMPLAINT = 'complaint';
    case UNDER_REVIEW = 'under review';
}
