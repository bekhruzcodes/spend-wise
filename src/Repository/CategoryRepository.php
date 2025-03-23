<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findByUser(?int $userId): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.user IS NULL OR c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.name', 'ASC');
        
        return $qb->getQuery()->getResult();
    }
}