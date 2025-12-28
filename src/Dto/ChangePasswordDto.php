<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Введите текущий пароль')]
        public string $currentPassword = '',
        #[Assert\NotBlank(message: 'Введите новый пароль')]
        #[Assert\Length(
            min: 6,
            minMessage: 'Новый пароль должен быть не менее {{ limit }} символов'
        )]
        public string $newPassword = '',
        #[Assert\NotBlank(message: 'Подтвердите новый пароль')]
        #[Assert\EqualTo(
            propertyPath: 'newPassword',
            message: 'Пароли не совпадают'
        )]
        public string $confirmPassword = '',
    ) {}
}
