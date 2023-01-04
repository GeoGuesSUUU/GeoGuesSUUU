<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'There is already a country with this ISO 3166-1 alpha-2 Code')]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The name field is required")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The name must be at least {{ limit }} characters long",
        maxMessage: "The name cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private string $name;

    #[ORM\Column(length: 2, unique: true)]
    #[Assert\NotBlank(message: "The name field is required")]
    #[Assert\Regex(pattern: "/^[A-Z]{2}$/", message: "The code field is only ISO 3166-1 alpha-2", match: true)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The code must be at least {{ limit }} characters long",
        maxMessage: "The code cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private string $code;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The flag must be at least {{ limit }} characters long",
        maxMessage: "The flag cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private string $flag;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The continent field is required")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The continent must be at least {{ limit }} characters long",
        maxMessage: "The continent cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private string $continent;

    #[ORM\Column(type: 'bigint')]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private int $initLife = 0;

    #[ORM\Column(type: 'bigint')]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $life = 0;

    #[ORM\Column(type: 'bigint')]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $lifeMax = 0;

    #[ORM\Column(type: 'bigint')]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $shield = 0;

    #[ORM\Column(type: 'bigint')]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $shieldMax = 0;

    #[ORM\Column]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private int $initPrice = 0;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private ?DateTimeImmutable $claimDate;

    /**
     * @OA\Property(type="array", @OA\Items(ref="Effect::class"))
     */
    #[ORM\Column]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private ?array $effects = [];

    // ==============================//
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $price = 0;
    // ==============================//

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: CountryItem::class)]
    #[Groups(groups: ['country_api_response'])]
    private Collection $countryItems;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'countries')]
    #[Groups(groups: ['country_api_response', 'api_edit'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private ?DateTimeImmutable $ownedAt;

    public function __construct()
    {
        $this->countryItems = new ArrayCollection();
    }

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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getFlag(): ?string
    {
        return $this->flag ?? null;
    }

    /**
     * @param string $flag
     * @return $this
     */
    public function setFlag(string $flag): self
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * @return string
     */
    public function getContinent(): string
    {
        return $this->continent;
    }

    /**
     * @param string $continent
     */
    public function setContinent(string $continent): void
    {
        $this->continent = $continent;
    }

    /**
     * @return int
     */
    public function getInitLife(): int
    {
        return $this->initLife;
    }

    /**
     * @param int $initLife
     */
    public function setInitLife(int $initLife): void
    {
        $this->initLife = $initLife;
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
            $countryItem->setCountry($this);
        }

        return $this;
    }

    public function removeCountryItem(CountryItem $countryItem): self
    {
        if ($this->countryItems->removeElement($countryItem) && $countryItem->getCountry() === $this) {
            $countryItem->setCountry(null);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getOwnedAt(): ?DateTimeImmutable
    {
        return $this->ownedAt;
    }

    /**
     * @param DateTimeImmutable|null $ownedAt
     * @return Country
     * @throws Exception
     */
    public function setOwnedAt(?DateTimeImmutable $ownedAt): self
    {
        if (is_null($ownedAt)) {
            return $this->initOwnedAt();
        }
        $this->ownedAt = $ownedAt;
        return $this;
    }

    /**
     * @return Country
     * @throws Exception
     */
    public function initOwnedAt(): self
    {
        $this->ownedAt = new DateTimeImmutable("3000-01-01");
        return $this;
    }

    /**
     * @return int
     */
    public function getInitPrice(): int
    {
        return $this->initPrice;
    }

    /**
     * @param int $initPrice
     */
    public function setInitPrice(int $initPrice): void
    {
        $this->initPrice = $initPrice;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getClaimDate(): ?DateTimeImmutable
    {
        return $this->claimDate;
    }

    /**
     * @param DateTimeImmutable|null $claimDate
     */
    public function setClaimDate(?DateTimeImmutable $claimDate): void
    {
        $this->claimDate = $claimDate;
    }

    /**
     * @return Country
     * @throws Exception
     */
    public function initClaimDate(): self
    {
        $this->claimDate = new DateTimeImmutable("3000-01-01");
        return $this;
    }

    /**
     * @return int
     */
    public function getLife(): int
    {
        return $this->life;
    }

    /**
     * @param int $life
     * @return Country
     */
    public function setLife(int $life): self
    {
        if ($life > $this->lifeMax) {
            $this->life = $this->lifeMax;
        } else {
            $this->life = $life;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getShield(): int
    {
        return $this->shield;
    }

    /**
     * @param int $shield
     * @return Country
     */
    public function setShield(int $shield): self
    {
        if ($shield > $this->shieldMax) {
            $this->shield = $this->shieldMax;
        } else {
            $this->shield = $shield;
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
     * @return Country
     */
    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int
     */
    public function getLifeMax(): int
    {
        return $this->lifeMax;
    }

    /**
     * @param int $lifeMax
     * @return Country
     */
    public function setLifeMax(int $lifeMax): self
    {
        $this->lifeMax = $lifeMax;
        return $this;
    }

    /**
     * @return int
     */
    public function getShieldMax(): int
    {
        return $this->shieldMax;
    }

    /**
     * @param int $shieldMax
     * @return Country
     */
    public function setShieldMax(int $shieldMax): self
    {
        $this->shieldMax = $shieldMax;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getEffects(): ?array
    {
        return $this->effects;
    }

    /**
     * @param array|null $effects
     * @return Country
     */
    public function setEffects(?array $effects): Country
    {
        $this->effects = $effects;
        return $this;
    }

    public function addEffect(mixed $effect): Country
    {
        $this->effects[] = $effect;
        return $this;
    }
}
