<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateReviewDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Рейтинг обязателен для заполнения')]
        #[Assert\Range(
            min: 1,
            max: 5,
            notInRangeMessage: 'Рейтинг должен быть от {{ min }} до {{ max }}'
        )]
        public int $rating = 0,
        #[Assert\Length(
            max: 1000,
            maxMessage: 'Комментарий не должен превышать {{ limit }} символов'
        )]
        public ?string $comment = null,
    ) {}
}
