<?php

namespace App\Controller;

use App\Entity\Game;
use App\Exception\GameNotFoundApiException;
use App\Exception\GameNotValidApiException;
use App\Repository\GameRepository;
use App\Utils\ApiResponse;
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

define('GAME_IGNORE_FILED', ['id', '$levels']);

#[OAA\Tag(name: 'Game')]
#[Security(name: 'Bearer')]
#[Route('/api/game')]
class GameApiController extends AbstractController
{

    /**
     * Get all Games
     * @OA\Response(
     *     response=200,
     *     description="Return all games",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Game::class, groups={"level_anti_cr", "game_api_response"}))
     *     )
     * )
     * @param GameRepository $gameRepository
     * @return Response
     */
    #[Route('/', name: 'app_game_api_all', methods: ['GET'], format: 'application/json')]
    public function all(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findAll();
        return $this->json(ApiResponse::get($games),
            200,
            [],
            ['groups' => ['level_anti_cr', 'game_api_response']]
        );
    }

    /**
     * Get game by ID
     * @OA\Response(
     *     response=200,
     *     description="Return game by Id",
     *     @Model(type=Game::class, groups={"level_anti_cr", "game_api_response"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="Game not found"
     * )
     * @param int $id
     * @param GameRepository $gameRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_game_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, GameRepository $gameRepository): Response
    {
        $game = $gameRepository->findOneBy(["id" => $id]);
        if ($game === null) {
            throw new GameNotFoundApiException();
        }
        return $this->json(ApiResponse::get($game),
            200,
            [],
            ['groups' => ['level_anti_cr', 'game_api_response']]
        );
    }

    /**
     * Create new game (Only Admin)
     * @OA\RequestBody(@Model(type=Game::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new game",
     *     @Model(type=Game::class, groups={"level_anti_cr", "game_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param GameRepository $gameRepository
     * @param ValidatorInterface $validator
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_game_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request $request,
        SerializerInterface $serializer,
        GameRepository $gameRepository,
        ValidatorInterface $validator
    ): Response
    {
        /** @var Game $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Game::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => GAME_IGNORE_FILED]
        );

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new GameNotValidApiException($errors->get(0)->getMessage());
        }

        $gameRepository->save($body, true);
        return $this->json(ApiResponse::get($body),
            200,
            [],
            ['groups' => ['level_anti_cr', 'game_api_response']]
        );
    }

    /**
     * Edit game by ID (Only Admin)
     * @OA\RequestBody(@Model(type=Game::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited game",
     *     @Model(type=Game::class, groups={"level_anti_cr", "game_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param GameRepository $gameRepository
     * @param ValidatorInterface $validator
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_game_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request $request,
        int $id,
        SerializerInterface $serializer,
        GameRepository $gameRepository,
        ValidatorInterface $validator
    ): Response
    {
        $game = $gameRepository->findOneBy(["id" => $id]);
        if ($game === null) {
            throw new GameNotFoundApiException();
        }

        /** @var Game $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Game::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $game,
                AbstractNormalizer::IGNORED_ATTRIBUTES => GAME_IGNORE_FILED
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new GameNotValidApiException($errors->get(0)->getMessage());
        }
        $gameRepository->save($game, true);
        $gameUpdated = $gameRepository->findOneBy([
            'id' => $game->getId()
        ]);

        return $this->json(
            ApiResponse::get($gameUpdated),
            200,
            [],
            ['groups' => ['level_anti_cr', 'game_api_response']]
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
     *     description="Game not found"
     * )
     * @param int $id
     * @param GameRepository $gameRepository
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_game_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, GameRepository $gameRepository): Response
    {
        $game = $gameRepository->findOneBy(["id" => $id]);
        if ($game === null) {
            throw new GameNotFoundApiException();
        }

        $gameRepository->remove($game, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
