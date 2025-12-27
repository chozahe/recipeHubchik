<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRecipeDto
{
    /**
     * @param RecipeIngredientDto[] $ingredients
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Название обязательно')]
        #[Assert\Length(min: 3, max: 255, minMessage: 'Минимум 3 символа')]
        public string $title = '',
        #[Assert\Length(max: 2000)]
        #[Assert\NotBlank(message: 'У рецепта обязательно должен быть текст!')]
        public ?string $description = '',
        #[Assert\NotBlank(message: 'У рецепта обязательно должен быть текст!')]
        public string $recipe = '',
        #[Assert\NotBlank(message: 'Количество порций обязательно')]
        #[Assert\Positive]
        #[Assert\Range(min: 1, max: 100)]
        public int $servings = 0,
        #[Assert\NotBlank(message: 'Время приготовления обязательно')]
        #[Assert\Positive]
        #[Assert\Range(min: 1, max: 1440)]
        public int $cookingTime = 0,
        #[Assert\Valid]
        #[Assert\Count(min: 1, minMessage: 'Добавьте хотя бы один ингредиент')]
        public array $ingredients = [],
    ) {}
}
