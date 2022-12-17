<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\UserNotFoundApiException;
use App\Exception\UserNotValidApiException;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

define('IGNORE_FILED', ['id', 'roles', 'isVerified', 'scores', 'userItems', 'countries']);

#[Route('/api/user')]
class UserApiController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_user_api_all', methods: ['GET'])]
    public function all(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json(ApiResponse::get($users));
    }


    /**
     * @throws Exception
     */
    #[Route('/{id}', name: 'app_user_api_index', methods: ['GET'])]
    public function one(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(["id" => $id]);
        if ($user === null) {
            throw new UserNotFoundApiException();
        }
        return $this->json(ApiResponse::get($user));
    }

    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_user_api_new', methods: ['POST'])]
    public function new(
        Request $request,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ValidatorInterface $validator
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

        $userRepository->save($body, true);
        $userSaved = $userRepository->findOneBy([
            'email' => $body->getEmail()
        ]);

        return $this->json(ApiResponse::get($userSaved));
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}', name: 'app_user_api_edit', methods: ['PUT', 'PATCH'])]
    public function edit(
        Request $request,
        int $id,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ValidatorInterface $validator
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
        $userRepository->save($user, true);
        $userUpdated = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        return $this->json(ApiResponse::get($userUpdated));
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}', name: 'app_user_api_delete', methods: ['DELETE'])]
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
