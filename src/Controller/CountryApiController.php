<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\User;
use App\Exception\CountryNotFoundApiException;
use App\Exception\CountryNotValidApiException;
use App\Repository\CountryRepository;
use App\Repository\UserRepository;
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
     *        @OA\Items(ref=@Model(type=Country::class, groups={"api_response"}))
     *     )
     * )
     * @param CountryRepository $countryRepository
     * @return Response
     */
    #[Route('/', name: 'app_country_api_all', methods: ['GET'], format: 'application/json')]
    public function all(CountryRepository $countryRepository): Response
    {
        $countries = $countryRepository->findAll();
        return $this->json(ApiResponse::get($countries),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response']]
        );
    }

    /**
     * Get country by ID
     * @OA\Response(
     *     response=200,
     *     description="Return country by Id",
     *     @Model(type=Country::class, groups={"api_response"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="Country not found"
     * )
     * @param int $id
     * @param CountryRepository $countryRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_country_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, CountryRepository $countryRepository): Response
    {
        $country = $countryRepository->findOneBy(["id" => $id]);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }
        return $this->json(ApiResponse::get($country),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response']]
        );
    }

    /**
     * Create new country (Only Admin)
     * @OA\RequestBody(@Model(type=Country::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new country",
     *     @Model(type=Country::class, groups={"api_response"})
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

        $body->initOwnedAt();

        $countryRepository->save($body, true);
        return $this->json(ApiResponse::get($body),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response']]
        );
    }

    /**
     * Edit country by ID (Only Admin)
     * @OA\RequestBody(@Model(type=Country::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited country",
     *     @Model(type=Country::class, groups={"api_response"})
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

        $countryRepository->save($country, true);
        $countryUpdated = $countryRepository->findOneBy([
            'id' => $country->getId()
        ]);

        return $this->json(
            ApiResponse::get($countryUpdated),
            200,
            [],
            ['groups' => ['user_anti_cr', 'country_api_response']]
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
     * @param CountryRepository $countryRepository
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_country_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, CountryRepository $countryRepository): Response
    {
        $country = $countryRepository->findOneBy(["id" => $id]);
        if ($country === null) {
            throw new CountryNotFoundApiException();
        }

        $countryRepository->remove($country, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
