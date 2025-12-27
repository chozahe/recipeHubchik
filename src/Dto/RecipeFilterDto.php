<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\RecipeSortOption;

readonly class RecipeFilterDto
{
    /**
     * @param array<string> $ingredients
     */
    public function __construct(
        public ?string $name = null,
        public array $ingredients = [],
        public ?int $authorId = null,
        public ?float $minRating = null,
        public RecipeSortOption $sort = RecipeSortOption::CREATED_DESC,
    ) {}

    public function isEmpty(): bool
    {
        return null === $this->name
            && [] === $this->ingredients
            && null === $this->authorId
            && null === $this->minRating;
    }
}
