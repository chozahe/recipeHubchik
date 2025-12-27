<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateRecipeDto;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\User;
use App\Repository\IngredientRepository;
use App\Repository\RecipeIngredientRepository;
use App\Repository\RecipeRepository;

use function trim;

readonly class RecipeService
{
    public function __construct(
        private RecipeRepository $recipeRepository,
        private IngredientRepository $ingredientRepository,
        private RecipeIngredientRepository $recipeIngredientRepository,
    ) {}

    public function createRecipe(CreateRecipeDto $dto, User $user): Recipe
    {
        $recipe = new Recipe(
            title: $dto->title,
            description: $dto->description,
            servings: $dto->servings,
            cookingTime: $dto->cookingTime,
            recipe: $dto->recipe,
            user: $user,
        );

        $this->recipeRepository->save($recipe);
        foreach ($dto->ingredients as $ingredientDto) {
            $ingredient = $this->findOrCreateIngredient($ingredientDto->ingredientName);

            $recipeIngredient = new RecipeIngredient(
                recipe: $recipe,
                ingredient: $ingredient,
                quantity: $ingredientDto->quantity,
                unit: $ingredientDto->unit,
            );

            $this->recipeIngredientRepository->save($recipeIngredient);
        }

        return $recipe;
    }

    public function deleteRecipe(Recipe $recipe): void
    {
        $this->recipeRepository->delete($recipe);
    }

    public function updateRecipe(Recipe $recipe, CreateRecipeDto $dto): Recipe
    {
        $recipe->setTitle($dto->title);
        $recipe->setDescription($dto->description);
        $recipe->setRecipe($dto->recipe);
        $recipe->setServings($dto->servings);
        $recipe->setCookingTime($dto->cookingTime);

        foreach ($recipe->getRecipeIngredients() as $recipeIngredient) {
            $this->recipeIngredientRepository->delete($recipeIngredient);
        }

        foreach ($dto->ingredients as $ingredientDto) {
            $ingredient = $this->findOrCreateIngredient($ingredientDto->ingredientName);
            $recipeIngredient = new RecipeIngredient(
                recipe: $recipe,
                ingredient: $ingredient,
                quantity: $ingredientDto->quantity,
                unit: $ingredientDto->unit,
            );
            $this->recipeIngredientRepository->save($recipeIngredient);
        }

        $this->recipeRepository->save($recipe);

        return $recipe;
    }

    /**
     * @return list<Recipe>
     */
    public function findByUser(User $user): array
    {
        return $this->recipeRepository->findByUser($user);
    }

    private function findOrCreateIngredient(string $name): Ingredient
    {
        $name = trim($name);

        $ingredient = $this->ingredientRepository->findOneByNameCaseInsensitive($name);

        if (null !== $ingredient) {
            return $ingredient;
        }

        $ingredient = new Ingredient($name);
        $this->ingredientRepository->save($ingredient);

        return $ingredient;
    }
}
