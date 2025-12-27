<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RegisterUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws Exception if email already exists or registration fails
     */
    public function registerUser(RegisterUserDto $dto): User
    {
        $email = $dto->email;
        $name = $dto->name;
        $password = $dto->password;

        if ($this->userRepository->emailExists($email)) {
            throw new Exception('Этот email уже зарегистрирован');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return $user;
    }
}
