<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Review;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function in_array;

/**
 * @extends Voter<string, Review>
 */
final class ReviewVoter extends Voter
{
    public const EDIT = 'REVIEW_EDIT';
    public const DELETE = 'REVIEW_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Review;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Review $review */
        $review = $subject;
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Администратор может редактировать/удалять любой отзыв
        if ($user->isAdmin()) {
            return true;
        }

        // Только автор отзыва может редактировать/удалять свой отзыв
        return $review->getUser() === $user;
    }
}
