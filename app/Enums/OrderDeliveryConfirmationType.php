<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum OrderDeliveryConfirmationType: string
{
    use TranslatableEnums;
    case DATA = 'data';
    case SIGNATURE = 'signature';
}
