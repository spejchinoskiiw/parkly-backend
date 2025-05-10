<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationType: string
{
    case ONDEMAND = 'ondemand';
    case SCHEDULED = 'scheduled';
} 