<?php

namespace App\Controller\WebSocket;

use App\Entity\User;
use App\Exception\UserNotFoundApiException;
use App\Repository\GameRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FindTheFlagWebSocketController extends AbstractController
{
    #[Route('/admin/game-page/find-the-flag', name: 'app_admin_game_find_the_flag')]
    public function index(GameRepository $gameRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $game = $gameRepository->findOneBy(['title' => 'Find The Flag']);

        if (is_null($game)) {
            throw new NotFoundHttpException();
        }

        return $this->render('websocket/game.html.twig', [
            'controller_name' => 'FindTheFlagWebsocketController',
            'user' => $user,
            'data' => $game,
            'bootstrap' => true,
            'icon' => '/assets/img/items/flag.svg',
            'script' => 'assets/js/find-the-flag.js',
            'host' => $_SERVER['HTTP_HOST']
        ]);
    }

    #[Security(name: 'Bearer')]
    #[Route('/api/game-page/find-the-flag', name: 'app_api_game_find_the_flag' , methods: ['GET'], format: 'text/html')]
    public function api(GameRepository $gameRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (is_null($user)) {
            throw new UserNotFoundApiException();
        }

        $game = $gameRepository->findOneBy(['title' => 'Find The Flag']);

        if (is_null($game)) {
            throw new NotFoundHttpException();
        }

        return $this->render('websocket/game.html.twig', [
            'controller_name' => 'FindTheFlagWebsocketController',
            'user' => $user,
            'data' => $game,
            'bootstrap' => false,
            'icon' => '/assets/img/items/flag.svg',
            'script' => 'assets/js/find-the-flag.js',
            'host' => $_SERVER['HTTP_HOST']
        ]);
    }
}
