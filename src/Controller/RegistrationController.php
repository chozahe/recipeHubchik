<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RegisterUserDto;
use App\Form\RegisterFormType;
use App\Service\RegistrationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
    ) {}

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $registerDto = new RegisterUserDto();
        $form = $this->createForm(RegisterFormType::class, $registerDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->registrationService->registerUser($registerDto);

                return $this->redirectToRoute('app_login', [], 303);
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());

                // Нужно передать 422 чтобы hotwire знал что не нужно форму очищать
                return $this->render('registration/register.html.twig', [
                    'form' => $form,
                    'formTarget' => $request->headers->get('Turbo-Frame', '_top'),
                ], new Response('', 422));
            }
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
            'formTarget' => $request->headers->get('Turbo-Frame', '_top'),
        ]);
    }
}
