<?php

namespace App\Controller\API;

use App\Entity\ItemType;
use App\Exception\ItemTypeNotFoundApiException;
use App\Exception\ItemTypeNotValidApiException;
use App\Repository\ItemTypeRepository;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;
use Exception;
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

define('ITEM_IGNORE_FILED', ['id', 'userItems', 'countryItems']);

#[OAA\Tag(name: 'Item')]
#[Security(name: 'Bearer')]
#[Route('/api/item')]
class ItemApiController extends AbstractController
{

    /**
     * Get all Items
     * @OA\Response(
     *     response=200,
     *     description="Return all items",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=ItemType::class, groups={"item_api_response"}))
     *     )
     * )
     * @param ItemTypeRepository $itemTypeRepository
     * @return Response
     */
    #[Route('/', name: 'app_item_api_all', methods: ['GET'], format: 'application/json')]
    public function all(ItemTypeRepository $itemTypeRepository): Response
    {
        $countries = $itemTypeRepository->findAll();
        return $this->json(ApiResponse::get($countries),
            200,
            [],
            ['groups' => ['item_api_response']]
        );
    }

    /**
     * Get itemType by ID
     * @OA\Response(
     *     response=200,
     *     description="Return itemType by Id",
     *     @Model(type=ItemType::class, groups={"user_anti_cr", "item_api_response"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="ItemType not found"
     * )
     * @param int $id
     * @param ItemTypeRepository $itemTypeRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_item_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, ItemTypeRepository $itemTypeRepository): Response
    {
        $itemType = $itemTypeRepository->findOneBy(["id" => $id]);
        if ($itemType === null) {
            throw new ItemTypeNotFoundApiException();
        }
        return $this->json(ApiResponse::get($itemType),
            200,
            [],
            ['groups' => ['user_anti_cr', 'item_api_response']]
        );
    }

    /**
     * Create new itemType (Only Admin)
     * @OA\RequestBody(@Model(type=ItemType::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new itemType",
     *     @Model(type=ItemType::class, groups={"user_anti_cr", "item_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ItemTypeRepository $itemTypeRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws Exception
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_item_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request             $request,
        SerializerInterface $serializer,
        ItemTypeRepository  $itemTypeRepository,
        ValidatorInterface  $validator
    ): Response
    {
        /** @var ItemType $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            ItemType::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ITEM_IGNORE_FILED]
        );

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new ItemTypeNotValidApiException($errors->get(0)->getMessage());
        }

        $itemTypeRepository->save($body, true);
        return $this->json(ApiResponse::get($body),
            200,
            [],
            ['groups' => ['user_anti_cr', 'item_api_response']]
        );
    }

    /**
     * Edit itemType by ID (Only Admin)
     * @OA\RequestBody(@Model(type=ItemType::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited itemType",
     *     @Model(type=ItemType::class, groups={"user_anti_cr", "item_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param ItemTypeRepository $itemTypeRepository
     * @param ValidatorInterface $validator
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_item_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request             $request,
        int                 $id,
        SerializerInterface $serializer,
        ItemTypeRepository  $itemTypeRepository,
        ValidatorInterface  $validator
    ): Response
    {
        $itemType = $itemTypeRepository->findOneBy(["id" => $id]);
        if ($itemType === null) {
            throw new ItemTypeNotFoundApiException();
        }

        /** @var ItemType $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            ItemType::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $itemType,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ITEM_IGNORE_FILED
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new ItemTypeNotValidApiException($errors->get(0)->getMessage());
        }

        $itemTypeRepository->save($itemType, true);
        $itemTypeUpdated = $itemTypeRepository->findOneBy([
            'id' => $itemType->getId()
        ]);

        return $this->json(
            ApiResponse::get($itemTypeUpdated),
            200,
            [],
            ['groups' => ['user_anti_cr', 'item_api_response']]
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
     *     description="ItemType not found"
     * )
     * @param int $id
     * @param ItemTypeRepository $itemTypeRepository
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_item_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, ItemTypeRepository $itemTypeRepository): Response
    {
        $itemType = $itemTypeRepository->findOneBy(["id" => $id]);
        if ($itemType === null) {
            throw new ItemTypeNotFoundApiException();
        }

        $itemTypeRepository->remove($itemType, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
