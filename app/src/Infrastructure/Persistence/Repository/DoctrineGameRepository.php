<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\GameId;
use App\Infrastructure\Persistence\Entity\GameEntity;
use Doctrine\ORM\EntityManagerInterface;

readonly class DoctrineGameRepository implements GameRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Game $game): void
    {
        $gameEntity = $this->entityManager->find(GameEntity::class, $game->getId()->toString());

        if ($gameEntity instanceof GameEntity) {
            $gameEntity->updateFromDomain(
                board: Board::serialize($game->getBoard()),
                currentPlayer: $game->getCurrentPlayer()->value,
                isFinished: $game->isFinished(),
                winner: $game->getWinner()?->value,
                updatedAt: $game->getUpdatedAt()
            );
            $this->entityManager->flush();

            return;
        }

        $gameEntity = new GameEntity(
            id: $game->getId()->toString(),
            board: Board::serialize($game->getBoard()),
            currentPlayer: $game->getCurrentPlayer()->value,
            isFinished: $game->isFinished(),
            winner: $game->getWinner()?->value,
            createdAt: $game->getCreatedAt(),
            updatedAt: $game->getUpdatedAt()
        );
        $this->entityManager->persist($gameEntity);

        $this->entityManager->flush();
    }

    public function findById(GameId $id): ?Game
    {
        $gameEntity = $this->entityManager->find(GameEntity::class, $id->toString());
        if (!$gameEntity instanceof GameEntity) {
            return null;
        }

        return Game::fromEntity($gameEntity);
    }

    public function delete(GameId $id): void
    {
        $gameEntity = $this->entityManager->find(GameEntity::class, $id->toString());
        if ($gameEntity instanceof GameEntity) {
            $this->entityManager->remove($gameEntity);
            $this->entityManager->flush();
        }
    }
}
