<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum OrderStatus: string
{
    use TranslatableEnums;

    case PENDING = 'pending';

    case ACCEPTED = 'accepted';

    case PROCESSING = 'processing';

    case COMPLETED = 'completed';

    case UNDELIVERED = 'undelivered';

    case DELIVERED = 'delivered';

    case CANCELED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
