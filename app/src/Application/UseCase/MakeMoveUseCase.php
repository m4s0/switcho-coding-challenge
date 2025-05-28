<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\GameStateDTO;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;

class MakeMoveUseCase
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
    ) {
    }

    public function execute(int $gameId, int $playerId, int $position): GameStateDTO
    {
        $game = $this->gameRepository->findById(new GameId($gameId));

        if (!$game) {
            throw new \DomainException('Game not found');
        }

        $game->makeMove(new PlayerId($playerId), new Position($position));
        $this->gameRepository->save($game);

        return GameStateDTO::fromGame($game);
    }
}
