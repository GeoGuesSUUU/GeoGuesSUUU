<?php

namespace App\Utils;

enum EffectType: string
{
    case TYPE_LIFE = 'life';
    case TYPE_SHIELD = 'shield';
    case TYPE_PRICE = 'price';

    public static function values() : array {
        return array_map(fn ($i) => $i->value, EffectType::cases());
    }

    public static function isEffectType(string $type): bool
    {
        return in_array($type, EffectType::values());
    }
}
