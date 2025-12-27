<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\UserSearchRequestDto;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    #[Route('/api/users/search', name: 'app_users_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        $searchRequest = new UserSearchRequestDto(query: $query);
        $results = $this->userService->searchUsers($searchRequest);

        return $this->json($results);
    }
}
