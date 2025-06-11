<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Game;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;

readonly class MakeMoveUseCase
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
    ) {
    }

    public function execute(string $gameId, int $playerId, int $row, int $col): Game
    {
        $game = $this->gameRepository->findById(GameId::fromString($gameId));
        if (null === $game) {
            throw new NotFoundException('Game not found');
        }

        $player = PlayerId::from($playerId);
        $movePosition = Position::create($row, $col);
        $game->makeMove($player, $movePosition);
        $this->gameRepository->save($game);

        return $game;
    }
}
