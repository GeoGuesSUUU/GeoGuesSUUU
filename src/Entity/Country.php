<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
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

    #[ORM\Column(length: 2)]
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
    #[Assert\NotBlank(message: "The flag field is required")]
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

    #[ORM\Column]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private int $initLife = 0;

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
     * @return string
     */
    public function getFlag(): string
    {
        return $this->flag;
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
     */
    public function setOwnedAt(?DateTimeImmutable $ownedAt): void
    {
        $this->ownedAt = $ownedAt;
    }
}
