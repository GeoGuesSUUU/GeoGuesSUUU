<?php

namespace App\Entity;

use App\Repository\StoreItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StoreItemRepository::class)]
class StoreItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'storeItem', cascade: ['persist'])]
    private ?ItemType $item = null;

    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The type must be at least {{ limit }} characters long",
        maxMessage: "The type cannot be longer than {{ limit }} characters"
    )]
    #[ORM\Column(length: 255)]
    private string $type ='auto';

    #[ORM\Column]
    private bool $trending = false;

    #[ORM\Column]
    private int $promotion = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ItemType
     */
    public function getItem(): ItemType
    {
        return $this->item;
    }

    /**
     * @param ItemType $item
     * @return $this
     */
    public function setItem(ItemType $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTrending(): bool
    {
        return $this->trending;
    }

    /**
     * @param bool $trending
     * @return $this
     */
    public function setTrending(bool $trending): self
    {
        $this->trending = $trending;

        return $this;
    }

    /**
     * @return int
     */
    public function getPromotion(): int
    {
        return $this->promotion;
    }

    /**
     * @param int $promotion
     * @return $this
     */
    public function setPromotion(int $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }
}
