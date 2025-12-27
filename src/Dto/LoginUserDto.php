<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LoginUserDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email не должен быть пустым')]
        #[Assert\Email(message: 'Email должен быть корректным')]
        public string $email = '',
        #[Assert\NotBlank(message: 'Пароль не должен быть пустым')]
        public string $password = '',
    ) {}
}
