<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\DomainException;

readonly class Position
{
    private function __construct(
        public int $row,
        public int $col,
    ) {
        if ($row < 0 || $row > Board::GRID_SIZE - 1 || $col < 0 || $col > Board::GRID_SIZE - 1) {
            throw new DomainException('Position must be between 0 and '.Board::GRID_SIZE - 1);
        }
    }

    public static function create(int $row, int $col): self
    {
        return new self($row, $col);
    }

    public static function fromIndex(int $index): self
    {
        if ($index < 0 || $index > Board::TOTAL_CELLS - 1) {
            throw new DomainException('Index must be between 0 and 8');
        }

        return new self(intdiv($index, Board::GRID_SIZE), $index % Board::GRID_SIZE);
    }

    public function toIndex(): int
    {
        return $this->row * Board::GRID_SIZE + $this->col;
    }

    public function equals(self $other): bool
    {
        return $this->row === $other->row && $this->col === $other->col;
    }
}
