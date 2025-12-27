<?php

declare(strict_types=1);

namespace App\Dto;

final class IngredientSearchResultDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
