<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class GameId
{
    public function __construct(private ?int $value = null)
    {
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function equals(?GameId $other): bool
    {
        if (null === $other) {
            return false;
        }

        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
