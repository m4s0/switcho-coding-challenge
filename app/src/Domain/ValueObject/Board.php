<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class Board
{
    private array $cells;

    public function __construct(?array $cells = null)
    {
        $this->cells = $cells ?? array_fill(0, 9, null);
    }

    public function makeMove(Position $position, PlayerId $playerId): void
    {
        $index = $position->toIndex();
        if (null !== $this->cells[$index]) {
            throw new \DomainException('Position is already occupied');
        }

        $this->cells[$index] = $playerId;
    }

    public function isPositionEmpty(Position $position): bool
    {
        return null === $this->cells[$position->toIndex()];
    }

    public function isFull(): bool
    {
        return !in_array(null, $this->cells, true);
    }

    public function hasWinner(): bool
    {
        return null !== $this->getWinner();
    }

    public function getWinner(): ?PlayerId
    {
        $winningCombinations = [
            [0, 1, 2], // Row
            [3, 4, 5], // Row
            [6, 7, 8], // Row
            [0, 3, 6], // Column
            [1, 4, 7], // Column
            [2, 5, 8], // Column
            [0, 4, 8], // Diagonal
            [2, 4, 6], // Diagonal
        ];

        foreach ($winningCombinations as [$a, $b, $c]) {
            if (null !== $this->cells[$a]
                && $this->cells[$a] === $this->cells[$b]
                && $this->cells[$b] === $this->cells[$c]
            ) {
                return $this->cells[$a];
            }
        }

        return null;
    }

    public function getCells(): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?int => $playerId?->value,
            $this->cells
        );
    }

    public function getCellsPlayerName(): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?string => $playerId?->name,
            $this->cells
        );
    }

    public function getCellsPlayerSymbols(): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?string => $playerId?->getSymbol(),
            $this->cells
        );
    }

    public function getRawCells(): array
    {
        return $this->cells;
    }

    public function getCellOccupant(Position $fromIndex)
    {
        $index = $fromIndex->toIndex();
        if (!isset($this->cells[$index])) {
            throw new \DomainException('Position is out of bounds');
        }

        return $this->cells[$index];
    }

    public function getCellOccupantName(Position $fromIndex): ?string
    {
        $occupant = $this->getCellOccupant($fromIndex);

        return $occupant?->name;
    }

    public function getCellOccupantValue(Position $fromIndex): ?int
    {
        $occupant = $this->getCellOccupant($fromIndex);

        return $occupant?->value;
    }
}
