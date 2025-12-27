<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function in_array;

/**
 * @extends Voter<string, Recipe>
 */
final class RecipeVoter extends Voter
{
    public const VIEW = 'RECIPE_VIEW';
    public const EDIT = 'RECIPE_EDIT';
    public const DELETE = 'RECIPE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Recipe;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Recipe $recipe */
        $recipe = $subject;
        $user = $token->getUser();
        if (self::VIEW === $attribute) {
            return true;
        }
        if (!$user instanceof User) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        return $recipe->getUser() === $user;
    }
}
