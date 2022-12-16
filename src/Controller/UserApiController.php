<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserEditDTO;
use App\Entity\UserSaveDTO;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

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
    public function one(User $user, UserRepository $userRepository): Response
    {
        if ($user) {
            return $this->json(ApiResponse::get($user));
        }
        return $this->json(ApiResponse::get(null, Response::HTTP_NOT_FOUND));
    }

    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_user_api_new', methods: ['POST'])]
    public function new(Request $request, SerializerInterface $serializer, UserRepository $userRepository): Response
    {
        /** @var UserSaveDTO $body */
        $body = $serializer->deserialize($request->getContent(), UserSaveDTO::class, 'json');

        if (
            !$body->verify()
        ) {
            return $this->json(ApiResponse::get(null, Response::HTTP_BAD_REQUEST));
        }

        $userRepository->save($body->toUser(), true);
        $userSaved = $userRepository->findOneBy([
            'email' => $body->getEmail()
        ]);

        return $this->json(ApiResponse::get($userSaved));
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}', name: 'app_user_api_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, User $user, SerializerInterface $serializer, UserRepository $userRepository): Response
    {
        /** @var UserEditDTO $body */
        $body = $serializer->deserialize($request->getContent(), UserEditDTO::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

        if (
            !$body->verify()
        ) {
            return $this->json(ApiResponse::get(null, Response::HTTP_BAD_REQUEST));
        }

        $userEdited = $body->edit($user);
        $userRepository->save($userEdited, true);
        $userUpdated = $userRepository->findOneBy([
            'id' => $userEdited->getId()
        ]);

        return $this->json(ApiResponse::get($userUpdated));
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}', name: 'app_user_api_delete', methods: ['DELETE'])]
    public function delete(User $user, UserRepository $userRepository): Response
    {
        $userRepository->remove($user, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
