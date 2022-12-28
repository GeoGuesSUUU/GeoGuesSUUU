<?php

namespace App\Service;

use App\Entity\ClaimRewards;
use App\Entity\Country;
use App\Entity\CountryItem;
use App\Entity\Effect;
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

    /**
     * @param Country $entity
     * @return Country
     * @throws Exception
     */
    public function create(Country $entity): Country
    {
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
        $this->save($entity);
        $this->userRepository->save($user, true);
        return $entity;
    }

    /**
     * @param Country $entity
     * @return Country
     * @throws Exception
     */
    public function removeOwner(Country $entity): Country
    {
        $entity->setUser(null);

        $this->removeItemByType($entity, ItemTypeType::TYPE_SUPPORT->value);

        $entity->initOwnedAt();
        $entity->initClaimDate();
        $this->save($entity, true);
        return $entity;
    }

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

        // TODO: finish function
//        $attack = $item->getEffects()
//
//        $shield = $country->getShield();
//        if ($shield === 0) {
//
//        }
//
//        $this->userService->removeItemById($user, $item->getId(), true);
        return $country;
    }
    //TODO: restore shield by percentage
    //TODO: add shield
    //TODO: heal

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

    public function claim(Country $entity): ClaimRewards | null {

        $lastClaim = $entity->getClaimDate()->getTimestamp();
        $now = time();

        if ((($now - $lastClaim) / 3600 % 24) < 24 ) {
            return null;
        }

        $country = $this->calculatePrice($entity);
        $rewards = new ClaimRewards();

        $coins = round($country->getPrice() / 100);

        // TODO : Rewards item
//        $rewardItems = [];

        $rewards->setCoins($coins);

        $country->setClaimDate(new \DateTimeImmutable());
        $this->save($country);
        return $rewards;
    }

    public function claimAllByUser(User $user): ClaimRewards {
        $rewards = new ClaimRewards();

        $coins = 0;
        $rewardItems = [];

        foreach ($user->getCountries()->toArray() as $country) {
            $reward = $this->claim($country);
            $coins += $reward->getCoins();
            array_push($rewardItems, ...$reward->getItems());
        }

        $this->countryRepository->flush();

        return $rewards->setCoins($coins)->setItems($rewardItems);
    }

}
