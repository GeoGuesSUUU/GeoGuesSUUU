<?php

namespace App\Controller\API;

use App\Entity\ItemType;
use App\Entity\StoreItem;
use App\Entity\User;
use App\Exception\ItemTypeNotFoundApiException;
use App\Exception\StoreItemNotFoundApiException;
use App\Exception\StoreItemNotValidApiException;
use App\Repository\ItemTypeRepository;
use App\Repository\StoreItemRepository;
use App\Service\StoreService;
use App\Utils\ApiResponse;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OAA\Tag(name: 'Store')]
#[Security(name: 'Bearer')]
#[Route('/api/store-item')]
class StoreApiController extends AbstractController
{

    /**
     * Get all StoreItems
     * @OA\Response(
     *     response=200,
     *     description="Return all storeItems",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=StoreItem::class, groups={"item_anti_cr", "store_api_response", "effect_anti_cr"}))
     *     )
     * )
     * @param StoreService $storeService
     * @return Response
     */
    #[Route('/', name: 'app_store_item_api_all', methods: ['GET'], format: 'application/json')]
    public function all(StoreService $storeService): Response
    {
        $storeItems = $storeService->getAll();
        return $this->json(ApiResponse::get($storeItems),
            200,
            [],
            ['groups' => ['item_anti_cr', 'store_api_response', 'effect_anti_cr']]
        );
    }

    /**
     * Get storeItem by ID
     * @OA\Response(
     *     response=200,
     *     description="Return storeItem by Id",
     *     @Model(type=StoreItem::class, groups={"item_anti_cr", "store_api_response", "effect_anti_cr"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="StoreItem not found"
     * )
     * @param int $id
     * @param StoreService $storeService
     * @return Response
     */
    #[Route('/{id}', name: 'app_store_item_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, StoreService $storeService): Response
    {
        $storeItem = $storeService->getById($id);
        return $this->json(ApiResponse::get($storeItem),
            200,
            [],
            ['groups' => ['item_anti_cr', 'store_api_response', 'effect_anti_cr']]
        );
    }

    /**
     * Create new storeItem (Only Admin)
     * @OA\RequestBody(@Model(type=StoreItem::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new storeItem",
     *     @Model(type=StoreItem::class, groups={"item_anti_cr", "store_api_response", "effect_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param StoreItemRepository $storeItemRepository
     * @param ItemTypeRepository $itemTypeRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_store_item_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request             $request,
        SerializerInterface $serializer,
        StoreItemRepository     $storeItemRepository,
        StoreService $storeService,
        ItemTypeRepository $itemTypeRepository,
        ValidatorInterface  $validator
    ): Response
    {
        /** @var StoreItem $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            StoreItem::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['id']]
        );
        if ($itemId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['item'] ?? null) {
            /** @var ItemType $item */
            $item = $itemTypeRepository->findOneBy(["id" => $itemId]);

            if (!is_null($item)) $body->setItem($item);
            else throw new ItemTypeNotFoundApiException();
        } else throw new StoreItemNotValidApiException("The item field is required");

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new StoreItemNotValidApiException($errors->get(0)->getMessage());
        }

        $storeItemSaved = $storeService->save($body, true);
        $storeItemSaved = $storeService->calculateItemPrice($storeItemSaved);

        return $this->json(ApiResponse::get($storeItemSaved),
            200,
            [],
            ['groups' => ['item_anti_cr', 'store_api_response', 'effect_anti_cr']]
        );
    }

    /**
     * Edit storeItem by ID (Only Admin)
     * @OA\RequestBody(@Model(type=StoreItem::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited storeItem",
     *     @Model(type=StoreItem::class, groups={"item_anti_cr", "store_api_response", "effect_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param StoreService $storeService
     * @param ItemTypeRepository $itemTypeRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_store_item_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request             $request,
        int                 $id,
        SerializerInterface $serializer,
        StoreService $storeService,
        ItemTypeRepository $itemTypeRepository,
        ValidatorInterface  $validator
    ): Response
    {
        $storeItem = $storeService->getById($id);

        /** @var StoreItem $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            StoreItem::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $storeItem,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['id']
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        if ($itemId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['item'] ?? null) {
            /** @var ItemType $item */
            $item = $itemTypeRepository->findOneBy(["id" => $itemId]);

            if (!is_null($item)) $body->setItem($item);
            else throw new ItemTypeNotFoundApiException();
        }

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new StoreItemNotValidApiException($errors->get(0)->getMessage());
        }

        $storeItemUpdated = $storeService->save($body, true);
        $storeItemUpdated = $storeService->calculateItemPrice($storeItemUpdated);

        return $this->json(
            ApiResponse::get($storeItemUpdated),
            200,
            [],
            ['groups' => ['item_anti_cr', 'store_api_response', 'effect_anti_cr']]
        );
    }

    /**
     * Delete storeItem by ID (Only Admin)
     * @OA\Response(
     *     response=204,
     *     description="No Content"
     * )
     * @OA\Response(
     *     response=404,
     *     description="StoreItem not found"
     * )
     * @param int $id
     * @param StoreItemRepository $storeItemRepository
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_store_item_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, StoreItemRepository $storeItemRepository): Response
    {
        $storeItem = $storeItemRepository->findOneBy(["id" => $id]);
        if ($storeItem === null) {
            throw new StoreItemNotFoundApiException();
        }

        $storeItemRepository->remove($storeItem, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Buy storeItem by ID
     * @OA\Response(
     *     response=200,
     *     description="Return User",
     *     @Model(type=User::class, groups={"country_anti_cr", "score_anti_cr", "user_api_response", "inventory_anti_cr", "item_anti_cr", "user_private", "user_details", "effect_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param int $id
     * @param StoreService $storeService
     * @param Request $request
     * @return Response
     */
    #[Route('/{id}/buy', name: 'app_store_api_buy', methods: ['POST'], format: 'application/json')]
    public function buy(int $id, StoreService $storeService, Request $request): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        /** @var int $quantity */
        $quantity = $request->get('quantity', 1);

        $user = $storeService->buy($user, $id, $quantity);

        return $this->json(ApiResponse::get($user),
            200,
            [],
            ['groups' => [
                'country_anti_cr', 'score_anti_cr', 'user_api_response', 'inventory_anti_cr', 'item_anti_cr', 'user_private', 'user_details', 'effect_anti_cr'
            ]]
        );
    }
}
