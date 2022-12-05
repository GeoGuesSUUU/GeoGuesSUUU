<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 1024)]
    private string $description;

    #[ORM\Column(length: 1024)]
    private string $tags;

    #[ORM\ManyToMany(targetEntity: Level::class, mappedBy: 'games')]
    private Collection $levels;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

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
     * @return string
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     * @return $this
     */
    public function setTags(string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return Collection<int, Level>
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    /**
     * @param Level $level
     * @return $this
     */
    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
            $level->addGame($this);
        }

        return $this;
    }

    /**
     * @param Level $level
     * @return $this
     */
    public function removeLevel(Level $level): self
    {
        if ($this->levels->removeElement($level)) {
            $level->removeGame($this);
        }

        return $this;
    }
}
