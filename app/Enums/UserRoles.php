<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum UserRoles: string
{
    use TranslatableEnums;
    
    case ADMIN = 'admin';

    case EMPLOYEE = 'employee';

    case DRIVER = 'driver';
}
