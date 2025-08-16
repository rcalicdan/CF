<?php

namespace App\Enums;

enum OrderPaymentMethods: string
{
    case CASH = 'cash';

    case CARD = 'card';
}
