<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class PlayerId
{
    public function __construct(private int $value)
    {
        if (!in_array($value, [1, 2], true)) {
            throw new \InvalidArgumentException('Player ID must be 1 or 2');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(PlayerId $other): bool
    {
        return $this->value === $other->value;
    }

    public function getOpponent(): PlayerId
    {
        return new self(1 === $this->value ? 2 : 1);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
