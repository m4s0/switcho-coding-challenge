<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Game;

final readonly class GameStateDTO
{
    public function __construct(
        public string $gameId,
        public array $board,
        public bool $isFinished,
        public ?int $winner,
        public string $status,
        public int $currentPlayer,
        public bool $isDraw,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromGame(Game $game): self
    {
        return new self(
            gameId: $game->getId()->toString(),
            board: $game->getBoard()->getCells(),
            isFinished: $game->isFinished(),
            winner: $game->getWinner()?->value,
            status: $game->getStatus(),
            currentPlayer: $game->getCurrentPlayer()->value,
            isDraw: $game->isDraw(),
            createdAt: $game->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $game->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
