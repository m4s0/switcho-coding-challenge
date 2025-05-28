<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class Board
{
    private array $cells;

    public function __construct(array $cells = [])
    {
        $this->cells = $cells ?: array_fill(0, 9, null);

        if (9 !== count($this->cells)) {
            throw new \InvalidArgumentException('Board must have exactly 9 cells');
        }
    }

    public function makeMove(Position $position, PlayerId $playerId): self
    {
        if ($this->isPositionTaken($position)) {
            throw new \DomainException('Position is already taken');
        }

        $newCells = $this->cells;
        $newCells[$position->getValue()] = $playerId->getValue();

        return new self($newCells);
    }

    public function isPositionTaken(Position $position): bool
    {
        return null !== $this->cells[$position->getValue()];
    }

    public function getPlayerAt(Position $position): ?PlayerId
    {
        $value = $this->cells[$position->getValue()];

        return $value ? new PlayerId($value) : null;
    }

    public function isFull(): bool
    {
        return !in_array(null, $this->cells, true);
    }

    public function isEmpty(): bool
    {
        return [] === array_filter($this->cells);
    }

    public function getWinner(): ?PlayerId
    {
        $winningCombinations = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8], // rows
            [0, 3, 6], [1, 4, 7], [2, 5, 8], // columns
            [0, 4, 8], [2, 4, 6],             // diagonals
        ];

        foreach ($winningCombinations as $combination) {
            [$a, $b, $c] = $combination;

            if (null !== $this->cells[$a]
                && $this->cells[$a] === $this->cells[$b]
                && $this->cells[$b] === $this->cells[$c]) {
                return new PlayerId($this->cells[$a]);
            }
        }

        return null;
    }

    public function toArray(): array
    {
        return $this->cells;
    }

    public function to2DArray(): array
    {
        return [
            [$this->cells[0], $this->cells[1], $this->cells[2]],
            [$this->cells[3], $this->cells[4], $this->cells[5]],
            [$this->cells[6], $this->cells[7], $this->cells[8]],
        ];
    }
}
