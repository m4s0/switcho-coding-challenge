<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\GameId;
use App\Infrastructure\Persistence\Entity\GameEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineGameRepository extends ServiceEntityRepository implements GameRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameEntity::class);
    }

    public function save(Game $game): void
    {
        $entity = $this->toEntity($game);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        if (null === $game->getId()->getValue()) {
            $game->setId(new GameId($entity->getId()));
        }
    }

    public function findById(GameId $gameId): ?Game
    {
        $entity = $this->find($gameId->getValue());

        if (!$entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function remove(Game $game): void
    {
        $entity = $this->find($game->getId()->getValue());

        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    private function toEntity(Game $game): GameEntity
    {
        $entityId = $game->getId()->getValue();
        $entity = $entityId ? $this->find($entityId) : null;

        if (!$entity) {
            $entity = new GameEntity();
        }

        $entity->setBoard($game->getBoard()->toArray())
               ->setCurrentPlayer($game->getCurrentPlayer()->getValue())
               ->setIsFinished($game->isFinished())
               ->setWinner($game->getWinner()?->getValue())
               ->setCreatedAt($game->getCreatedAt());

        return $entity;
    }

    private function toDomain(GameEntity $entity): Game
    {
        $game = new Game(new GameId($entity->getId()));

        $game->restoreState(
            $entity->getBoard(),
            $entity->getCurrentPlayer(),
            $entity->isFinished(),
            $entity->getWinner(),
            $entity->getCreatedAt()
        );

        return $game;
    }
}
