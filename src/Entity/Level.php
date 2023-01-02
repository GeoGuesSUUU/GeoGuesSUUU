<?php

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['level_api_response', 'level_anti_cr'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['level_api_response', 'api_new', 'api_edit', 'level_anti_cr'])]
    private string $label;

    #[ORM\Column(length: 1024)]
    #[Groups(groups: ['level_api_response', 'api_new', 'api_edit', 'level_anti_cr'])]
    private string $description;

    #[ORM\ManyToOne(inversedBy: 'levels')]
    #[Groups(groups: ['level_api_response', 'api_new', 'api_edit'])]
    private Game $game;

    #[ORM\OneToMany(mappedBy: 'levels', targetEntity: Score::class)]
    #[Groups(groups: ['level_api_response'])]
    private Collection $scores;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
    }

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
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }

    /**
     * @param Game $game
     * @return Level
     */
    public function setGame(Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    /**
     * @param Score $score
     * @return $this
     */
    public function addScore(Score $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setLevels($this);
        }

        return $this;
    }

    /**
     * @param Score $score
     * @return $this
     */
    public function removeScore(Score $score): self
    {
        if ($this->scores->removeElement($score) && $score->getLevels() === $this) {
            $score->setLevels(null);
        }

        return $this;
    }
}
