<?php

namespace App\Entity;

use App\Repository\ScoreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ScoreRepository::class)]
class Score
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['score_api_response', 'score_anti_cr', 'user_details'])]
    private int $id;

    #[ORM\Column]
    #[Groups(groups: ['score_api_response', 'api_new', 'api_edit', 'score_anti_cr', 'user_details'])]
    private int $score;

    #[ORM\Column]
    #[Groups(groups: ['score_api_response', 'score_anti_cr', 'user_details'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(groups: ['score_api_response', 'api_new', 'api_edit', 'score_anti_cr', 'user_details'])]
    private int $time;

    #[ORM\ManyToOne(inversedBy: 'scores')]
    #[Groups(groups: ['score_api_response', 'api_new', 'api_edit', 'user_details'])]
    private Level $level;

    #[ORM\ManyToOne(inversedBy: 'scores')]
    #[Groups(groups: ['score_api_response', 'api_new', 'api_edit'])]
    private User $user;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return $this
     */
    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return $this
     */
    public function initCreatedAt(): self
    {
        $this->createdAt = new \DateTimeImmutable();
        return $this;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $time
     * @return $this
     */
    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->level;
    }

    /**
     * @param Level $level
     */
    public function setLevel(Level $level): void
    {
        $this->level = $level;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
