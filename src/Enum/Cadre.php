<?php

namespace App\Enum;

enum Cadre: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';

    public function getLabel(): string
    {
        return match($this) {
            self::A => 'Cadre A',
            self::B => 'Cadre B',
            self::C => 'Cadre C',
            self::D => 'Cadre D',
            self::E => 'Cadre E',
        };
    }
}
