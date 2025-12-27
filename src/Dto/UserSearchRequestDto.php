<?php

declare(strict_types=1);

namespace App\Dto;

final class UserSearchRequestDto
{
    public function __construct(
        public readonly string $query = '',
    ) {}
}
