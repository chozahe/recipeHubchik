<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\RecipeFilterDto;
use App\Entity\Recipe;
use App\Entity\User;
use App\Enum\RecipeSortOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

use function count;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function save(Recipe $recipe): void
    {
        $this->getEntityManager()->persist($recipe);
        $this->getEntityManager()->flush();
    }

    public function delete(Recipe $recipe): void
    {
        $this->getEntityManager()->remove($recipe);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<Recipe>
     */
    public function findAllWithUser(): array
    {
        /** @var list<Recipe> $result */
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return list<Recipe>
     */
    public function findByUser(User $user): array
    {
        /** @var list<Recipe> $result */
        $result = $this->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $result;
    }

    public function findOneWithRelations(int $id): ?Recipe
    {
        /** @var ?Recipe $result */
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.recipeIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'i')
            ->addSelect('u', 'ri', 'i')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return Paginator<Recipe>
     */
    public function findPaginated(int $page = 1, int $limit = 12, ?RecipeFilterDto $filter = null): Paginator
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        // Применить фильтры
        if (null !== $filter) {
            // Фильтр по названию
            if (null !== $filter->name && '' !== $filter->name) {
                $qb->andWhere('LOWER(r.title) LIKE LOWER(:name)')
                    ->setParameter('name', '%' . $filter->name . '%');
            }

            // Фильтр по ингредиентам
            if (!([] === $filter->ingredients)) {
                $qb->innerJoin('r.recipeIngredients', 'ri')
                    ->innerJoin('ri.ingredient', 'i')
                    ->andWhere('i.name IN (:ingredients)')
                    ->setParameter('ingredients', $filter->ingredients)
                    ->groupBy('r.id', 'u.id')
                    ->having('COUNT(DISTINCT i.id) = :ingredientCount')
                    ->setParameter('ingredientCount', count($filter->ingredients));
            }

            // Фильтр по автору
            if (null !== $filter->authorId) {
                $qb->andWhere('r.user = :author')
                    ->setParameter('author', $filter->authorId);
            }

            // Фильтр по минимальному рейтингу
            if (null !== $filter->minRating) {
                $qb->andWhere('r.averageRating >= :minRating')
                    ->setParameter('minRating', $filter->minRating);
            }

            // Применить сортировку
            $orderBy = $filter->sort->toOrderBy();

            // Для сортировки по рейтингу: рецепты без рейтинга идут в конец
            if (RecipeSortOption::RATING_DESC === $filter->sort || RecipeSortOption::RATING_ASC === $filter->sort) {
                $qb->orderBy('CASE WHEN r.averageRating IS NULL THEN 1 ELSE 0 END', 'ASC')
                    ->addOrderBy($orderBy['field'], $orderBy['direction']);
            } else {
                $qb->orderBy($orderBy['field'], $orderBy['direction']);
            }
        } else {
            $qb->orderBy('r.createdAt', 'DESC');
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();

        /**
         * @var Paginator<Recipe> $paginator
         */
        $paginator = new Paginator($query, true);

        return $paginator;
    }
}
