<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['country_api_response', 'country_anti_cr'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['country_api_response', 'api_new', 'api_edit', 'country_anti_cr'])]
    private string $flag;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: CountryItem::class)]
    #[Groups(groups: ['country_api_response'])]
    private Collection $countryItems;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'countries')]
    #[Groups(groups: ['country_api_response', 'api_edit'])]
    private ?User $user = null;

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
}
