<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Utils\LevelManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['user_api_response', 'user_anti_cr'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The name field is required")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "The name must be at least {{ limit }} characters long",
        maxMessage: "The name cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['user_api_response', 'api_new', 'api_edit', 'user_anti_cr'])]
    private string $name;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "The email field is required")]
    #[Assert\Email]
    #[Assert\Length(
        min: 1,
        max: 180,
        minMessage: "The email must be at least {{ limit }} characters long",
        maxMessage: "The email cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['user_api_response', 'api_new', 'api_edit', 'api_login', 'user_anti_cr'])]
    private string $email;

    #[ORM\Column]
    #[Groups(groups: ['user_private', 'api_edit', 'user_anti_cr'])]
    private int $coins = 0;

    #[ORM\Column]
    #[Groups(groups: ['user_api_response', 'api_edit', 'user_anti_cr'])]
    private int $xp = 0;

    // ==============================//
    #[Groups(groups: ['user_api_response', 'user_anti_cr'])]
    private int $level = 0;

    #[Groups(groups: ['user_api_response', 'user_anti_cr'])]
    private int $levelXpMax = 0;

    #[Groups(groups: ['user_api_response', 'user_anti_cr'])]
    private int $levelXpMin = 0;
    // ==============================//

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The locale field is required")]
    #[Assert\Length(
        min: 1,
        max: 5,
        minMessage: "The locale must be at least {{ limit }} characters long",
        maxMessage: "The locale cannot be longer than {{ limit }} characters"
    )]
    #[Groups(groups: ['user_api_response', 'api_edit', 'user_anti_cr'])]
    private string $locale = "en-US";

    /**
     * @OA\Property(type="array", @OA\Items(type="string"))
     */
    #[ORM\Column]
    #[Groups(groups: ['user_api_response'])]
    private array $roles = ['ROLE_USER'];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Score::class)]
    #[Groups(groups: ['user_details'])]
    private Collection $scores;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserItem::class)]
    #[Groups(groups: ['user_private'])]
    private Collection $userItems;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Country::class)]
    #[Groups(groups: ['user_details'])]
    private Collection $countries;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "The password field is required")]
    #[Groups(groups: ['api_new', 'api_edit', 'api_login'])]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    #[SerializedName('isVerified')]
    #[Groups(groups: ['user_api_response', 'user_anti_cr'])]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
        $this->userItems = new ArrayCollection();
        $this->countries = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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
     * @return int
     */
    public function getXp(): int
    {
        return $this->xp;
    }

    /**
     * @param int $xp
     */
    public function setXp(int $xp): void
    {
        $this->xp = $xp;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param ?string $password
     * @return $this
     */
    public function setPassword(?string $password): self
    {
        if (!is_null($password)) {
            $this->password = $password;
        }
        return $this;
    }

    public function encryptPassword(UserPasswordHasherInterface $passwordHasher): self
    {
        $hashedPassword = $passwordHasher->hashPassword(
            $this,
            $this->getPassword()
        );
        $this->setPassword($hashedPassword);
        return $this;
    }

    /**
     * @return $this
     */
    public function unsetPassword(): self
    {
        unset($this->password);
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
        if ($this->scores->removeElement($score) && $score->getUser() === $this) {
            $score->setUser(null);
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
        if ($this->userItems->removeElement($userItem) && $userItem->getUser() === $this) {
            $userItem->setUser(null);
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
        if ($this->countries->removeElement($country) && $country->getUser() === $this) {
            $country->setUser(null);
        }

        return $this;
    }

    public function unsetCountries(): self
    {
        unset($this->countries);
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getLevelXpMax(): int
    {
        return $this->levelXpMax;
    }

    /**
     * @param int $levelXpMax
     */
    public function setLevelXpMax(int $levelXpMax): void
    {
        $this->levelXpMax = $levelXpMax;
    }

    /**
     * @return int
     */
    public function getLevelXpMin(): int
    {
        return $this->levelXpMin;
    }

    /**
     * @param int $levelXpMin
     */
    public function setLevelXpMin(int $levelXpMin): void
    {
        $this->levelXpMin = $levelXpMin;
    }

    public function setLevelData(): self
    {
        $xp = $this->getXp();
        $level = LevelManager::getLevelByXp($xp);
        $levelXpMax = LevelManager::getXpLevel($level + 1);
        $levelXpMin = LevelManager::getXpLevel($level);

        $this->setLevel($level);
        $this->setLevelXpMax($levelXpMax);
        $this->setLevelXpMin($levelXpMin);

        return $this;
    }
}
