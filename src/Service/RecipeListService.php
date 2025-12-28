<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RecipeFilterDto;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use Traversable;

use function ceil;
use function count;

readonly class RecipeListService
{
    private const RECIPES_PER_PAGE = 3;

    public function __construct(
        private RecipeRepository $recipeRepository,
        private UserRepository $userRepository,
        private IngredientRepository $ingredientRepository,
    ) {}

    /**
     * @return array{
     *     recipes: Traversable<int, Recipe>,
     *     currentPage: int,
     *     totalPages: int,
     *     totalRecipes: int,
     *     hasNextPage: bool,
     *     hasPrevPage: bool,
     *     currentFilter: ?RecipeFilterDto,
     *     filterAuthor: ?User,
     *     filterIngredients: list<Ingredient>
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

        $filterAuthor = null;
        $filterIngredients = [];

        if (null !== $filter) {
            if (null !== $filter->authorId) {
                $filterAuthor = $this->userRepository->findById($filter->authorId);
            }

            if ([] !== $filter->ingredientIds) {
                $filterIngredients = $this->ingredientRepository->findByIds($filter->ingredientIds);
            }
        }

        return [
            'recipes' => $paginator->getIterator(),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecipes' => $totalRecipes,
            'hasNextPage' => $page < $totalPages,
            'hasPrevPage' => $page > 1,
            'currentFilter' => $filter,
            'filterAuthor' => $filterAuthor,
            'filterIngredients' => $filterIngredients,
        ];
    }
}
