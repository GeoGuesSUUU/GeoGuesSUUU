<?php

namespace App\Controller\WebSocket;

use App\Entity\User;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FindTheFlagWebSocketController extends AbstractController
{
    #[Route('/ws/game/find-the-flag', name: 'app_game_find_the_flag')]
    public function index(GameRepository $gameRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $game = $gameRepository->findOneBy(['title' => 'Find The Flag']);

        if (is_null($game)) {
            throw new NotFoundHttpException();
        }

        return $this->render('websocket/ftf.html.twig', [
            'controller_name' => 'FindTheFlagWebsocketController',
            'user' => $user,
            'data' => $game
        ]);
    }
}
