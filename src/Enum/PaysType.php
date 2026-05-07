<?php

declare(strict_types=1);

namespace App\Enum;

enum PaysType: string
{
    case LOCAL = 'LOCAL';
    case ETRANGER = 'ETRANGER';
}
