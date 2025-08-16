<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case OPEN = 'open';

    case IN_PROGRESS = 'in progress';

    case RESOLVED = 'resolved';

    case REJECTED = 'rejected';

    case CLOSED = 'closed';
}
