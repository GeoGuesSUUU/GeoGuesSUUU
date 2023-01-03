<?php

namespace App\Utils;

enum Rarity: string
{
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';

    public static function values() : array {
        return array_map(fn ($i) => $i->value, Rarity::cases());
    }

    public static function isRarity(string $type): bool
    {
        return in_array(strtolower($type), Rarity::values());
    }
}
