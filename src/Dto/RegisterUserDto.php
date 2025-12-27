<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email не должен быть пустым')]
        #[Assert\Email(message: 'Email должен быть корректным')]
        public string $email = '',
        #[Assert\NotBlank(message: 'Имя не должно быть пустым')]
        #[Assert\Length(
            min: 2,
            max: 30,
            minMessage: 'Имя должно быть не менее {{ limit }} символов',
            maxMessage: 'Имя должно быть не более {{ limit }} символов'
        )]
        #[Assert\Regex(
            pattern: '/^[а-яёА-ЯЁa-zA-Z\-\s]+$/u',
            message: 'Имя может содержать только буквы, дефисы и пробелы'
        )]
        public string $name = '',
        #[Assert\NotBlank(message: 'Пароль не должен быть пустым')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Пароль должен быть не менее {{ limit }} символов'
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*[a-zA-Zа-яёА-ЯЁ])(?=.*\d)/u',
            message: 'Пароль должен содержать буквы и цифры'
        )]
        public string $password = '',
        #[Assert\NotBlank(message: 'Подтверждение пароля не должно быть пустым')]
        public string $passwordConfirm = '',
    ) {}
}
