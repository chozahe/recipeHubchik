<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\IngredientSearchRequestDto;
use App\Dto\IngredientSearchResultDto;
use App\Repository\IngredientRepository;

use function array_map;
use function strlen;

final readonly class IngredientService
{
    public function __construct(
        private IngredientRepository $ingredientRepository,
    ) {}

    /**
     * @return list<IngredientSearchResultDto>
     */
    public function searchIngredients(IngredientSearchRequestDto $request): array
    {
        if (strlen($request->query) < 2) {
            return [];
        }

        $ingredients = $this->ingredientRepository->searchByName($request->query, 10);

        return array_map(
            static fn ($ingredient) => new IngredientSearchResultDto(
                id: $ingredient->getId() ?? 0,
                name: $ingredient->getName(),
            ),
            $ingredients
        );
    }
}
