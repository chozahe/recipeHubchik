<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RecipeFilterDto;
use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Traversable;

use function ceil;
use function count;

readonly class RecipeListService
{
    private const RECIPES_PER_PAGE = 3;

    public function __construct(
        private RecipeRepository $recipeRepository,
    ) {}

    /**
     * @return array{
     *     recipes: Traversable<int, Recipe>,
     *     currentPage: int,
     *     totalPages: int,
     *     totalRecipes: int,
     *     hasNextPage: bool,
     *     hasPrevPage: bool,
     *     currentFilter: ?RecipeFilterDto
     * }
     */
    public function getRecipes(int $page = 1, ?RecipeFilterDto $filter = null): array
    {
        if ($page < 1) {
            $page = 1;
        }

        $paginator = $this->recipeRepository->findPaginated($page, self::RECIPES_PER_PAGE, $filter);
        $totalRecipes = count($paginator);
        $totalPages = (int) ceil($totalRecipes / self::RECIPES_PER_PAGE);

        return [
            'recipes' => $paginator->getIterator(),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecipes' => $totalRecipes,
            'hasNextPage' => $page < $totalPages,
            'hasPrevPage' => $page > 1,
            'currentFilter' => $filter,
        ];
    }
}
