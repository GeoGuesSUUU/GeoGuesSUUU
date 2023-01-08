<?php

namespace App\Controller\WebSocket;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatWebSocketController extends AbstractController
{
    #[Route('/admin/chat', name: 'app_admin_chat')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('websocket/index.html.twig', [
            'controller_name' => 'ChatWebsocketController',
            'user' => $user,
            'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles())
        ]);
    }
}
