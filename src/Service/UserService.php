<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserSearchRequestDto;
use App\Dto\UserSearchResultDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function array_map;
use function strlen;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'monolog.logger.security')]
        private LoggerInterface $logger,
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

    public function banUser(User $user): void
    {
        $this->logger->warning('User banned', [
            'user_id' => $user->getId(),
            'user_email' => $user->getEmail(),
            'user_name' => $user->getName(),
        ]);

        $user->setIsActive(false);
        $this->entityManager->flush();
    }

    public function unbanUser(User $user): void
    {
        $this->logger->info('User unbanned', [
            'user_id' => $user->getId(),
            'user_email' => $user->getEmail(),
            'user_name' => $user->getName(),
        ]);

        $user->setIsActive(true);
        $this->entityManager->flush();
    }

    /**
     * @return list<User>
     */
    public function findAllUsers(): array
    {
        return $this->userRepository->findAllOrderedByCreatedAt();
    }
}
