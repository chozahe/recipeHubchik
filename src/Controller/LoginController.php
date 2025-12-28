<?php

declare(strict_types=1);

namespace App\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Проверяем, был ли пользователь забанен
        $bannedMessage = null;
        if (1 === $request->query->getInt('banned')) {
            $bannedMessage = 'Ваш аккаунт заблокирован за использование нецензурной лексики в отзыве';
        }

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'banned_message' => $bannedMessage,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
