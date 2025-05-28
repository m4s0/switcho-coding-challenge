<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class Position
{
    public function __construct(private int $value)
    {
        if ($value < 0 || $value > 8) {
            throw new \InvalidArgumentException('Position must be between 0 and 8');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(Position $other): bool
    {
        return $this->value === $other->value;
    }

    public function toRowCol(): array
    {
        return [
            'row' => intdiv($this->value, 3),
            'col' => $this->value % 3,
        ];
    }

    public static function fromRowCol(int $row, int $col): self
    {
        return new self($row * 3 + $col);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
