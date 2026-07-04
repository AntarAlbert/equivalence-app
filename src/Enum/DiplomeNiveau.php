<?php

namespace App\Enum;

enum DiplomeNiveau: string
{
    case BREVET = 'brevet';
    case BAC = 'bac';
    case BAC_PLUS_2 = 'bac_2';
    case LICENCE = 'licence';
    case MASTER = 'master';
    case DOCTORAT = 'doctorat';
    case INGENIEUR = 'ingenieur';
    case CERTIFICAT = 'certificat';
    case EMBA = 'emba';
    case AUTRE = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::BREVET => 'Brevet (BEPC)',
            self::BAC => 'Baccalauréat',
            self::BAC_PLUS_2 => 'Bac+2 (BTS / DUT)',
            self::LICENCE => 'Licence / Bachelor (Bac+3)',
            self::MASTER => 'Master / Mastère (Bac+5)',
            self::DOCTORAT => 'Doctorat / PhD',
            self::INGENIEUR => 'Diplôme d\'Ingénieur',
            self::CERTIFICAT => 'Certificat / Formation courte',
            self::EMBA => 'Executive MBA',
            self::AUTRE => 'Autre',
        };
    }
}
