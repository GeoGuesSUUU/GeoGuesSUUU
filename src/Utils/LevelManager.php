<?php

namespace App\Utils;

class LevelManager
{
    public const LEVEL_MULTIPLIER = 0.9;

    public const LEVEL_1_XP = 1000;

    public static function getLevelByXp(int $xp): int
    {
        return floor(pow($xp / LevelManager::LEVEL_1_XP, LevelManager::LEVEL_MULTIPLIER));
    }

    public static function getXpLevel(int $level): int
    {
        if ($level <= 0) return 0;
        if ($level === 1) return self::LEVEL_1_XP;
        return round(pow($level, 1 / LevelManager::LEVEL_MULTIPLIER) * LevelManager::LEVEL_1_XP);
    }
}
