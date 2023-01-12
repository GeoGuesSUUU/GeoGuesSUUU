<?php

namespace App\Entity;

class FindTheFlagUserGuess
{
    private int $userId;

    /** @var FindTheFlagAnswer[]  */
    private array $answers;

    /** @var int[] array  */
    private array $guessScores;

    /**
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->answers = [];
        $this->guessScores = [];
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return FindTheFlagUserGuess
     */
    public function setUserId(int $userId): FindTheFlagUserGuess
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return array
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param array $answers
     * @return FindTheFlagUserGuess
     */
    public function setAnswers(array $answers): FindTheFlagUserGuess
    {
        $this->answers = $answers;
        return $this;
    }

    /**
     * @param string $iso
     * @return FindTheFlagAnswer | null
     */
    public function getAnswer(string $iso): FindTheFlagAnswer | null
    {
        return $this->answers[$iso];
    }

    /**
     * @return array
     */
    public function getGuessScores(): array
    {
        return $this->guessScores;
    }

    /**
     * @param array $guessScores
     * @return FindTheFlagUserGuess
     */
    public function setGuessScores(array $guessScores): FindTheFlagUserGuess
    {
        $this->guessScores = $guessScores;
        return $this;
    }

    public function addAnswer(string $iso, string $correctAnswer, string $userAnswer): self
    {
        $answer = new FindTheFlagAnswer($iso, $correctAnswer, $userAnswer);
        $this->answers[$iso] = $answer;
        if ($correctAnswer === $userAnswer) {
            if (is_null($this->guessScores["good"] ?? null)) {
                $this->guessScores["good"] = 1;
            }
            $this->guessScores["good"] += 1;
        } else {
            if (is_null($this->guessScores["bad"] ?? null)) {
                $this->guessScores["bad"] = 1;
            }
            $this->guessScores["bad"] += 1;
        }
        return $this;
    }

    public function getScore(): int
    {
        $score = 0;
        foreach ($this->getAnswers() as $answer) {
            if ($answer->isCorrect()) $score++;
        }
        $score *= 1000;
        return $score;
    }

}
