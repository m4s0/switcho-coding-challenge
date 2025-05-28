<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\GameStateDTO;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\GameId;

class GetGameStatusUseCase
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
    ) {
    }

    public function execute(int $gameId): GameStateDTO
    {
        $game = $this->gameRepository->findById(new GameId($gameId));

        if (!$game) {
            throw new \DomainException('Game not found');
        }

        return GameStateDTO::fromGame($game);
    }
}
