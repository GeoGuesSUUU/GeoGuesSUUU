<?php

namespace App\Entity;

use App\Exception\GameNotStartException;
use App\Utils\CountriesISO;
use Exception;

class FindTheFlagRoom extends GameRoom
{
    /** @var string[] $guess */
    private array $guess = [];

    /** @var string[] $answers */
    private array $answers = [];

    /** @var FindTheFlagUserGuess[] $userGuess  */
    private array $userGuess = [];

    private int $multiplier = 1;

    private int $difficulty = 1;

    public function clearGame(): void
    {
        $this->guess = [];
        $this->answers = [];
    }

    /**
     * @throws Exception
     */
    public function initGame(int $difficulty = 1): void
    {
        $this->difficulty = $difficulty;
        $guessCount = $this->difficulty * 3;
        $this->clearGame();
        $this->initMultiplier();

        $guessArray = [];
        $answerArray = [];
        $isoAvailable = CountriesISO::countriesCases();
        for ($i = 0; $i < $guessCount; $i++) {
            $rand = random_int(0, count($isoAvailable) - 1);
            $iso = $isoAvailable[$rand];

            if (in_array(strtolower($iso->name), $guessArray)) {
                $guessCount++;
                continue;
            }
            $guessArray[] = strtolower($iso->name);
            $answerArray[strtolower($iso->name)] = $iso->value;
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

    /**
     * @return array
     */
    public function getUserGuess(): array
    {
        return $this->userGuess;
    }

    /**
     * @param int $userId
     * @return FindTheFlagUserGuess|null
     */
    public function getUserGuessByUserId(int $userId): FindTheFlagUserGuess | null
    {
        return $this->userGuess[$userId] ?? null;
    }

    /**
     * @param int $userId
     * @param string $iso
     * @param string $response
     * @return bool
     * @throws GameNotStartException
     */
    public function guess(int $userId, string $iso, string $response): bool
    {
        if (empty($this->answers)) throw new GameNotStartException();

        if (is_null($this->userGuess[$userId] ?? null)) {
            $this->userGuess[$userId] = new FindTheFlagUserGuess(
                $userId
            );
        }

        $this->userGuess[$userId]->addAnswer($iso, $this->answers[$iso], $response);

        return $this->userGuess[$userId]->getAnswer($iso)->isCorrect();
    }

    /**
     * @return int
     */
    public function getMultiplier(): int
    {
        return $this->multiplier;
    }

    /**
     * @return FindTheFlagRoom
     */
    public function initMultiplier(): FindTheFlagRoom
    {
        $int = count($this->getConnections());
        if ($int > 8) $int = 8;
        elseif ($int < 1) $int = 1;
        $this->multiplier = $int;
        return $this;
    }

    /**
     * @return int
     */
    public function getDifficulty(): int
    {
        return $this->difficulty;
    }
}
