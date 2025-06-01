<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\ValueObject\GameId;

final readonly class StartGameUseCase
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
    ) {
    }

    public function execute(): Game
    {
        $game = new Game(GameId::generate());
        $this->gameRepository->save($game);

        return $game;
    }
}
