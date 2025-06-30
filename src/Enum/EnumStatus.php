<?php

namespace App\Enum;

enum EnumStatus : string
{
    case CREATED = 'Created';
    case OPENED = 'Opened';
    case CLOSED = 'Closed';
    case IN_PROGRESS = 'In progress';
    case PAST = 'Past';
    case CANCELED = 'Canceled';

}
