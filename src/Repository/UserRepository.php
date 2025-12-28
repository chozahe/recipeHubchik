<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function emailExists(string $email): bool
    {
        return null !== $this->findByEmail($email);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    /**
     * Поиск пользователей по имени (частичное совпадение, без учёта регистра).
     *
     * @return list<User>
     */
    public function searchByName(string $query, int $limit = 10): array
    {
        /** @var list<User> $result */
        $result = $this->createQueryBuilder('u')
            ->where('LOWER(u.name) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults($limit)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return list<User>
     */
    public function findAllOrderedByCreatedAt(): array
    {
        /** @var list<User> $result */
        $result = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
