<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Infrastructure\Persistence\Entity\GameEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineGameRepository implements GameRepositoryInterface
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
                board: $this->serializeBoard($game->getBoard()),
                currentPlayer: $game->getCurrentPlayer()->value,
                isFinished: $game->isFinished(),
                winner: $game->getWinner()?->value,
                updatedAt: $game->getUpdatedAt()
            );
        } else {
            $gameEntity = new GameEntity(
                id: $game->getId()->toString(),
                board: $this->serializeBoard($game->getBoard()),
                currentPlayer: $game->getCurrentPlayer()->value,
                isFinished: $game->isFinished(),
                winner: $game->getWinner()?->value,
                createdAt: $game->getCreatedAt(),
                updatedAt: $game->getUpdatedAt()
            );
            $this->entityManager->persist($gameEntity);
        }
        $this->entityManager->flush();
    }

    public function findById(GameId $id): ?Game
    {
        $gameEntity = $this->entityManager->find(GameEntity::class, $id->toString());
        if (!$gameEntity instanceof GameEntity) {
            return null;
        }

        return $this->toDomainEntity($gameEntity);
    }

    public function delete(GameId $id): void
    {
        $gameEntity = $this->entityManager->find(GameEntity::class, $id->toString());
        if ($gameEntity instanceof GameEntity) {
            $this->entityManager->remove($gameEntity);
            $this->entityManager->flush();
        }
    }

    private function toDomainEntity(GameEntity $gameEntity): Game
    {
        $board = $this->deserializeBoard($gameEntity->getBoard());

        $game = new Game(GameId::fromString($gameEntity->getId()), $board);
        $game->reconstituteState(
            currentPlayer: PlayerId::from($gameEntity->getCurrentPlayer()),
            isFinished: $gameEntity->isFinished(),
            winner: null !== $gameEntity->getWinner() ? PlayerId::from($gameEntity->getWinner()) : null,
            createdAt: $gameEntity->getCreatedAt(),
            updatedAt: $gameEntity->getUpdatedAt()
        );

        return $game;
    }

    private function serializeBoard(Board $board): array
    {
        return array_map(static fn (?PlayerId $playerId): ?int => $playerId?->value, $board->getRawCells());
    }

    private function deserializeBoard(array $data): Board
    {
        $cells = array_map(static fn (?int $playerId): ?PlayerId => null !== $playerId ? PlayerId::from($playerId) : null, $data);

        return new Board($cells);
    }
}
