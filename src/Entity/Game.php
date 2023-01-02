<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['game_api_response', 'game_anti_cr'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The name field is required")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The name must be at least {{ limit }} characters long",
        maxMessage: "The name cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['game_api_response', 'api_new', 'api_edit', 'game_anti_cr'])]
    private string $name;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(
        min: 1,
        max: 1024,
        minMessage: "The description must be at least {{ limit }} characters long",
        maxMessage: "The description cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['game_api_response', 'api_new', 'api_edit', 'game_anti_cr'])]
    private string $description;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(
        min: 1,
        max: 1024,
        minMessage: "The tags must be at least {{ limit }} characters long",
        maxMessage: "The tags cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['game_api_response', 'api_new', 'api_edit', 'game_anti_cr'])]
    private string $tags;

    #[Groups(groups: ['game_api_response'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Level::class)]
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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
            $level->setGame($this);
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
            $level->setGame(null);
        }

        return $this;
    }
}
