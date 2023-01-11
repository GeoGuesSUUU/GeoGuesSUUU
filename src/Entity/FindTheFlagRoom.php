<?php

namespace App\Entity;

use App\Utils\CountriesISO;
use App\Utils\GameRoomVisibility;

class FindTheFlagRoom extends GameRoom
{
    /** @var string[] $guess */
    private array $guess = [];

    /** @var string[] $answers */
    private array $answers = [];

    public function clearGame(): void
    {
        $this->guess = [];
        $this->answers = [];
    }

    public function initGame(int $difficulty = 1): void
    {
        $guessCount = (int) ($difficulty * 3);
        $this->clearGame();

        $guessArray = [];
        $answerArray = [];
        $isoAvailable = CountriesISO::countriesCases();
        for ($i = 0; $i < $guessCount; $i++) {
            $rand = random_int(0, count($isoAvailable) - 1);
            $iso = $isoAvailable[$rand];

            if (in_array(strtolower($iso->name), $guessArray)) continue;
            $guessArray[strtolower($iso->value)] = strtolower($iso->name);
            $answerArray[] = strtolower($iso->value);
        }
        $this->guess = $guessArray;
        $this->answers = $answerArray;
    }

    /**
     * @return array
     */
    public function getGuess(): array
    {
        return $this->guess;
    }

    /**
     * @return array
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }
}
