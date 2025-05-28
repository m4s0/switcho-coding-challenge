<?php

declare(strict_types=1);

namespace App\Infrastructure\Serializer;

use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;

final class BoardSerializer
{
    public function serialize(Board $board): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?int => $playerId?->value,
            $board->getRawCells()
        );
    }

    public function deserialize(array $data): Board
    {
        $cells = array_map(
            static fn (?int $playerId): ?PlayerId => null !== $playerId ? PlayerId::from($playerId) : null,
            $data
        );

        return new Board($cells);
    }
}
