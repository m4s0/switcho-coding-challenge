<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class GameId
{
    public function __construct(
        private Uuid $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function toString(): string
    {
        return $this->value->toRfc4122();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }
}
