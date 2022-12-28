<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\User;
use App\Exception\CountryNotFoundApiException;
use App\Exception\CountryNotValidApiException;
use App\Exception\ItemTypeNotFoundApiException;
use App\Repository\CountryRepository;
use App\Repository\ItemTypeRepository;
use App\Repository\UserRepository;
use App\Service\CountryService;
use App\Service\UserService;
use App\Utils\ApiResponse;
use Exception;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

define('COUNTRY_IGNORE_FILED', ['id', 'countryItems', 'user']);

#[OAA\Tag(name: 'Country')]
#[Security(name: 'Bearer')]
#[Route('/api/country')]
class CountryApiController extends AbstractController
{

    /**
     * Get all Countries
     * @OA\Response(
     *     response=200,
     *     description="Return all countries",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Country::class, groups={"user_anti_cr", "country_api_response", "country_item_anti_cr", "item_anti_cr"}))
     *     )
     * )
     * @param CountryRepository $countryRepository
     * @return Response
     */
    #[Route('/', name: 'app_country_api_all', methods: ['GET'], format: 'application/json')]
    public function all(CountryRepository $countryRepository, CountryService $countryService): Response
    {
        $countries = $countryRepository->findAll();
        foreach ($countries as &$country) {
            $country = $countryService->calculatePrice($country);
        }
        return $this->json(ApiResponse::get($countries),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr']]
        );
    }

    /**
     * Get country by ID
     * @OA\Response(
     *     response=200,
     *     description="Return country by Id",
     *     @Model(type=Country::class, groups={"user_anti_cr", "country_api_response", "country_item_anti_cr", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="Country not found"
     * )
     * @param int $id
     * @param CountryRepository $countryRepository
     * @param CountryService $countryService
     * @return Response
     */
    #[Route('/{id}', name: 'app_country_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, CountryRepository $countryRepository, CountryService $countryService): Response
    {
        $country = $countryRepository->findOneBy(["id" => $id]);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }
        $country = $countryService->calculatePrice($country);
        return $this->json(ApiResponse::get($country),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr']]
        );
    }

    /**
     * Create new country (Only Admin)
     * @OA\RequestBody(@Model(type=Country::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new country",
     *     @Model(type=Country::class, groups={"user_anti_cr", "country_api_response", "country_item_anti_cr", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CountryRepository $countryRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws Exception
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_country_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request $request,
        SerializerInterface $serializer,
        CountryRepository $countryRepository,
        CountryService $countryService,
        ValidatorInterface $validator
    ): Response
    {
        /** @var Country $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Country::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => COUNTRY_IGNORE_FILED]
        );

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new CountryNotValidApiException($errors->get(0)->getMessage());
        }

        $country = $countryService->create($body);
        $country = $countryService->calculatePrice($country);

        return $this->json(ApiResponse::get($country),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr']]
        );
    }

    /**
     * Edit country by ID (Only Admin)
     * @OA\RequestBody(@Model(type=Country::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited country",
     *     @Model(type=Country::class, groups={"user_anti_cr", "country_api_response", "country_item_anti_cr", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param CountryRepository $countryRepository
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_country_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request $request,
        int $id,
        SerializerInterface $serializer,
        CountryRepository $countryRepository,
        CountryService $countryService,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): Response
    {
        $user = null;
        $country = $countryRepository->findOneBy(["id" => $id]);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }

        /** @var Country $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Country::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $country,
                AbstractNormalizer::IGNORED_ATTRIBUTES => COUNTRY_IGNORE_FILED
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        // Get dependency Entity
        if ($userId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['user'] ?? null) {
            /** @var User $user */
            $user = $userRepository->findOneBy(["id" => $userId]);

            if (!is_null($user)) $body->setUser($user);
        }

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new CountryNotValidApiException($errors->get(0)->getMessage());
        }

        $country = $countryService->save($body, true);
        $country = $countryService->calculatePrice($country);

        return $this->json(
            ApiResponse::get($country),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr']]
        );
    }

    /**
     * Delete user by ID (Only Admin)
     * @OA\Response(
     *     response=204,
     *     description="No Content"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Country not found"
     * )
     * @param int $id
     * @param CountryService $countryService
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_country_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, CountryService $countryService): Response
    {
        $countryService->deleteById($id);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Buy country by ID
     * @OA\Response(
     *     response=200,
     *     description="Return country",
     *     @Model(type=Country::class, groups={"user_anti_cr", "country_api_response", "country_item_anti_cr", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param int $id
     * @param CountryRepository $countryRepository
     * @param CountryService $countryService
     * @return Response
     * @throws Exception
     */
    #[Route('/{id}/buy', name: 'app_country_api_buy', methods: ['POST'], format: 'application/json')]
    public function buy(int $id, CountryRepository $countryRepository, CountryService $countryService): Response
    {
        $country = $countryRepository->findOneBy(["id" => $id]);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $country = $countryService->buy($country, $user);
        $country = $countryService->calculatePrice($country);

        return $this->json(ApiResponse::get($country),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr']]
        );
    }

    /**
     * Claim country by ID
     * @OA\Response(
     *     response=200,
     *     description="Return reward",
     *     @Model(type=Country::class, groups={"response", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param int $id
     * @param UserRepository $userRepository
     * @param CountryService $countryService
     * @param UserService $userService
     * @return Response
     */
    #[Route('/{id}/claim', name: 'app_country_api_claim', methods: ['POST'], format: 'application/json')]
    public function claimById(
        int $id,
        UserRepository $userRepository,
        CountryService $countryService,
        UserService $userService,
    ): Response
    {
        $country = $countryService->getById($id);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if (is_null($country->getUser()) || $country->getUser()->getId() !== $user->getId()) {
            throw new CountryNotValidApiException();
        }

        $reward = $countryService->claim($country);
        if (is_null($reward)) {
            throw new CountryNotValidApiException("You must wait 24 hours before claiming");
        }

        $user->setCoins($user->getCoins() + $reward->getCoins());
        foreach ($reward->getItems() as $item) {
            $userService->addItemInInventory($user, $item->getItem(), $item->getQuantity());
        }

        $userRepository->save($user, true);

        return $this->json(ApiResponse::get($reward),
            200,
            [],
            ['groups' => ['response', 'item_anti_cr']]
        );
    }

    /**
     * Claim All your countries
     * @OA\Response(
     *     response=200,
     *     description="Return reward",
     *     @Model(type=Country::class, groups={"response", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param UserRepository $userRepository
     * @param CountryService $countryService
     * @param UserService $userService
     * @return Response
     */
    #[Route('/claim', name: 'app_country_api_claim_all', methods: ['POST'], format: 'application/json')]
    public function claimAll(
        UserRepository $userRepository,
        CountryService $countryService,
        UserService $userService,
    ): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        $reward = $countryService->claimAllByUser($user);

        $user->setCoins($user->getCoins() + $reward->getCoins());
        foreach ($reward->getItems() as $item) {
            $userService->addItemInInventory($user, $item->getItem(), $item->getQuantity());
        }

        $userRepository->save($user, true);

        return $this->json(ApiResponse::get($reward),
            200,
            [],
            ['groups' => ['response', 'item_anti_cr']]
        );
    }

    /**
     * Attack Country By ID
     * @OA\Response(
     *     response=200,
     *     description="Return reward",
     *     @Model(type=Country::class, groups={"response", "item_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param int $id
     * @param int $itemId
     * @param ItemTypeRepository $itemTypeRepository
     * @param CountryService $countryService
     * @return Response
     * @throws Exception
     */
    #[Route('/{id}/attack/{itemId}', name: 'app_country_api_attck', methods: ['POST'], format: 'application/json')]
    public function attack(
        int $id,
        int $itemId,
        ItemTypeRepository $itemTypeRepository,
        CountryService $countryService,
    ): Response
    {

        $item = $itemTypeRepository->findOneBy([ 'id' => $itemId ]);
        if ($item === null) {
            throw new ItemTypeNotFoundApiException();
        }

        $country = $countryService->getById($id);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $country = $countryService->attack($country, $user, $item);


        return $this->json(ApiResponse::get($country),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr']]
        );
    }
}
