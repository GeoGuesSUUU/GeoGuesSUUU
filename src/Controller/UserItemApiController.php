<?php

namespace App\Controller;

use App\Entity\ItemType;
use App\Entity\UserItem;
use App\Exception\ItemTypeNotFoundApiException;
use App\Exception\UserNotFoundApiException;
use App\Repository\ItemTypeRepository;
use App\Repository\UserItemRepository;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OAA\Tag(name: 'User')]
#[Security(name: 'Bearer')]
#[Route('/api/user/inventory')]
class UserItemApiController extends AbstractController
{

    /**
     * Add Item in user inventory
     * @OA\Parameter(name="quantity", in="query")
     * @OA\Response(
     *     response=200,
     *     description="Return new itemType",
     *     @Model(type=ItemType::class, groups={"user_anti_cr", "item_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param int $userId
     * @param int $itemId
     * @param UserItemRepository $userItemRepository
     * @param UserRepository $userRepository
     * @param ItemTypeRepository $itemTypeRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/{userId}/add/{itemId}', name: 'app_user_item_api_add', methods: ['POST'], format: 'application/json')]
    public function add(
        int $userId,
        int $itemId,
        UserItemRepository $userItemRepository,
        UserRepository $userRepository,
        ItemTypeRepository $itemTypeRepository,
        Request $request
    ): Response
    {

        $link = $userItemRepository->findOneBy(["user" => $userId, "itemType" => $itemId]);
        if ($link) {
            $link->setQuantity($link->getQuantity() + 1);
            $userItemRepository->save($link, true);
            return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
        }

        $user = $userRepository->findOneBy(["id" => $userId]);
        if ($user === null) {
            throw new UserNotFoundApiException();
        }
        $item = $itemTypeRepository->findOneBy(["id" => $itemId]);
        if ($item === null) {
            throw new ItemTypeNotFoundApiException();
        }

        /** @var int $quantity */
        $quantity = $request->get('quantity', 1);

        $itemLink = new UserItem();
        $itemLink->setQuantity($quantity);
        $itemLink->setUser($user);
        $itemLink->setItemType($item);

        $userItemRepository->save($itemLink, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Delete user item inventory (all stack)
     * @OA\Response(
     *     response=204,
     *     description="No Content"
     * )
     * @OA\Response(
     *     response=404,
     *     description="ItemType not found"
     * )
     * @param int $userId
     * @param int $itemId
     * @param UserItemRepository $userItemRepository
     * @return Response
     */
    #[Route('/{userId}/remove/{itemId}', name: 'app_user_item_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function remove(int $userId, int $itemId, UserItemRepository $userItemRepository): Response
    {
        $itemLink = $userItemRepository->findOneBy(["user" => $userId, "itemType" => $itemId]);
        if ($itemLink === null) {
            throw new ItemTypeNotFoundApiException();
        }

        $userItemRepository->remove($itemLink, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Subtract user item inventory by ID
     * @OA\Response(
     *     response=204,
     *     description="No Content"
     * )
     * @OA\Response(
     *     response=404,
     *     description="ItemType not found"
     * )
     * @param int $userId
     * @param int $itemId
     * @param int $num
     * @param UserItemRepository $userItemRepository
     * @return Response
     */
    #[Route(
        '/{userId}/remove/{itemId}/quantity/{num}',
        name: 'app_item_api_remove',
        methods: ['DELETE'],
        format: 'application/json')
    ]
    public function removeQuantity(int $userId, int $itemId, int $num, UserItemRepository $userItemRepository): Response
    {
        $itemLink = $userItemRepository->findOneBy(["user" => $userId, "itemType" => $itemId]);
        if ($itemLink === null) {
            throw new ItemTypeNotFoundApiException();
        }

        $itemLink->setQuantity($itemLink->getQuantity() - $num);

        if ($itemLink->getQuantity() < 1) {
            $userItemRepository->remove($itemLink, true);
        }
        else {
            $userItemRepository->save($itemLink, true);
        }


        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
