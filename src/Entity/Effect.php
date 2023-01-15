<?php

namespace App\Entity;

use App\Repository\EffectRepository;
use App\Utils\EffectType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EffectRepository::class)]
class Effect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['effect_api_response', 'effect_anti_cr'])]
    public int $id;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['effect_api_response', 'effect_anti_cr'])]
    public string $type;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['effect_api_response', 'effect_anti_cr'])]
    public int $value;

    #[Groups(groups: ['effect_api_response'])]
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'effects')]
    private ?Country $country;

    #[Groups(groups: ['effect_api_response'])]
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'effects')]
    private ?ItemType $itemType;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Effect
     */
    public function setId(int $id): Effect
    {
        $this->id = $id;
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
     * @return Effect
     */
    public function setType(string $type): self
    {
        if (EffectType::isEffectType($type)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return Effect
     */
    public function setValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     * @return Effect
     */
    public function setCountry(?Country $country): Effect
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return ItemType|null
     */
    public function getItemType(): ?ItemType
    {
        return $this->itemType;
    }

    /**
     * @param ItemType|null $itemType
     * @return Effect
     */
    public function setItemType(?ItemType $itemType): Effect
    {
        $this->itemType = $itemType;
        return $this;
    }
}
