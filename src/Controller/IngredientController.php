<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\IngredientSearchRequestDto;
use App\Service\IngredientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class IngredientController extends AbstractController
{
    public function __construct(
        private readonly IngredientService $ingredientService,
    ) {}

    #[Route('/api/ingredients/search', name: 'app_ingredients_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        $searchRequest = new IngredientSearchRequestDto(query: $query);
        $results = $this->ingredientService->searchIngredients($searchRequest);

        return $this->json($results);
    }
}
