<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RecipeIngredientDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Название ингредиента обязательно')]
        #[Assert\Length(min: 2, max: 255, minMessage: 'Минимум 2 символа')]
        public string $ingredientName = '',
        #[Assert\NotBlank(message: 'Количество обязательно')]
        #[Assert\Positive(message: 'Количество должно быть положительным')]
        public float $quantity = 0,
        #[Assert\NotBlank(message: 'Единица измерения обязательна')]
        public string $unit = '',
    ) {}
}
