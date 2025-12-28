<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ChangePasswordDto;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Service\PasswordChangeService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly PasswordChangeService $passwordChangeService,
    ) {}

    #[Route('', name: 'app_profile', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $userInterface = $this->getUser();

        if (!$userInterface instanceof User) {
            throw $this->createAccessDeniedException('Вы должны быть авторизованы для доступа к профилю');
        }

        $changePasswordDto = new ChangePasswordDto();
        $form = $this->createForm(ChangePasswordFormType::class, $changePasswordDto);

        return $this->render('profile/index.html.twig', [
            'user' => $userInterface,
            'changePasswordForm' => $form,
        ]);
    }

    #[Route('/change-password', name: 'app_profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request): Response
    {
        $userInterface = $this->getUser();

        if (!$userInterface instanceof User) {
            throw $this->createAccessDeniedException('Вы должны быть авторизованы для смены пароля');
        }

        $changePasswordDto = new ChangePasswordDto();
        $form = $this->createForm(ChangePasswordFormType::class, $changePasswordDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->passwordChangeService->changePassword($userInterface, $changePasswordDto);
                $this->addFlash('success', 'Пароль успешно изменен');

                return $this->redirectToRoute('app_profile', [], 303);
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->render('profile/index.html.twig', [
                    'user' => $userInterface,
                    'changePasswordForm' => $form,
                ], new Response('', 422));
            }
        }

        $this->addFlash('error', 'Пожалуйста, проверьте введенные данные');

        return $this->render('profile/index.html.twig', [
            'user' => $userInterface,
            'changePasswordForm' => $form,
        ], new Response('', 422));
    }
}
