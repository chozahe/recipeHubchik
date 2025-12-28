<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ChangePasswordDto;
use App\Entity\User;
use App\Repository\UserRepository;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class PasswordChangeService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) {}

    /**
     * Changes user password after validating the current password.
     *
     * @throws InvalidArgumentException if current password is incorrect
     */
    public function changePassword(User $user, ChangePasswordDto $dto): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $dto->currentPassword)) {
            $this->logger->warning('Failed password change attempt - invalid current password', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
            ]);

            throw new InvalidArgumentException('Текущий пароль указан неверно');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->newPassword);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        $this->logger->info('Password changed successfully', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
        ]);
    }
}
