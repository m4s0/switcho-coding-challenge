<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\DomainException;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use App\Infrastructure\Persistence\Entity\GameEntity;

class Game
{
    public const IN_PROGRESS = 'in_progress';
    public const WON = 'won';
    public const DRAW = 'draw';

    private Board $board;
    private PlayerId $currentPlayer;
    private bool $isFinished;
    private ?PlayerId $winner;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        private readonly GameId $id,
        Board $board,
        PlayerId $currentPlayer,
        bool $isFinished,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?PlayerId $winner = null,
    ) {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
        $this->isFinished = $isFinished;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->winner = $winner;
    }

    public static function create(GameId $id): self
    {
        return new self(
            id: $id,
            board: Board::createEmpty(),
            currentPlayer: PlayerId::PLAYER_ONE,
            isFinished: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }

    public static function fromEntity(GameEntity $entity): self
    {
        return new self(
            id: GameId::fromString($entity->getId()),
            board: Board::deserialize($entity->getBoard()),
            currentPlayer: PlayerId::from($entity->getCurrentPlayer()),
            isFinished: $entity->isFinished(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            winner: null !== $entity->getWinner() ? PlayerId::from($entity->getWinner()) : null,
        );
    }

    public function makeMove(PlayerId $playerId, Position $position): void
    {
        $this->validateMove($playerId, $position);
        $this->board->makeMove($position, $playerId);
        $this->updatedAt = new \DateTimeImmutable();

        if ($this->board->hasWinner()) {
            $this->winner = $this->board->getWinner();
            $this->isFinished = true;

            return;
        }

        if ($this->board->isFull()) {
            $this->isFinished = true;

            return;
        }

        $this->currentPlayer = $this->currentPlayer->getOpponent();
    }

    private function validateMove(PlayerId $playerId, Position $position): void
    {
        if ($this->isFinished) {
            throw new DomainException('Game is already finished');
        }

        if ($this->currentPlayer !== $playerId) {
            throw new DomainException('It is not your turn');
        }

        if (!$this->board->isPositionEmpty($position)) {
            throw new DomainException('Position is already occupied');
        }
    }

    public function getWinner(): ?PlayerId
    {
        return $this->winner;
    }

    public function getId(): GameId
    {
        return $this->id;
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getCurrentPlayer(): PlayerId
    {
        return $this->currentPlayer;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function isDraw(): bool
    {
        return $this->isFinished && null === $this->winner;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getStatus(): string
    {
        return match (true) {
            !$this->isFinished => self::IN_PROGRESS,
            null !== $this->winner => self::WON,
            default => self::DRAW,
        };
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->getId())
            && $this->board->equals($other->getBoard())
            && $this->currentPlayer->equals($other->getCurrentPlayer())
            && $this->isFinished === $other->isFinished()
            && ($this->winner?->equals($other->getWinner()) ?? null === $other->getWinner());
    }
}
