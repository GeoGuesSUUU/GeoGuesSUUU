<?php

namespace App\Controller\API;

use App\Entity\ItemType;
use App\Entity\User;
use App\Exception\CountryNotFoundApiException;
use App\Exception\ItemTypeNotFoundApiException;
use App\Repository\CountryRepository;
use App\Repository\ItemTypeRepository;
use App\Service\CountryService;
use App\Utils\ApiResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OAA\Tag(name: 'Country')]
#[Security(name: 'Bearer')]
#[Route('/api/country/equipment')]
class CountryItemApiController extends AbstractController
{

    /**
     * Add Item in country inventory
     * @OA\Parameter(name="quantity", in="query")
     * @OA\Response(
     *     response=200,
     *     description="Return updated Country",
     *     @Model(type=ItemType::class, groups={"user_anti_cr", "country_api_response", "country_item_anti_cr", "item_anti_cr", "effect_anti_cr"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param int $countryId
     * @param int $itemId
     * @param CountryRepository $countryRepository
     * @param ItemTypeRepository $itemTypeRepository
     * @param CountryService $countryService
     * @return Response
     */
    #[Route('/{countryId}/add/{itemId}', name: 'app_country_item_api_add', methods: ['POST'], format: 'application/json')]
    public function add(
        int                $countryId,
        int                $itemId,
        CountryRepository  $countryRepository,
        ItemTypeRepository $itemTypeRepository,
        CountryService     $countryService,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $country = $countryRepository->findOneBy(['id' => $countryId]);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }
        $item = $itemTypeRepository->findOneBy(['id' => $itemId]);
        if ($item === null) {
            throw new ItemTypeNotFoundApiException();
        }

        $updatedCountry = $countryService->addItemFromInventory($country, $user, $item);
        $updatedCountry = $countryService->calculatePrice($updatedCountry);

        return $this->json(ApiResponse::get($updatedCountry, Response::HTTP_OK),
            200,
            [],
            ['groups' => [
                'user_anti_cr', 'country_api_response', 'country_item_anti_cr', 'item_anti_cr', 'effect_anti_cr'
            ]]);
    }
}
