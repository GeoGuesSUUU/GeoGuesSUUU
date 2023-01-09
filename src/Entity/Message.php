<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'messages')]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?string $color;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column]
    private DateTimeImmutable $publishAt;

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Message
     */
    public function setUser(User $user): Message
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Message
     */
    public function setContent(string $content): Message
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPublishAt(): DateTimeImmutable
    {
        return $this->publishAt;
    }

    /**
     * @param DateTimeImmutable $publishAt
     * @return Message
     */
    public function setPublishAt(DateTimeImmutable $publishAt): Message
    {
        $this->publishAt = $publishAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string|null $color
     * @return Message
     */
    public function setColor(?string $color): Message
    {
        $this->color = $color;
        return $this;
    }
}
