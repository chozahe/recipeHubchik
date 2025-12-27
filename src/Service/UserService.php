<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserSearchRequestDto;
use App\Dto\UserSearchResultDto;
use App\Repository\UserRepository;

use function array_map;
use function strlen;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * @return list<UserSearchResultDto>
     */
    public function searchUsers(UserSearchRequestDto $request): array
    {
        if (strlen($request->query) < 2) {
            return [];
        }

        $users = $this->userRepository->searchByName($request->query, 10);

        return array_map(
            static fn ($user) => new UserSearchResultDto(
                id: $user->getId() ?? 0,
                name: $user->getName(),
            ),
            $users
        );
    }
}
