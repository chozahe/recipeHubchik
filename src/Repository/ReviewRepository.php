<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function round;

/**
 * @extends ServiceEntityRepository<Review>
 */
final class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $review): void
    {
        $this->getEntityManager()->persist($review);
        $this->getEntityManager()->flush();
    }

    public function delete(Review $review): void
    {
        $this->getEntityManager()->remove($review);
        $this->getEntityManager()->flush();
    }

    public function findOneByRecipeAndUser(Recipe $recipe, User $user): ?Review
    {
        /** @var ?Review $result */
        $result = $this->createQueryBuilder('r')
            ->where('r.recipe = :recipe')
            ->andWhere('r.user = :user')
            ->setParameter('recipe', $recipe)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return list<Review>
     */
    public function findByRecipeWithUser(Recipe $recipe): array
    {
        /** @var list<Review> $result */
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->where('r.recipe = :recipe')
            ->setParameter('recipe', $recipe)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function calculateAverageRating(Recipe $recipe): ?float
    {
        /** @var null|array{avg_rating: ?float} $result */
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avg_rating')
            ->where('r.recipe = :recipe')
            ->setParameter('recipe', $recipe)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $result || null === $result['avg_rating']) {
            return null;
        }

        return round($result['avg_rating'], 1);
    }

    /**
     * @return int<0, max>
     */
    public function countByRecipe(Recipe $recipe): int
    {
        return $this->count(['recipe' => $recipe]);
    }
}
