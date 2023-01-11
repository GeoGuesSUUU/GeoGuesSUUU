<?php

namespace App\Utils;

enum GameRoomVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    public static function values() : array {
        return array_map(fn ($i) => $i->value, GameRoomVisibility::cases());
    }

    public static function isEffectType(string $type): bool
    {
        return in_array($type, GameRoomVisibility::values());
    }
}
