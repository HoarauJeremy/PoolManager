<?php
namespace App\Enum;

enum Status: string
{
    case PLANIFIER = 'Planifer';
    case TERMINER = 'Terminer';
    case ANNULER = 'Annuler';
    case ENCOURS = 'En cours';


    public function label(): string
    {
        return match($this) {
            self::PLANIFIER => 'Planifer',
            self::TERMINER => 'Terminer',
            self::ANNULER => 'Annuler',
            self::ENCOURS => 'En cours',
        };
    }
}