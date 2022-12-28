<?php

namespace App\Utils;

enum EffectType: string
{
    // MINUS
    case MINUS_LIFE = 'life-';
    case MINUS_SHIELD = 'shield-';
    case MINUS_PRICE = 'price-';
    case MINUS_CLAIM = 'claim-';

    // BONUS
    case BONUS_LIFE = 'life+';
    case BONUS_SHIELD = 'shield+';
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
