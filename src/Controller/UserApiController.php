<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\UserNotFoundApiException;
use App\Exception\UserNotValidApiException;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

define('IGNORE_FILED', ['id', 'roles', 'isVerified', 'scores', 'userItems', 'countries']);

#[OAA\Tag(name: 'User')]
#[Security(name: 'Bearer')]
#[Route('/api/user')]
class UserApiController extends AbstractController
{

    /**
     * Get all Users
     * @OA\Response(
     *     response=200,
     *     description="Return all users",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"country_anti_cr", "user_api_response"}))
     *     )
     * )
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/', name: 'app_user_api_all', methods: ['GET'], format: 'application/json')]
    public function all(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json(ApiResponse::get($users),
            200,
            [],
            ['groups' => ['country_anti_cr', 'user_api_response']]
        );
    }

    /**
     * Get user by ID
     * @OA\Response(
     *     response=200,
     *     description="Return user by Id",
     *     @Model(type=User::class, groups={"country_anti_cr", "user_api_response", "user_details"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="User not found"
     * )
     * @param int $id
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_user_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(["id" => $id]);
        if ($user === null) {
            throw new UserNotFoundApiException();
        }
        return $this->json(ApiResponse::get($user),
            200,
            [],
            ['groups' => ['country_anti_cr', 'user_api_response', 'user_details']]
        );
    }

    /**
     * Create new user
     * @OA\RequestBody(@Model(type=User::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new user",
     *     @Model(type=User::class, groups={"country_anti_cr", "user_api_response", "user_details"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    #[Route('/', name: 'app_user_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request $request,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => IGNORE_FILED]
        );

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new UserNotValidApiException($errors->get(0)->getMessage());
        }

        $body->encryptPassword($passwordHasher);
        $userRepository->save($body, true);
        $userSaved = $userRepository->findOneBy([
            'email' => $body->getEmail()
        ]);
        $userSaved?->unsetPassword();

        return $this->json(ApiResponse::get($userSaved),
            200,
            [],
            ['groups' => ['country_anti_cr', 'user_api_response', 'user_details']]
        );
    }

    /**
     * Edit user by ID
     * @OA\RequestBody(@Model(type=User::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited user",
     *     @Model(type=User::class, groups={"country_anti_cr", "user_api_response", "user_details"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     * @throws JsonException
     */
    #[Route('/{id}', name: 'app_user_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request $request,
        int $id,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneBy(["id" => $id]);
        if ($user === null) {
            throw new UserNotFoundApiException();
        }

        /** @var User $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $user,
                AbstractNormalizer::IGNORED_ATTRIBUTES => IGNORE_FILED
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new UserNotValidApiException($errors->get(0)->getMessage());
        }
        if (isset(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['password'])) {
            $body->encryptPassword($passwordHasher);
        }

        $userRepository->save($user, true);
        $userUpdated = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);
        $userUpdated?->unsetPassword();

        return $this->json(ApiResponse::get($userUpdated),
            200,
            [],
            ['groups' => ['country_anti_cr', 'user_api_response', 'user_details']]
        );
    }

    /**
     * Delete user by ID
     * @OA\Response(
     *     response=204,
     *     description="No Content"
     * )
     * @OA\Response(
     *     response=404,
     *     description="User not found"
     * )
     * @param int $id
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_user_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(["id" => $id]);
        if ($user === null) {
            throw new UserNotFoundApiException();
        }

        $userRepository->remove($user, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
