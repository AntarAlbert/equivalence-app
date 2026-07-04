<?php

namespace App\Enum;

enum DiplomeDomaine: string
{
    case INFORMATIQUE = 'informatique';
    case MANAGEMENT = 'management';
    case FINANCE = 'finance';
    case INGENIERIE = 'ingenierie';
    case SANTE = 'sante';
    case DROIT = 'droit';
    case SCIENCES_HUMAINES = 'sciences_humaines';
    case AGRICULTURE = 'agriculture';
    case TOURISME = 'tourisme';
    case ARTS = 'arts';
    case EDUCATION = 'education';
    case SCIENCES = 'sciences';
    case AUTRE = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::INFORMATIQUE => 'Informatique & Numérique',
            self::MANAGEMENT => 'Management & Business',
            self::FINANCE => 'Finance & Comptabilité',
            self::INGENIERIE => 'Ingénierie & Technologies',
            self::SANTE => 'Sciences de la Santé',
            self::DROIT => 'Droit & Sciences Politiques',
            self::SCIENCES_HUMAINES => 'Sciences Humaines & Sociales',
            self::AGRICULTURE => 'Agriculture & Environnement',
            self::TOURISME => 'Tourisme & Hôtellerie',
            self::ARTS => 'Arts, Design & Communication',
            self::EDUCATION => 'Éducation & Formation',
            self::SCIENCES => 'Sciences Fondamentales',
            self::AUTRE => 'Autre',
        };
    }
}
