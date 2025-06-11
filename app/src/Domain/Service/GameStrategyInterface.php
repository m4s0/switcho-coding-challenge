<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;

interface GameStrategyInterface
{
    /**
     * @return array<Position>
     */
    public function findOpponentWinningMoves(Board $board, PlayerId $currentPlayer): array;

    public function findBestMove(Board $board, PlayerId $currentPlayer): ?Position;
}
