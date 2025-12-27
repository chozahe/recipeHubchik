<?php

declare(strict_types=1);

namespace App\Enum;

enum RecipeSortOption: string
{
    case CREATED_DESC = 'created_desc';
    case CREATED_ASC = 'created_asc';
    case RATING_DESC = 'rating_desc';
    case RATING_ASC = 'rating_asc';
    case NAME_ASC = 'name_asc';
    case NAME_DESC = 'name_desc';

    /**
     * @return array{field: string, direction: string}
     */
    public function toOrderBy(): array
    {
        return match ($this) {
            self::CREATED_DESC => ['field' => 'r.createdAt', 'direction' => 'DESC'],
            self::CREATED_ASC => ['field' => 'r.createdAt', 'direction' => 'ASC'],
            self::RATING_DESC => ['field' => 'r.averageRating', 'direction' => 'DESC'],
            self::RATING_ASC => ['field' => 'r.averageRating', 'direction' => 'ASC'],
            self::NAME_ASC => ['field' => 'r.title', 'direction' => 'ASC'],
            self::NAME_DESC => ['field' => 'r.title', 'direction' => 'DESC'],
        };
    }

    public static function fromString(?string $value): self
    {
        if (null === $value) {
            return self::CREATED_DESC;
        }

        return self::tryFrom($value) ?? self::CREATED_DESC;
    }
}
