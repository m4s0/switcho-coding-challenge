<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Game;
use App\Domain\ValueObject\GameId;

interface GameRepositoryInterface
{
    public function save(Game $game): void;

    public function findById(GameId $gameId): ?Game;

    public function remove(Game $game): void;
}
