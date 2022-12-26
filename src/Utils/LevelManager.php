<?php

namespace App\Utils;

class LevelManager
{
    public const LEVEL_MULTIPLIER = 1.5;

    public const LEVEL_1_XP = 1500;

    public static function getLevelByXp(int $xp): int
    {
        return round($xp / (LevelManager::LEVEL_1_XP * LevelManager::LEVEL_MULTIPLIER), 0, PHP_ROUND_HALF_DOWN);
    }

    public static function getXpLevel(int $level): int
    {
        if ($level <= 0) return 0;
        if ($level === 1) return self::LEVEL_1_XP;
        return round(self::LEVEL_1_XP * self::LEVEL_MULTIPLIER * $level);
    }
}
