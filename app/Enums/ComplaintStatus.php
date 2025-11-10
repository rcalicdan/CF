<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum ComplaintStatus: string
{
    use TranslatableEnums;
    
    case OPEN = 'open';

    case IN_PROGRESS = 'in progress';

    case RESOLVED = 'resolved';

    case REJECTED = 'rejected';

    case CLOSED = 'closed';
}
