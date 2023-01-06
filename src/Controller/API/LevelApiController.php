<?php

namespace App\Controller\API;

use App\Entity\Game;
use App\Entity\Level;
use App\Exception\GameNotFoundApiException;
use App\Exception\LevelNotFoundApiException;
use App\Exception\LevelNotValidApiException;
use App\Repository\GameRepository;
use App\Repository\LevelRepository;
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

define('LEVEL_IGNORE_FILED', ['id', '$levels']);

#[OAA\Tag(name: 'Level')]
#[Security(name: 'Bearer')]
#[Route('/api/level')]
class LevelApiController extends AbstractController
{

    /**
     * Get all Levels
     * @OA\Response(
     *     response=200,
     *     description="Return all levels",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Level::class, groups={"score_anti_cr", "game_anti_cr", "level_api_response"}))
     *     )
     * )
     * @param LevelRepository $levelRepository
     * @return Response
     */
    #[Route('/', name: 'app_level_api_all', methods: ['GET'], format: 'application/json')]
    public function all(LevelRepository $levelRepository): Response
    {
        $levels = $levelRepository->findAll();
        return $this->json(ApiResponse::get($levels),
            200,
            [],
            ['groups' => ['score_anti_cr', 'game_anti_cr', 'level_api_response']]
        );
    }

    /**
     * Get level by ID
     * @OA\Response(
     *     response=200,
     *     description="Return level by Id",
     *     @Model(type=Level::class, groups={"score_anti_cr", "game_anti_cr", "level_api_response"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="Level not found"
     * )
     * @param int $id
     * @param LevelRepository $levelRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_level_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, LevelRepository $levelRepository): Response
    {
        $level = $levelRepository->findOneBy(["id" => $id]);
        if ($level === null) {
            throw new LevelNotFoundApiException();
        }
        return $this->json(ApiResponse::get($level),
            200,
            [],
            ['groups' => ['score_anti_cr', 'game_anti_cr', 'level_api_response']]
        );
    }

    /**
     * Create new level (Only Admin)
     * @OA\RequestBody(@Model(type=Level::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new level",
     *     @Model(type=Level::class, groups={"score_anti_cr", "game_anti_cr", "level_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param LevelRepository $levelRepository
     * @param GameRepository $gameRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_level_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request             $request,
        SerializerInterface $serializer,
        LevelRepository     $levelRepository,
        GameRepository      $gameRepository,
        ValidatorInterface  $validator
    ): Response
    {
        /** @var Level $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Level::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => LEVEL_IGNORE_FILED]
        );
        if ($gameId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['game'] ?? null) {
            /** @var Game $game */
            $game = $gameRepository->findOneBy(["id" => $gameId]);

            if (!is_null($game)) $body->setGame($game);
            else throw new GameNotFoundApiException();
        } else throw new LevelNotValidApiException("The game field is required");

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new LevelNotValidApiException($errors->get(0)->getMessage());
        }

        $levelRepository->save($body, true);
        return $this->json(ApiResponse::get($body),
            200,
            [],
            ['groups' => ['score_anti_cr', 'game_anti_cr', 'level_api_response']]
        );
    }

    /**
     * Edit level by ID (Only Admin)
     * @OA\RequestBody(@Model(type=Level::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited level",
     *     @Model(type=Level::class, groups={"score_anti_cr", "game_anti_cr", "level_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param LevelRepository $levelRepository
     * @param GameRepository $gameRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_level_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request             $request,
        int                 $id,
        SerializerInterface $serializer,
        LevelRepository     $levelRepository,
        GameRepository      $gameRepository,
        ValidatorInterface  $validator
    ): Response
    {
        $level = $levelRepository->findOneBy(["id" => $id]);
        if ($level === null) {
            throw new LevelNotFoundApiException();
        }

        /** @var Level $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Level::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $level,
                AbstractNormalizer::IGNORED_ATTRIBUTES => LEVEL_IGNORE_FILED
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        if ($gameId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['game'] ?? null) {
            /** @var Game $game */
            $game = $gameRepository->findOneBy(["id" => $gameId]);

            if (!is_null($game)) $body->setGame($game);
        }
        if (is_null($body->getGame())) throw new LevelNotValidApiException("The game field is required");

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new LevelNotValidApiException($errors->get(0)->getMessage());
        }
        $levelRepository->save($level, true);
        $levelUpdated = $levelRepository->findOneBy([
            'id' => $level->getId()
        ]);

        return $this->json(
            ApiResponse::get($levelUpdated),
            200,
            [],
            ['groups' => ['score_anti_cr', 'game_anti_cr', 'level_api_response']]
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
     *     description="Level not found"
     * )
     * @param int $id
     * @param LevelRepository $levelRepository
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_level_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, LevelRepository $levelRepository): Response
    {
        $level = $levelRepository->findOneBy(["id" => $id]);
        if ($level === null) {
            throw new LevelNotFoundApiException();
        }

        $levelRepository->remove($level, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
