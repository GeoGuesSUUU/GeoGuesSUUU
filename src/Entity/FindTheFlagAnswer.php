<?php

namespace App\Entity;

class FindTheFlagAnswer
{
    public string $iso;

    public string $correctAnswer;

    public string $userAnswer;

    /**
     * @param string $iso
     * @param string $correctAnswer
     * @param string $userAnswer
     */
    public function __construct(string $iso, string $correctAnswer, string $userAnswer)
    {
        $this->iso = $iso;
        $this->correctAnswer = $correctAnswer;
        $this->userAnswer = $userAnswer;
    }

    public function isCorrect(): bool
    {
        return $this->correctAnswer === $this->userAnswer;
    }
}
