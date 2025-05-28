<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Game;

final readonly class GameStateDTO
{
    public function __construct(
        public int $gameId,
        public array $board2D,
        public array $board1D,
        public int $currentPlayer,
        public bool $isFinished,
        public ?int $winner,
        public bool $isDraw,
        public string $createdAt,
    ) {
    }

    public static function fromGame(Game $game): self
    {
        return new self(
            gameId: $game->getId()->getValue(),
            board2D: $game->getBoard()->to2DArray(),
            board1D: $game->getBoard()->toArray(),
            currentPlayer: $game->getCurrentPlayer()->getValue(),
            isFinished: $game->isFinished(),
            winner: $game->getWinner()?->getValue(),
            isDraw: $game->isDraw(),
            createdAt: $game->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'game_id' => $this->gameId,
            'board' => $this->board2D,
            'board_1d' => $this->board1D,
            'current_player' => $this->currentPlayer,
            'is_finished' => $this->isFinished,
            'winner' => $this->winner,
            'is_draw' => $this->isDraw,
            'created_at' => $this->createdAt,
        ];
    }
}
