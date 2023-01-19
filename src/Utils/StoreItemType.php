<?php

namespace App\Utils;

enum StoreItemType: string
{
    case AUTO = 'auto';
    case MANUAL = 'manual';

    public static function values(): array
    {
        return array_map(fn($i) => $i->value, StoreItemType::cases());
    }

    public static function isStoreItemType(string $type): bool
    {
        return in_array(strtolower($type), StoreItemType::values());
    }
}