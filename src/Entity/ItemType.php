<?php

namespace App\Entity;

use App\Repository\EffectRepository;
use App\Repository\ItemTypeRepository;
use App\Utils\ItemTypeType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemTypeRepository::class)]
class ItemType
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['item_api_response', 'item_anti_cr'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The name must be at least {{ limit }} characters long",
        maxMessage: "The name cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private string $name;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(
        max: 1024,
        maxMessage: "The description cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The type must be at least {{ limit }} characters long",
        maxMessage: "The type cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private string $type = "other";

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The rarity must be at least {{ limit }} characters long",
        maxMessage: "The rarity cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private string $rarity = "common";

    #[ORM\Column]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private bool $fantastic = false;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "The img cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private ?string $img;

    #[ORM\Column(options: [ "unsigned" => true ])]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private int $price = 0;

    #[ORM\OneToMany(mappedBy: 'itemType', targetEntity: Effect::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(groups: ['item_api_response', 'api_new', 'api_edit', 'item_anti_cr'])]
    private Collection $effects;

    #[ORM\OneToMany(mappedBy: 'itemType', targetEntity: UserItem::class, orphanRemoval: true)]
    #[Groups(groups: ['item_api_response'])]
    private Collection $userItems;

    #[ORM\OneToMany(mappedBy: 'itemType', targetEntity: CountryItem::class, orphanRemoval: true)]
    #[Groups(groups: ['item_api_response'])]
    private Collection $countryItems;

    #[ORM\OneToOne(mappedBy: 'itemType', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(groups: ['item_api_response'])]
    private ?StoreItem $storeItem = null;

    public function __construct()
    {
        $this->effects = new ArrayCollection();
        $this->userItems = new ArrayCollection();
        $this->countryItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (ItemTypeType::isItemType($type)) {
            $this->type = $type;
        }

        return $this;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): self
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function isFantastic(): bool
    {
        return $this->fantastic;
    }

    public function setFantastic(bool $fantastic): self
    {
        $this->fantastic = $fantastic;

        return $this;
    }

    /**
     * @return Collection<int, UserItem>
     */
    public function getUserItems(): Collection
    {
        return $this->userItems;
    }

    public function addUserItem(UserItem $userItem): self
    {
        if (!$this->userItems->contains($userItem)) {
            $this->userItems->add($userItem);
            $userItem->setItemType($this);
        }

        return $this;
    }

    public function removeUserItem(UserItem $userItem): self
    {
        if ($this->userItems->removeElement($userItem) && $userItem->getItemType() === $this) {
            $userItem->setItemType(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, CountryItem>
     */
    public function getCountryItems(): Collection
    {
        return $this->countryItems;
    }

    public function addCountryItem(CountryItem $countryItem): self
    {
        if (!$this->countryItems->contains($countryItem)) {
            $this->countryItems->add($countryItem);
            $countryItem->setItemType($this);
        }

        return $this;
    }

    public function removeCountryItem(CountryItem $countryItem): self
    {
        if ($this->countryItems->removeElement($countryItem) && $countryItem->getItemType() === $this) {
            $countryItem->setItemType(null);
        }

        return $this;
    }

    /**
     * @return string | null
     */
    public function getImg(): ?string
    {
        return $this->img;
    }

    /**
     * @param string | null $img
     */
    public function setImg(?string $img): void
    {
        $this->img = $img;
    }

    /**
     * @return Collection<int, Effect>
     */
    public function getEffects(): Collection
    {
        return $this->effects;
    }

    public function setEffectsByArray(EffectRepository $effectRepository, array $effects): self
    {
        foreach ($effects as $effect) {
            $e = new Effect();
            $e->setType($effect['type']);
            $e->setValue($effect['value']);
            $this->addEffect($e);
            $effectRepository->save($e);
        }
        return $this;
    }

    public function addEffect(Effect $effect): self
    {
        if (!$this->effects->contains($effect)) {
            $this->effects->add($effect);
            $effect->setItemType($this);
        }

        return $this;
    }

    public function removeEffect(Effect $effect): self
    {
        if ($this->effects->removeElement($effect) && $effect->getItemType() === $this) {
            $effect->setItemType(null);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     * @return ItemType
     */
    public function setPrice(int $price): ItemType
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return StoreItem|null
     */
    public function getStoreItem(): ?StoreItem
    {
        return $this->storeItem;
    }

    /**
     * @param StoreItem|null $storeItem
     * @return ItemType
     */
    public function setStoreItem(?StoreItem $storeItem): ItemType
    {
        $this->storeItem = $storeItem;
        return $this;
    }
}
