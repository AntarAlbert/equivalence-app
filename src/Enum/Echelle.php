<?php

namespace App\Enum;

enum Echelle: string
{
    case ECHELLE_1 = '1';
    case ECHELLE_2 = '2';
    case ECHELLE_3 = '3';
    case ECHELLE_4 = '4';
    case ECHELLE_5 = '5';
    case ECHELLE_6 = '6';
    case ECHELLE_7 = '7';
    case ECHELLE_8 = '8';

    public function getLabel(): string
    {
        return "Échelle {$this->value}";
    }
}
