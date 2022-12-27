<?php

namespace App\Service;

use App\Entity\Country;
use App\Entity\CountryItem;
use App\Entity\ItemType;
use App\Entity\User;
use App\Entity\UserItem;
use App\Exception\ItemTypeNotFoundApiException;
use App\Repository\CountryItemRepository;
use App\Repository\CountryRepository;
use App\Repository\UserItemRepository;
use App\Repository\UserRepository;
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

    public function save(Country $entity, bool $flush = false): Country
    {
        $this->countryRepository->save($entity, $flush);
        return $entity;
    }

    public function deleteById(int $id): void
    {
        $entity = $this->getById($id);
        if (!is_null($entity)) {
            $this->countryRepository->remove($entity);
        }
    }

    /**
     * @param Country $entity
     * @param User $user
     * @return Country | null
     * @throws Exception
     */
    public function buy(Country $entity, User $user): Country | null
    {
        if ($user->getCoins() < $entity->getPrice()) {
            return null;
        }
        $user->setCoins($user->getCoins() - $entity->getPrice());

        $this->userRepository->save($user);

        $entity->setUser($user);
        $entity->setOwnedAt(new \DateTimeImmutable());
        $entity->setClaimDate(new \DateTimeImmutable());
        $this->save($entity, true);
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

        $this->countryItemRepository->removeByItemType(ItemTypeType::TYPE_SUPPORT->value);

        $entity->initOwnedAt();
        $entity->initClaimDate();
        $this->save($entity);
        return $entity;
    }

    /**
     * @param Country $country
     * @param User $user
     * @param ItemType $item
     * @return Country
     */
    public function addEquipment(Country $country, User $user, ItemType $item): Country
    {
        $itemInventory = $this->userService->findItemById($user, $item->getId());
        if (is_null($itemInventory)) {
            throw new ItemTypeNotFoundApiException();
        }

        $link = $this->countryItemRepository->findOneBy([
            'country' => $country->getId(),
            'itemType' => $item->getId()
        ]);

        if (is_null($link)) {
            $link = new CountryItem();
            $link->setCountry($country);
            $link->setItemType($item);
        }

        $link->setQuantity($link->getQuantity() + 1);

        $this->countryItemRepository->save($link);
        $this->userService->removeItemById($user, $item->getId());

        return $country;
    }

    public function calculateExtraData(Country $entity): Country
    {


        return $entity;
    }

//    public function attack(Country $entity, User $user, ItemType $item): Country
//    {
//        $link = new CountryItem();
//        $link->
//        $this->countryItemRepository
//    }
}
