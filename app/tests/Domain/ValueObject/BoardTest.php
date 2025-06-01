<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
final class BoardTest extends TestCase
{
    private Board $board;

    protected function setUp(): void
    {
        $this->board = new Board();
    }

    public function testInitialBoardIsEmpty(): void
    {
        self::assertCount(9, $this->board->getCells());
        self::assertEmpty(array_filter($this->board->getCells()));
    }

    public function testMakeValidMove(): void
    {
        $position = new Position(0, 0);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);

        self::assertEquals(PlayerId::PLAYER_ONE->value, $this->board->getCellOccupantValue($position));
        self::assertFalse($this->board->isPositionEmpty($position));
    }

    public function testMakeMoveOnOccupiedPosition(): void
    {
        $position = new Position(0, 0);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Position is already occupied');

        $this->board->makeMove($position, PlayerId::PLAYER_TWO);
    }

    public function testHasWinnerHorizontal(): void
    {
        $this->board->makeMove(Position::fromIndex(0), PlayerId::PLAYER_ONE);
        $this->board->makeMove(Position::fromIndex(1), PlayerId::PLAYER_ONE);
        $this->board->makeMove(Position::fromIndex(2), PlayerId::PLAYER_ONE);

        $this->assertTrue($this->board->hasWinner());
        $this->assertSame(PlayerId::PLAYER_ONE, $this->board->getWinner());
    }

    public function testHasWinnerVertical(): void
    {
        $this->board->makeMove(Position::fromIndex(0), PlayerId::PLAYER_TWO);
        $this->board->makeMove(Position::fromIndex(3), PlayerId::PLAYER_TWO);
        $this->board->makeMove(Position::fromIndex(6), PlayerId::PLAYER_TWO);

        $this->assertTrue($this->board->hasWinner());
        $this->assertSame(PlayerId::PLAYER_TWO, $this->board->getWinner());
    }

    public function testIsFull(): void
    {
        for ($i = 0; $i < 9; ++$i) {
            $this->board->makeMove(
                Position::fromIndex($i),
                0 === $i % 2 ? PlayerId::PLAYER_ONE : PlayerId::PLAYER_TWO
            );
        }

        $this->assertTrue($this->board->isFull());
    }

    public function testThrowsExceptionWhenMakingMoveOnOccupiedPosition(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Position is already occupied');

        $position = new Position(0, 0);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);
        $this->board->makeMove($position, PlayerId::PLAYER_TWO);
    }

    public function testDetectsWinningCombination(): void
    {
        $this->board->makeMove(new Position(0, 0), PlayerId::PLAYER_ONE);
        $this->board->makeMove(new Position(0, 1), PlayerId::PLAYER_ONE);
        $this->board->makeMove(new Position(0, 2), PlayerId::PLAYER_ONE);

        self::assertTrue($this->board->hasWinner());
        self::assertEquals(PlayerId::PLAYER_ONE, $this->board->getWinner());
    }

    public function testGetCellsReturnsCorrectFormat(): void
    {
        $position = new Position(1, 1);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);

        self::assertIsArray($this->board->getCells());
        self::assertEquals(PlayerId::PLAYER_ONE->value, $this->board->getCells()[4]);
        self::assertEquals(PlayerId::PLAYER_ONE->name, $this->board->getCellsPlayerName()[4]);
        self::assertEquals(PlayerId::PLAYER_ONE->getSymbol(), $this->board->getCellsPlayerSymbols()[4]);
    }

    public function testThrowsExceptionForInvalidPosition(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Position is out of bounds');

        $this->board->getCellOccupant(new Position(2, 2));
    }
}
