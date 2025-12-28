<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateReviewDto;
use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\RecipeRepository;
use App\Repository\ReviewRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use RuntimeException;

final readonly class ReviewService
{
    public function __construct(
        private ReviewRepository $reviewRepository,
        private RecipeRepository $recipeRepository,
        private LoggerInterface $logger,
    ) {}

    public function createReview(CreateReviewDto $dto, Recipe $recipe, User $user): Review
    {
        $oldRating = $recipe->getAverageRating();

        $review = new Review(
            rating: $dto->rating,
            comment: $dto->comment,
            user: $user,
            recipe: $recipe,
        );

        $this->reviewRepository->save($review);
        $this->recalculateRecipeRating($recipe);

        $newRating = $recipe->getAverageRating();

        $this->logger->info('Review created in service', ['review_id' => $review->getId(), 'user_id' => $user->getId(), 'recipe_id' => $recipe->getId(), 'rating' => $dto->rating, 'old_rating' => $oldRating, 'new_rating' => $newRating]);

        return $review;
    }

    public function updateReview(Review $review, CreateReviewDto $dto): Review
    {
        $recipe = $review->getRecipe();
        if (null === $recipe) {
            throw new RuntimeException('Review must have associated recipe');
        }

        $oldRating = $recipe->getAverageRating();

        $review->setRating($dto->rating);
        $review->setComment($dto->comment);
        $review->setUpdatedAt(new DateTimeImmutable());

        $this->reviewRepository->save($review);
        $this->recalculateRecipeRating($recipe);

        $newRating = $recipe->getAverageRating();

        $this->logger->info('Review updated', ['review_id' => $review->getId(), 'user_id' => $review->getUser()?->getId(), 'recipe_id' => $recipe->getId(), 'rating' => $dto->rating, 'old_rating' => $oldRating, 'new_rating' => $newRating]);

        return $review;
    }

    public function deleteReview(Review $review): void
    {
        $recipe = $review->getRecipe();
        if (null === $recipe) {
            throw new RuntimeException('Review must have associated recipe');
        }

        $reviewId = $review->getId();
        $oldRating = $recipe->getAverageRating();

        $this->reviewRepository->delete($review);
        $this->recalculateRecipeRating($recipe);

        $newRating = $recipe->getAverageRating();

        $this->logger->info('Review deleted', ['review_id' => $reviewId, 'user_id' => $review->getUser()?->getId(), 'recipe_id' => $recipe->getId(), 'old_rating' => $oldRating, 'new_rating' => $newRating]);
    }

    public function findUserReview(Recipe $recipe, User $user): ?Review
    {
        return $this->reviewRepository->findOneByRecipeAndUser($recipe, $user);
    }

    public function canUserReview(Recipe $recipe, User $user): bool
    {
        // Автор рецепта не может оставлять отзыв на свой рецепт
        if ($recipe->getUser() === $user) {
            return false;
        }

        // Пользователь может оставить только один отзыв
        $existingReview = $this->findUserReview($recipe, $user);

        return null === $existingReview;
    }

    private function recalculateRecipeRating(Recipe $recipe): void
    {
        $averageRating = $this->reviewRepository->calculateAverageRating($recipe);
        $recipe->setAverageRating($averageRating);
        $this->recipeRepository->save($recipe);
    }
}
