<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum OrderPaymentStatus: string
{
    use TranslatableEnums;
    case PENDING = 'pending';

    case COMPLETED = 'completed';

    case CANCELED = 'canceled';
}
