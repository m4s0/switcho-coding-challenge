<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\GameId;

final readonly class GetGameStatusUseCase
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
    ) {
    }

    public function execute(string $gameId): Game
    {
        $game = $this->gameRepository->findById(GameId::fromString($gameId));
        if (null === $game) {
            throw new \InvalidArgumentException('Game not found');
        }

        return $game;
    }
}
