<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private int $coins = 0;

    #[ORM\Column]
    private bool $admin = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Score::class)]
    private Collection $scores;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserItem::class)]
    private Collection $userItems;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Country::class)]
    private Collection $countries;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
        $this->userItems = new ArrayCollection();
        $this->countries = new ArrayCollection();
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
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return int
     */
    public function getCoins(): int
    {
        return $this->coins;
    }

    /**
     * @param int $coins
     * @return User
     */
    public function setCoins(int $coins): self
    {
        $this->coins = $coins;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     * @return User
     */
    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    /**
     * @param Score $score
     * @return $this
     */
    public function addScore(Score $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setUser($this);
        }

        return $this;
    }

    /**
     * @param Score $score
     * @return $this
     */
    public function removeScore(Score $score): self
    {
        if ($this->scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getUser() === $this) {
                $score->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserItem>
     */
    public function getUserItems(): Collection
    {
        return $this->userItems;
    }

    /**
     * @param UserItem $userItem
     * @return $this
     */
    public function addUserItem(UserItem $userItem): self
    {
        if (!$this->userItems->contains($userItem)) {
            $this->userItems->add($userItem);
            $userItem->setUser($this);
        }

        return $this;
    }

    /**
     * @param UserItem $userItem
     * @return $this
     */
    public function removeUserItem(UserItem $userItem): self
    {
        if ($this->userItems->removeElement($userItem)) {
            // set the owning side to null (unless already changed)
            if ($userItem->getUser() === $this) {
                $userItem->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Country>
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function addCountry(Country $country): self
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
            $country->setUser($this);
        }

        return $this;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function removeCountry(Country $country): self
    {
        if ($this->countries->removeElement($country)) {
            // set the owning side to null (unless already changed)
            if ($country->getUser() === $this) {
                $country->setUser(null);
            }
        }

        return $this;
    }
}
