<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateRecipeDto;
use App\Dto\RecipeFilterDto;
use App\Dto\RecipeIngredientDto;
use App\Entity\Recipe;
use App\Enum\RecipeSortOption;
use App\Form\RecipeFormType;
use App\Service\RecipeListService;
use App\Service\RecipeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function max;

class RecipeController extends AbstractController
{
    public function __construct(
        private readonly RecipeService $recipeService,
    ) {}

    #[Route('/recipes/create', name: 'app_recipe_create', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(Request $request): Response
    {
        $dto = new CreateRecipeDto();
        $form = $this->createForm(RecipeFormType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $recipe = $this->recipeService->createRecipe($dto, $user);

            $this->addFlash('success', 'Рецепт успешно создан!');

            return $this->redirectToRoute('app_recipe_view', ['id' => $recipe->getId()]);
        }

        return $this->render('recipe/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/recipes/my', name: 'app_recipes_my', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myRecipes(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $recipes = $this->recipeService->findByUser($user);

        return $this->render('recipe/my.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipes/{id}/edit', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    #[IsGranted('RECIPE_EDIT', subject: 'recipe')]
    public function edit(Recipe $recipe, Request $request): Response
    {
        $dto = new CreateRecipeDto();
        $dto->title = $recipe->getTitle();
        $dto->description = $recipe->getDescription();
        $dto->recipe = $recipe->getRecipe();
        $dto->servings = $recipe->getServings();
        $dto->cookingTime = $recipe->getCookingTime();

        foreach ($recipe->getRecipeIngredients() as $recipeIngredient) {
            $ingredientDto = new RecipeIngredientDto();
            $ingredientDto->ingredientName = $recipeIngredient->getIngredient()->getName();
            $ingredientDto->quantity = $recipeIngredient->getQuantity();
            $ingredientDto->unit = $recipeIngredient->getUnit();
            $dto->ingredients[] = $ingredientDto;
        }

        $form = $this->createForm(RecipeFormType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->updateRecipe($recipe, $dto);

            $this->addFlash('success', 'Рецепт успешно обновлен!');

            return $this->redirectToRoute('app_recipes_my');
        }

        return $this->render('recipe/edit.html.twig', [
            'form' => $form,
            'recipe' => $recipe,
        ]);
    }

    #[Route('/recipes/{id}/delete', name: 'app_recipe_delete', methods: ['POST'])]
    #[IsGranted('RECIPE_DELETE', subject: 'recipe')]
    public function delete(Recipe $recipe, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete-recipe-' . $recipe->getId(), $request->request->getString('_token'))) {
            $this->recipeService->deleteRecipe($recipe);
            $this->addFlash('success', 'Рецепт успешно удален!');
        } else {
            $this->addFlash('error', 'Неверный CSRF токен');
        }

        return $this->redirectToRoute('app_recipes_my');
    }

    #[Route('/recipes/{id}', name: 'app_recipe_view', methods: ['GET'])]
    #[IsGranted('RECIPE_VIEW', subject: 'recipe')]
    public function view(Recipe $recipe): Response
    {
        return $this->render('recipe/view.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/recipes', name: 'app_recipes_list', methods: ['GET'])]
    public function list(Request $request, RecipeListService $recipeListService): Response
    {
        $page = max(1, $request->query->getInt('page', 1));

        $authorParam = $request->query->get('author');
        $authorId = null !== $authorParam && '' !== $authorParam ? (int) $authorParam : null;

        $minRatingParam = $request->query->get('min_rating');
        $minRating = null !== $minRatingParam && '' !== $minRatingParam ? (float) $minRatingParam : null;

        /** @var list<int> $ingredientIds */
        $ingredientIds = $request->query->all('ingredients');
        $filter = new RecipeFilterDto(
            name: '' !== $request->query->getString('name') ? $request->query->getString('name') : null,
            ingredientIds: $ingredientIds,
            authorId: $authorId,
            minRating: $minRating,
            sort: RecipeSortOption::fromString($request->query->getString('sort')),
        );

        $data = $recipeListService->getRecipes($page, $filter);

        return $this->render('recipe/list.html.twig', $data);
    }
}
