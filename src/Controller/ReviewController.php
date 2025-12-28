<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateReviewDto;
use App\Entity\Recipe;
use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Service\ContentModerationService;
use App\Service\ReviewService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewService $reviewService,
        private readonly ContentModerationService $contentModerationService,
        private readonly UserService $userService,
        private readonly Security $security,
    ) {}

    #[Route('/recipes/{id}/reviews/create', name: 'app_review_create', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(Recipe $recipe, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$this->reviewService->canUserReview($recipe, $user)) {
            $this->addFlash('error', 'Вы не можете оставить отзыв на этот рецепт');

            return $this->redirectToRoute('app_recipe_view', ['id' => $recipe->getId()]);
        }

        $dto = new CreateReviewDto();
        $form = $this->createForm(ReviewFormType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Проверяем комментарий на наличие нецензурной лексики
            if (null !== $dto->comment && $this->contentModerationService->containsProfanity($dto->comment)) {
                // Баним пользователя
                $this->userService->banUser($user);

                // Разлогиниваем пользователя
                $this->security->logout(false);

                $this->addFlash('error', 'Ваш аккаунт заблокирован за использование нецензурной лексики в отзыве');

                return $this->redirectToRoute('app_recipe_view', ['id' => $recipe->getId()]);
            }

            $this->reviewService->createReview($dto, $recipe, $user);

            $this->addFlash('success', 'Отзыв успешно создан');

            return $this->redirectToRoute('app_recipe_view', ['id' => $recipe->getId()]);
        }

        return $this->render('review/create.html.twig', [
            'form' => $form,
            'recipe' => $recipe,
        ]);
    }

    #[Route('/reviews/{id}/edit', name: 'app_review_edit', methods: ['GET', 'POST'])]
    #[IsGranted('REVIEW_EDIT', subject: 'review')]
    public function edit(Review $review, Request $request): Response
    {
        $recipe = $review->getRecipe();
        if (null === $recipe) {
            throw $this->createNotFoundException('Рецепт не найден');
        }

        $dto = new CreateReviewDto(
            rating: $review->getRating(),
            comment: $review->getComment(),
        );

        $form = $this->createForm(ReviewFormType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();

            // Проверяем комментарий на наличие нецензурной лексики
            if (null !== $dto->comment && $this->contentModerationService->containsProfanity($dto->comment)) {
                // Баним пользователя
                $this->userService->banUser($currentUser);

                // Разлогиниваем пользователя
                $this->security->logout(false);

                $this->addFlash('error', 'Ваш аккаунт заблокирован за использование нецензурной лексики в отзыве');

                return $this->redirectToRoute('app_recipe_view', ['id' => $recipe->getId()]);
            }

            $this->reviewService->updateReview($review, $dto);

            $this->addFlash('success', 'Отзыв успешно обновлен');

            return $this->redirectToRoute('app_recipe_view', ['id' => $recipe->getId()]);
        }

        return $this->render('review/edit.html.twig', [
            'form' => $form,
            'review' => $review,
        ]);
    }

    #[Route('/reviews/{id}/delete', name: 'app_review_delete', methods: ['POST'])]
    #[IsGranted('REVIEW_DELETE', subject: 'review')]
    public function delete(Review $review, Request $request): Response
    {
        $recipe = $review->getRecipe();
        if (null === $recipe) {
            throw $this->createNotFoundException('Рецепт не найден');
        }

        $recipeId = $recipe->getId();

        if ($this->isCsrfTokenValid('delete-review-' . $review->getId(), $request->request->getString('_token'))) {
            $this->reviewService->deleteReview($review);
            $this->addFlash('success', 'Отзыв успешно удален');
        } else {
            $this->addFlash('error', 'Неверный CSRF токен');
        }

        return $this->redirectToRoute('app_recipe_view', ['id' => $recipeId]);
    }
}
