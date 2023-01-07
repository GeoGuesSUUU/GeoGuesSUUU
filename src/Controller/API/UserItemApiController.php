<?php

namespace App\Controller\API;

use App\Entity\ItemType;
use App\Entity\User;
use App\Exception\ItemTypeNotFoundApiException;
use App\Repository\ItemTypeRepository;
use App\Repository\UserItemRepository;
use App\Service\UserService;
use App\Utils\ApiResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;
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
     * @param int $itemId
     * @param UserService $userService
     * @param ItemTypeRepository $itemTypeRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/add/{itemId}', name: 'app_user_item_api_add', methods: ['POST'], format: 'application/json')]
    public function add(
        int                $itemId,
        UserService        $userService,
        ItemTypeRepository $itemTypeRepository,
        Request            $request
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $item = $itemTypeRepository->findOneBy(["id" => $itemId]);
        if ($item === null) {
            throw new ItemTypeNotFoundApiException();
        }

        /** @var int $quantity */
        $quantity = $request->get('quantity', 1);

        $userService->addItemInInventory($user, $item, $quantity, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Delete user item inventory (default deleted all stack)
     * @OA\Parameter(name="quantity", in="query")
     * @OA\Response(
     *     response=204,
     *     description="No Content"
     * )
     * @OA\Response(
     *     response=404,
     *     description="ItemType not found"
     * )
     * @param int $itemId
     * @param UserItemRepository $userItemRepository
     * @param UserService $userService
     * @param Request $request
     * @return Response
     */
    #[Route('/remove/{itemId}', name: 'app_user_item_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function remove(
        int                $itemId,
        UserItemRepository $userItemRepository,
        UserService        $userService,
        Request            $request
    ): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        /** @var int | null $quantity */
        $quantity = $request->get('quantity');

        if (is_null($quantity)) {
            $itemLink = $userItemRepository->findOneBy(["user" => $user->getId(), "itemType" => $itemId]);
            if ($itemLink === null) {
                throw new ItemTypeNotFoundApiException();
            }

            $userItemRepository->remove($itemLink, true);
        } else {
            $userService->removeItemById($user, $itemId, $quantity, true);
        }

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
