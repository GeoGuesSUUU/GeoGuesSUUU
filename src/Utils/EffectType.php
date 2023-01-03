<?php

namespace App\Utils;

enum EffectType: string
{
    // MALUS
    case MALUS_LIFE = 'life-';
    case MALUS_SHIELD = 'shield-';
    case MALUS_PRICE = 'price-';
    case MALUS_CLAIM = 'claim-';

    // BONUS
    case BONUS_LIFE = 'life+';
    case BONUS_LIFE_MAX = 'life_max+';
    case BONUS_SHIELD = 'shield+';
    case BONUS_SHIELD_MAX = 'shield_max+';
    case BONUS_PRICE = 'price+';
    case BONUS_CLAIM = 'claim+';

    public static function values() : array {
        return array_map(fn ($i) => $i->value, EffectType::cases());
    }

    public static function isEffectType(string $type): bool
    {
        return in_array($type, EffectType::values());
    }
}
