<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

readonly class Position
{
    private function __construct(
        public int $row,
        public int $col,
    ) {
        if ($row < 0 || $row > 2 || $col < 0 || $col > 2) {
            throw new \DomainException('Position must be between 0 and 2');
        }
    }

    public static function create(int $row, int $col): self
    {
        return new self(row: $row, col: $col);
    }

    public static function fromIndex(int $index): self
    {
        if ($index < 0 || $index > 8) {
            throw new \DomainException('Index must be between 0 and 8');
        }

        return new self(row: intdiv($index, 3), col: $index % 3);
    }

    public function toIndex(): int
    {
        return $this->row * 3 + $this->col;
    }

    public function equals(self $other): bool
    {
        return $this->row === $other->row && $this->col === $other->col;
    }
}
