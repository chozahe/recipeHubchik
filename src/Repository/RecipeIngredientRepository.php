<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RecipeIngredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecipeIngredient>
 */
class RecipeIngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeIngredient::class);
    }

    public function save(RecipeIngredient $recipeIngredient): void
    {
        $this->getEntityManager()->persist($recipeIngredient);
        $this->getEntityManager()->flush();
    }

    public function delete(RecipeIngredient $recipeIngredient): void
    {
        $this->getEntityManager()->remove($recipeIngredient);
        $this->getEntityManager()->flush();
    }
}
