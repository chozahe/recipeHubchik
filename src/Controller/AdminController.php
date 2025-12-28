<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function is_string;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(): Response
    {
        $users = $this->userService->findAllUsers();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}/ban', name: 'app_admin_user_ban', methods: ['POST'])]
    public function banUser(User $user, Request $request): Response
    {
        $token = $request->request->get('_token');
        $csrfToken = is_string($token) ? $token : null;

        if (!$this->isCsrfTokenValid('ban_user_' . $user->getId(), $csrfToken)) {
            $this->addFlash('error', 'Недействительный CSRF токен');

            return $this->redirectToRoute('app_admin_users');
        }

        if (!$user->isActive()) {
            $this->addFlash('warning', 'Пользователь уже заблокирован');

            return $this->redirectToRoute('app_admin_users');
        }

        $this->userService->banUser($user);
        $this->addFlash('success', 'Пользователь успешно заблокирован');

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/unban', name: 'app_admin_user_unban', methods: ['POST'])]
    public function unbanUser(User $user, Request $request): Response
    {
        $token = $request->request->get('_token');
        $csrfToken = is_string($token) ? $token : null;

        if (!$this->isCsrfTokenValid('unban_user_' . $user->getId(), $csrfToken)) {
            $this->addFlash('error', 'Недействительный CSRF токен');

            return $this->redirectToRoute('app_admin_users');
        }

        if ($user->isActive()) {
            $this->addFlash('warning', 'Пользователь не заблокирован');

            return $this->redirectToRoute('app_admin_users');
        }

        $this->userService->unbanUser($user);
        $this->addFlash('success', 'Пользователь успешно разблокирован');

        return $this->redirectToRoute('app_admin_users');
    }
}
