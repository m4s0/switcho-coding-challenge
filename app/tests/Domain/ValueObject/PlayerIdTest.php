<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\PlayerId;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class PlayerIdTest extends TestCase
{
    public function testGetSymbolReturnsCorrectSymbolForPlayers(): void
    {
        $this->assertEquals(PlayerId::PLAYER_ONE_SYMBOL, PlayerId::PLAYER_ONE->getSymbol());
        $this->assertEquals(PlayerId::PLAYER_TWO_SYMBOL, PlayerId::PLAYER_TWO->getSymbol());
    }

    public function testGetOpponentReturnsCorrectOpponent(): void
    {
        $this->assertEquals(PlayerId::PLAYER_TWO, PlayerId::PLAYER_ONE->getOpponent());
        $this->assertEquals(PlayerId::PLAYER_ONE, PlayerId::PLAYER_TWO->getOpponent());
    }

    public function testEquals(): void
    {
        $this->assertTrue(PlayerId::PLAYER_ONE->equals(PlayerId::PLAYER_ONE));
        $this->assertTrue(PlayerId::PLAYER_TWO->equals(PlayerId::PLAYER_TWO));
        $this->assertFalse(PlayerId::PLAYER_ONE->equals(PlayerId::PLAYER_TWO));
        $this->assertFalse(PlayerId::PLAYER_TWO->equals(PlayerId::PLAYER_ONE));
    }
}
