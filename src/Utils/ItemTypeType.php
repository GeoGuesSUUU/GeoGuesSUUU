<?php

namespace App\Utils;

enum ItemTypeType: string
{
    case TYPE_SKIN = 'skin';
    case TYPE_ATTACK = 'attack';
    case TYPE_EQUIPMENT = 'equipment';
    case TYPE_SUPPORT = 'support';
    case TYPE_OTHER = 'other';

    public static function values() : array {
        return array_map(fn ($i) => $i->value, ItemTypeType::cases());
    }

    public static function isItemType(string $type): bool
    {
        return in_array(strtolower($type), ItemTypeType::values());
    }
}
