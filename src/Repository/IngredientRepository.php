<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ingredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingredient>
 */
class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function save(Ingredient $ingredient): void
    {
        $this->getEntityManager()->persist($ingredient);
        $this->getEntityManager()->flush();
    }

    public function findOneByNameCaseInsensitive(string $name): ?Ingredient
    {
        /** @var ?Ingredient $result */
        $result = $this->createQueryBuilder('i')
            ->where('LOWER(i.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return list<Ingredient>
     */
    public function findAllOrdered(): array
    {
        /** @var list<Ingredient> $result */
        $result = $this->createQueryBuilder('i')
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return list<Ingredient>
     */
    public function searchByName(string $query, int $limit = 10): array
    {
        /** @var list<Ingredient> $result */
        $result = $this->createQueryBuilder('i')
            ->where('LOWER(i.name) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('i.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
