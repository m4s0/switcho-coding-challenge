<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum PlayerId: int
{
    case PLAYER_ONE = 1;
    case PLAYER_TWO = 2;

    public const PLAYER_ONE_SYMBOL = 'X';
    public const PLAYER_TWO_SYMBOL = 'O';

    public function getSymbol(): string
    {
        return match ($this) {
            self::PLAYER_ONE => self::PLAYER_ONE_SYMBOL,
            self::PLAYER_TWO => self::PLAYER_TWO_SYMBOL,
        };
    }

    public function getOpponent(): self
    {
        return match ($this) {
            self::PLAYER_ONE => self::PLAYER_TWO,
            self::PLAYER_TWO => self::PLAYER_ONE,
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
