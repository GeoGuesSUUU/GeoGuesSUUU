<?php

namespace App\Controller;

use App\Entity\Level;
use App\Entity\Score;
use App\Entity\User;
use App\Exception\LevelNotFoundApiException;
use App\Exception\ScoreNotFoundApiException;
use App\Exception\ScoreNotValidApiException;
use App\Exception\UserNotFoundApiException;
use App\Repository\LevelRepository;
use App\Repository\ScoreRepository;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;
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

define('SCORE_IGNORE_FILED', ['id', 'createdAt']);

#[OAA\Tag(name: 'Score')]
#[Security(name: 'Bearer')]
#[Route('/api/score')]
class ScoreApiController extends AbstractController
{

    /**
     * Get all Scores
     * @OA\Response(
     *     response=200,
     *     description="Return all scores",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Score::class, groups={"user_anti_cr", "level_anti_cr", "score_api_response"}))
     *     )
     * )
     * @param ScoreRepository $scoreRepository
     * @return Response
     */
    #[Route('/', name: 'app_score_api_all', methods: ['GET'], format: 'application/json')]
    public function all(ScoreRepository $scoreRepository): Response
    {
        $scores = $scoreRepository->findAll();
        return $this->json(ApiResponse::get($scores),
            200,
            [],
            ['groups' => ['user_anti_cr', 'level_anti_cr', 'score_api_response']]
        );
    }

    /**
     * Get score by ID
     * @OA\Response(
     *     response=200,
     *     description="Return score by Id",
     *     @Model(type=Score::class, groups={"user_anti_cr", "level_anti_cr", "score_api_response"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="Score not found"
     * )
     * @param int $id
     * @param ScoreRepository $scoreRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_score_api_index', methods: ['GET'], format: 'application/json')]
    public function one(int $id, ScoreRepository $scoreRepository): Response
    {
        $score = $scoreRepository->findOneBy(["id" => $id]);
        if ($score === null) {
            throw new ScoreNotFoundApiException();
        }
        return $this->json(ApiResponse::get($score),
            200,
            [],
            ['groups' => ['user_anti_cr', 'level_anti_cr', 'score_api_response']]
        );
    }

    /**
     * Create new score (Only Admin)
     * @OA\RequestBody(@Model(type=Score::class, groups={"api_new"}))
     * @OA\Response(
     *     response=200,
     *     description="Return new score",
     *     @Model(type=Score::class, groups={"user_anti_cr", "level_anti_cr", "score_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ScoreRepository $scoreRepository
     * @param LevelRepository $levelRepository
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_score_api_new', methods: ['POST'], format: 'application/json')]
    public function new(
        Request $request,
        SerializerInterface $serializer,
        ScoreRepository $scoreRepository,
        LevelRepository $levelRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): Response
    {
        /** @var Score $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Score::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => SCORE_IGNORE_FILED]
        );

        // Get dependency Entity
        if ($levelId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['level'] ?? null) {
            /** @var Level $level */
            $level = $levelRepository->findOneBy(["id" => $levelId]);

            if (!is_null($level)) $body->setLevel($level);
            else throw new LevelNotFoundApiException();
        }
        else throw new ScoreNotValidApiException("The level field is required");
        if ($userId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['user'] ?? null) {
            /** @var User $user */
            $user = $userRepository->findOneBy(["id" => $userId]);

            if (!is_null($user)) $body->setUser($user);
            else throw new UserNotFoundApiException();
        }
        else throw new ScoreNotValidApiException("The user field is required");

        $body->initCreatedAt();

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new ScoreNotValidApiException($errors->get(0)->getMessage());
        }

        $scoreRepository->save($body, true);
        return $this->json(ApiResponse::get($body),
            200,
            [],
            ['groups' => ['user_anti_cr', 'level_anti_cr', 'score_api_response']]
        );
    }

    /**
     * Edit score by ID (Only Admin)
     * @OA\RequestBody(@Model(type=Score::class, groups={"api_edit"}))
     * @OA\Response(
     *     response=200,
     *     description="Return edited score",
     *     @Model(type=Score::class, groups={"user_anti_cr", "level_anti_cr", "score_api_response"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @param Request $request
     * @param int $id
     * @param SerializerInterface $serializer
     * @param ScoreRepository $scoreRepository
     * @param LevelRepository $levelRepository
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @return Response
     * @throws JsonException
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_score_api_edit', methods: ['PUT', 'PATCH'], format: 'application/json')]
    public function edit(
        Request $request,
        int $id,
        SerializerInterface $serializer,
        ScoreRepository $scoreRepository,
        LevelRepository $levelRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): Response
    {
        $score = $scoreRepository->findOneBy(["id" => $id]);
        if ($score === null) {
            throw new ScoreNotFoundApiException();
        }

        /** @var Score $body */
        $body = $serializer->deserialize(
            $request->getContent(),
            Score::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $score,
                AbstractNormalizer::IGNORED_ATTRIBUTES => SCORE_IGNORE_FILED
            ]
        );
        // reset the ID if it has been changed on request
        $body->setId($id);

        // Get dependency Entity
        if ($levelId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['level'] ?? null) {
            /** @var Level $level */
            $level = $levelRepository->findOneBy(["id" => $levelId]);

            if (!is_null($level)) $body->setLevel($level);
        }
        if ($userId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['user'] ?? null) {
            /** @var User $user */
            $user = $userRepository->findOneBy(["id" => $userId]);

            if (!is_null($user)) $body->setUser($user);
        }
        if (is_null($body->getLevel()) || is_null($body->getUser())) throw new ScoreNotValidApiException();

        if ($levelId = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['level'] ?? null) {
            /** @var Level $level */
            $level = $levelRepository->findOneBy(["id" => $levelId]);

            if (!is_null($level)) $body->setLevel($level);
        }
        if (is_null($body->getLevel())) throw new ScoreNotValidApiException("The level field is required");

        $errors = $validator->validate($body);
        if (
            $errors->count() > 0
        ) {
            throw new ScoreNotValidApiException($errors->get(0)->getMessage());
        }
        $scoreRepository->save($score, true);
        $scoreUpdated = $scoreRepository->findOneBy([
            'id' => $score->getId()
        ]);

        return $this->json(
            ApiResponse::get($scoreUpdated),
            200,
            [],
            ['groups' => ['user_anti_cr', 'level_anti_cr', 'score_api_response']]
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
     *     description="Score not found"
     * )
     * @param int $id
     * @param ScoreRepository $scoreRepository
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_score_api_delete', methods: ['DELETE'], format: 'application/json')]
    public function delete(int $id, ScoreRepository $scoreRepository): Response
    {
        $score = $scoreRepository->findOneBy(["id" => $id]);
        if ($score === null) {
            throw new ScoreNotFoundApiException();
        }

        $scoreRepository->remove($score, true);

        return $this->json(ApiResponse::get(null, Response::HTTP_NO_CONTENT));
    }
}
