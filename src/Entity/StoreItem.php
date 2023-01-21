<?php

namespace App\Entity;

use App\Repository\StoreItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StoreItemRepository::class)]
#[UniqueEntity(fields: ['item'], message: 'There is already this item in the store')]
class StoreItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['store_api_response', 'store_anti_cr'])]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'storeItem')]
    #[Groups(groups: ['store_api_response', 'api_new', 'api_edit'])]
    private ?ItemType $item = null;

    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The type must be at least {{ limit }} characters long",
        maxMessage: "The type cannot be longer than {{ limit }} characters"
    )]
    #[ORM\Column(length: 255)]
    #[Groups(groups: ['store_api_response', 'api_new', 'api_edit', 'store_anti_cr'])]
    private string $type = 'auto';

    #[ORM\Column]
    #[Groups(groups: ['store_api_response', 'api_new', 'api_edit', 'store_anti_cr'])]
    private bool $trending = false;

    #[Assert\GreaterThanOrEqual(value: 0, message: "The promotion cannot be less than {{ limit }}")]
    #[Assert\LessThanOrEqual(value: 100, message: "The promotion cannot be greater than {{ limit }}")]
    #[ORM\Column]
    #[Groups(groups: ['store_api_response', 'api_new', 'api_edit', 'store_anti_cr'])]
    private int $promotion = 0;

    // ==============================//
    #[Groups(groups: ['store_api_response', 'store_anti_cr'])]
    private int $promoPrice = 0;
    // ==============================//

    /**
     * @param int $id
     * @return StoreItem
     */
    public function setId(int $id): StoreItem
    {
        $this->id = $id;
        return $this;
    }

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

    /**
     * @return int
     */
    public function getPromoPrice(): int
    {
        return $this->promoPrice;
    }

    /**
     * @param int $promoPrice
     * @return StoreItem
     */
    public function setPromoPrice(int $promoPrice): StoreItem
    {
        $this->promoPrice = $promoPrice;
        return $this;
    }
}
