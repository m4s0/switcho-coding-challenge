<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum PlayerId: int
{
    case PLAYER_ONE = 1;
    case PLAYER_TWO = 2;

    public function getSymbol(): string
    {
        return match ($this) {
            self::PLAYER_ONE => 'X',
            self::PLAYER_TWO => 'O',
        };
    }

    public function getOpponent(): self
    {
        return match ($this) {
            self::PLAYER_ONE => self::PLAYER_TWO,
            self::PLAYER_TWO => self::PLAYER_ONE,
        };
    }
}
