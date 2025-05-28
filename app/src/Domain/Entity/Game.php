<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;

class Game
{
    private GameId $id;
    private Board $board;
    private PlayerId $currentPlayer;
    private bool $isFinished;
    private ?PlayerId $winner;
    private \DateTimeImmutable $createdAt;

    public function __construct(?GameId $id = null)
    {
        $this->id = $id ?? new GameId();
        $this->board = new Board();
        $this->currentPlayer = new PlayerId(1);
        $this->isFinished = false;
        $this->winner = null;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): GameId
    {
        return $this->id;
    }

    public function makeMove(PlayerId $playerId, Position $position): void
    {
        $this->ensureGameIsNotFinished();
        $this->ensurePlayerTurn($playerId);

        $this->board = $this->board->makeMove($position, $playerId);

        $this->checkGameEnd();

        if (!$this->isFinished) {
            $this->currentPlayer = $this->currentPlayer->getOpponent();
        }
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

    public function getWinner(): ?PlayerId
    {
        return $this->winner;
    }

    public function isDraw(): bool
    {
        return $this->isFinished && null === $this->winner;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function ensureGameIsNotFinished(): void
    {
        if ($this->isFinished) {
            throw new \DomainException('Game is already finished');
        }
    }

    private function ensurePlayerTurn(PlayerId $playerId): void
    {
        if (!$this->currentPlayer->equals($playerId)) {
            throw new \DomainException("It is not player {$playerId}'s turn");
        }
    }

    private function checkGameEnd(): void
    {
        $winner = $this->board->getWinner();

        if (null !== $winner) {
            $this->winner = $winner;
            $this->isFinished = true;
        } elseif ($this->board->isFull()) {
            $this->isFinished = true;
        }
    }

    public function setId(GameId $id): void
    {
        $this->id = $id;
    }

    public function restoreState(
        array $boardCells,
        int $currentPlayer,
        bool $isFinished,
        ?int $winner,
        \DateTimeImmutable $createdAt,
    ): void {
        $this->board = new Board($boardCells);
        $this->currentPlayer = new PlayerId($currentPlayer);
        $this->isFinished = $isFinished;
        $this->winner = $winner ? new PlayerId($winner) : null;
        $this->createdAt = $createdAt;
    }
}
