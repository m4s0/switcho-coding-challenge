<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findById(Uuid $id): ?Game
    {
        return $this->createQueryBuilder('game')
            ->andWhere('game.id = :id')
            ->setParameter('id', $id)->getQuery()
            ->getOneOrNullResult();
    }
}
