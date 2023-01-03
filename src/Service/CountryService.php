<?php

namespace App\Service;

use App\Entity\ClaimRewards;
use App\Entity\Country;
use App\Entity\CountryItem;
use App\Entity\ItemsQuantity;
use App\Entity\ItemType;
use App\Entity\User;
use App\Exception\CountryNotFoundApiException;
use App\Exception\CountryNotValidApiException;
use App\Exception\ItemTypeNotFoundApiException;
use App\Exception\ItemTypeNotValidApiException;
use App\Exception\UserNotValidApiException;
use App\Repository\CountryItemRepository;
use App\Repository\CountryRepository;
use App\Repository\UserRepository;
use App\Utils\EffectType;
use App\Utils\ItemTypeType;
use Exception;

class CountryService
{

    public function __construct(
        private readonly CountryRepository     $countryRepository,
        private readonly CountryItemRepository $countryItemRepository,
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
    )
    {
    }

    public function flush(): void
    {
        $this->countryRepository->flush();
    }

    /**
     * @param Country $entity
     * @return Country
     * @throws Exception
     */
    public function create(Country $entity): Country
    {

        $flag = $entity->getFlag();
        if (is_null($flag)) {
            $entity->setFlag("https://countryflagsapi.com/svg/" . strtolower($entity->getCode()));
        }

        $entity->initOwnedAt();
        $entity->initClaimDate();
        $entity->setLife($entity->getInitLife());
        $entity->setLifeMax($entity->getInitLife());
        $this->countryRepository->save($entity, true);
        return $entity;
    }

    /**
     * @return Country[]
     */
    public function getAll(): array
    {
        return $this->countryRepository->findAll();
    }

    /**
     * @param int $id
     * @return Country | null
     */
    public function getById(int $id): Country | null
    {
        return $this->countryRepository->findOneBy([ 'id' => $id ]);
    }

    /**
     * @param Country $entity
     * @param bool $flush
     * @return Country
     */
    public function save(Country $entity, bool $flush = false): Country
    {
        $this->countryRepository->save($entity, $flush);
        return $entity;
    }

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void
    {
        $entity = $this->getById($id);
        if (!is_null($entity)) {
            $this->countryRepository->remove($entity);
        }
        else {
            throw new CountryNotFoundApiException();
        }
    }

    /**
     * @param Country $country
     * @param string $type
     * @return array|null
     */
    public function foundEffectByType(Country $country, string $type): array | null
    {
        foreach ($country->getEffects() as $effect) {
            if (isset($effect['type']) && $effect['type'] === $type) {
                return $effect;
            }
        }
        return null;
    }

    /**
     * @param Country $country
     * @param array $effect
     * @return int
     */
    public function foundEffectKey(Country $country, array $effect): int
    {
        foreach ($country->getEffects() as $key => $e) {
            if (isset($e['type']) && isset($effect['type']) && $e['type'] === $effect['type']) {
                return $key;
            }
        }
        return -1;
    }

    /**
     * Add or Overwrite Effect value
     * @param Country $country
     * @param array $effect
     * @return Country
     */
    public function addEffect(Country $country, array $effect): Country
    {
        $e = $this->foundEffectByType($country, $effect['type']);

        if (is_null($e)) {
            return $country->addEffect($effect);
        }

        $key = $this->foundEffectKey($country, $effect);
        $es = $country->getEffects();
        if ($key !== -1) {
            $es[$key]['value'] = $effect['value'];
        }
        return $country->setEffects($es);
    }

    public function removeAllEffect(Country $country): Country
    {
        return $country->setEffects([]);
    }

    public function removeItemByType(Country $country, string $type): void
    {
        $links = $this->countryItemRepository->findBy([ 'country' => $country->getId() ]);

        $linksByType = array_filter($links, function ($link) use ($type) {
            return $link->getItemType()->getType() === $type;
        });

        foreach ($linksByType as $linkType) {
            $this->countryItemRepository->remove($linkType);
        }

        $this->countryItemRepository->flush();
    }

    /**
     * @param Country $entity
     * @param User $user
     * @return Country
     * @throws Exception
     */
    public function buy(Country $entity, User $user): Country
    {
        if ($entity->getUser() !== null) {
            throw new CountryNotValidApiException("This country is already owned by someone");
        }
        $entity = $this->calculatePrice($entity);
        if ($user->getCoins() < $entity->getPrice()) {
            throw new UserNotValidApiException("Invalid User coins");
        }
        $user->setCoins($user->getCoins() - $entity->getPrice());

        $this->userRepository->save($user);

        $entity->setUser($user);
        $entity->setOwnedAt(new \DateTimeImmutable());
        $entity->setClaimDate(new \DateTimeImmutable());
        $entity->setEffects([]);
        $entity->setLife($entity->getInitLife());
        $entity->setLifeMax($entity->getInitLife());
        $entity->setShield(0);
        $entity->setShieldMax(10000);
        $this->save($entity);
        $this->userRepository->save($user, true);
        return $entity;
    }

    /**
     * @param Country $entity
     * @param bool $flush
     * @return Country
     * @throws Exception
     */
    public function removeOwner(Country $entity, bool $flush =  false): Country
    {
        $entity->setUser(null);

        $entity->initOwnedAt();
        $entity->initClaimDate();
        $this->save($entity, $flush);
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function attack(Country $country, User $user, ItemType $item): Country
    {
        // if item is in user inventory
        $itemInventory = $this->userService->findItemById($user, $item->getId());
        if (is_null($itemInventory)) {
            throw new ItemTypeNotFoundApiException();
        }

        if ($itemInventory->getType() !== ItemTypeType::TYPE_ATTACK->value) {
            throw new ItemTypeNotValidApiException("Only attack type is valid");
        }

        $damageLife = 0;
        $damageShield = 0;
        // TODO: damage price

        foreach ($item->getEffects() as $effect) {
            if (isset($effect['type']) && isset($effect['value'])) {
                if ($effect['type'] === EffectType::MALUS_SHIELD->value) {
                    $damageShield += $effect['value'];
                }
                elseif ($effect['type'] === EffectType::MALUS_LIFE->value) {
                    $damageLife += $effect['value'];
                }
            }
        }

        $shield = $country->getShield();
        $life = $country->getLife();

        if ($shield < $damageShield) {
            $life = $life + $shield - $damageShield;
            $shield = 0;
        } else {
            $shield -= $damageShield;
        }

        $life -= $damageLife;

        $country->setLife($life);
        $country->setShield($shield);

        if ($life <= 0) {
            $country->setLife(0);
            $this->removeOwner($country);
        }

        $this->userService->removeItemById($user, $item->getId());
        $this->save($country, true);
        return $country;
    }

    public function restoreShield(Country $country, int $percentage, bool $flush = false): Country
    {
        $shield = $country->getShield() + $country->getShieldMax() * ($percentage / 100);
        $country->setShield($shield);

        $this->save($country, $flush);
        return $country;
    }

    public function useSupportItem(Country $country, User $user, ItemType $item): Country
    {
        // if item is in user inventory
        $itemInventory = $this->userService->findItemById($user, $item->getId());
        if (is_null($itemInventory)) {
            throw new ItemTypeNotFoundApiException();
        }

        if (
            $itemInventory->getType() !== ItemTypeType::TYPE_SUPPORT->value) {
            throw new ItemTypeNotValidApiException("Only support type is valid");
        }

        $extraLife = $country->getLife();
        $extraLifeMax = $country->getLifeMax();
        $extraShield = $country->getShield();
        $extraShieldMax = $country->getShieldMax();

        foreach ($item->getEffects() as $effect) {
            if (isset($effect['type']) && isset($effect['value'])) {
                if ($effect['type'] === EffectType::BONUS_SHIELD->value) {
                    $extraShieldMax += $effect['value'];
                }
                elseif ($effect['type'] === EffectType::BONUS_SHIELD_MAX->value) {
                    $extraShieldMax += $effect['value'];
                }
                elseif ($effect['type'] === EffectType::BONUS_LIFE->value) {
                    $extraLife += $effect['value'];
                }
                elseif ($effect['type'] === EffectType::BONUS_LIFE_MAX->value) {
                    $extraLifeMax += $effect['value'];
                }
            }
        }

        $country->setLife($extraLife);
        $country->setLifeMax($extraLifeMax);
        $country->setShield($extraShield);
        $country->setShieldMax($extraShieldMax);

        $this->userService->removeItemById($user, $item->getId());
        $this->save($country, true);
        return $country;
    }

    /**
     * @param Country $country
     * @param User $user
     * @param ItemType $item
     * @return Country
     */
    public function addItemFromInventory(Country $country, User $user, ItemType $item): Country
    {
        // if item is in user inventory
        $itemInventory = $this->userService->findItemById($user, $item->getId());
        if (is_null($itemInventory)) {
            throw new ItemTypeNotFoundApiException();
        }

        // if user isn't owner
        if (is_null($country->getUser()) || $country->getUser()->getId() !== $user->getId()) {
            throw new CountryNotValidApiException();
        }

        // if item is not equipment
        if ($itemInventory->getType() !== ItemTypeType::TYPE_EQUIPMENT->value) {
            throw new ItemTypeNotValidApiException("Only equipment type is valid");
        }

        // if link already exist
        $link = $this->countryItemRepository->findOneBy([
            'country' => $country->getId(),
            'itemType' => $item->getId()
        ]);

        // if not exist
        if (is_null($link)) {
            $link = new CountryItem();
            $link->setCountry($country);
            $link->setItemType($item);
        }

        $link->setQuantity($link->getQuantity() + 1);

        $this->countryItemRepository->save($link);
        $this->userService->removeItemById($user, $item->getId(), true);

        return $country;
    }

    public function calculatePrice(Country $entity): Country
    {
        $pricePercentage = 0.0;

        foreach($entity->getCountryItems()->getValues() as $link) {
            $item = $link->getItemType();
            if ($item->getType() === ItemTypeType::TYPE_EQUIPMENT->value) {
                foreach ($item->getEffects() as $effect) {
                    if (
                        isset($effect['type']) &&
                        isset($effect['value']) &&
                        $effect['type'] === EffectType::BONUS_PRICE->value
                    ) {
                        $pricePercentage += (float) ($effect['value'] * $link->getQuantity());
                    }
                }
            }
        }

        $price = $entity->getInitPrice() + $entity->getInitPrice() * ($pricePercentage / 100);

        return $entity->setPrice((int) $price);
    }

    public function claim(Country $entity, bool $flush = false): ClaimRewards | null {

        $lastClaim = $entity->getClaimDate()->getTimestamp();
        $now = time();
        $days = ($now - $lastClaim) / 3600 / 24;

        if ($days < 1) {
            return null;
        }

        $country = $this->calculatePrice($entity);
        $rewards = new ClaimRewards();

        $coins = round($country->getPrice() / 100);

        // TODO : Rewards item
        /** @var ItemsQuantity[] $rewardItems */
        $rewardItems = [];

        $rewards->setCoins($coins * $days);
        $rewards->setItems($rewardItems);

        $country->setClaimDate(new \DateTimeImmutable());
        $this->save($country, $flush);
        return $rewards;
    }

    public function claimAllByUser(User $user): ClaimRewards {
        $rewards = new ClaimRewards();

        $coins = 0;
        $rewardItems = [];

        foreach ($user->getCountries()->toArray() as $country) {
            $reward = $this->claim($country);
            if (!is_null($reward)) {
                $coins += $reward->getCoins();
                array_push($rewardItems, ...$reward->getItems());
            }
        }

        $this->countryRepository->flush();

        return $rewards->setCoins($coins)->setItems($rewardItems);
    }

}
