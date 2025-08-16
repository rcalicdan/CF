<?php

namespace App\Enums;

use App\Traits\TranslatableEnums;

enum CostType: string
{
    use TranslatableEnums;

    case ENERGY = 'energy';
    case WATER = 'water';
    case FUEL = 'fuel';
    case WAGES = 'wages';
    case CHEMICALS = 'chemicals';
    case SUPPLIES = 'supplies';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match($this) {
            self::ENERGY => 'Energia',
            self::WATER => 'Woda',
            self::FUEL => 'Paliwo',
            self::WAGES => 'Wynagrodzenia',
            self::CHEMICALS => 'Chemikalia',
            self::SUPPLIES => 'Materiały',
            self::OTHER => 'Inne',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ENERGY => '#FF6B6B',
            self::WATER => '#4ECDC4',
            self::FUEL => '#45B7D1',
            self::WAGES => '#96CEB4',
            self::CHEMICALS => '#FFEAA7',
            self::SUPPLIES => '#DDA0DD',
            self::OTHER => '#98D8C8',
        };
    }
}