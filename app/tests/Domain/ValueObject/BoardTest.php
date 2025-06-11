<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\Exception\DomainException;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class BoardTest extends TestCase
{
    private Board $board;

    protected function setUp(): void
    {
        $this->board = Board::createEmpty();
    }

    public function testInitialBoardIsEmpty(): void
    {
        self::assertCount(9, $this->board->getCells());
        self::assertEmpty(array_filter($this->board->getCells()));
    }

    public function testMakeValidMove(): void
    {
        $position = Position::create(0, 0);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);

        self::assertEquals(PlayerId::PLAYER_ONE->value, $this->board->getCellOccupantValue($position));
        self::assertFalse($this->board->isPositionEmpty($position));
    }

    public function testMakeMoveOnOccupiedPosition(): void
    {
        $position = Position::create(0, 0);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);

        $this->expectException(DomainException::class);
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
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Position is already occupied');

        $position = Position::create(0, 0);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);
        $this->board->makeMove($position, PlayerId::PLAYER_TWO);
    }

    public function testDetectsWinningCombination(): void
    {
        $this->board->makeMove(Position::create(0, 0), PlayerId::PLAYER_ONE);
        $this->board->makeMove(Position::create(0, 1), PlayerId::PLAYER_ONE);
        $this->board->makeMove(Position::create(0, 2), PlayerId::PLAYER_ONE);

        self::assertTrue($this->board->hasWinner());
        self::assertEquals(PlayerId::PLAYER_ONE, $this->board->getWinner());
    }

    public function testGetCellsReturnsCorrectFormat(): void
    {
        $position = Position::create(1, 1);
        $this->board->makeMove($position, PlayerId::PLAYER_ONE);

        self::assertEquals(PlayerId::PLAYER_ONE->value, $this->board->getCells()[4]);
        self::assertEquals(PlayerId::PLAYER_ONE->name, $this->board->getCellsPlayerName()[4]);
        self::assertEquals(PlayerId::PLAYER_ONE->getSymbol(), $this->board->getCellsPlayerSymbols()[4]);
    }

    public function testThrowsExceptionForInvalidPosition(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Position is out of bounds');

        $this->board->getCellOccupant(Position::create(2, 2));
    }

    public function testSerialize(): void
    {
        $board = Board::createEmpty();
        $board->makeMove(Position::fromIndex(0), PlayerId::PLAYER_ONE);
        $board->makeMove(Position::fromIndex(4), PlayerId::PLAYER_TWO);

        $serialized = Board::serialize($board);

        $expected = [
            PlayerId::PLAYER_ONE->value,
            null,
            null,
            null,
            PlayerId::PLAYER_TWO->value,
            null,
            null,
            null,
            null,
        ];

        $this->assertEquals($expected, $serialized);
    }

    public function testDeserialize(): void
    {
        $data = [
            PlayerId::PLAYER_ONE->value,
            null,
            null,
            null,
            PlayerId::PLAYER_TWO->value,
            null,
            null,
            null,
            null,
        ];

        $board = Board::deserialize($data);

        $this->assertEquals(PlayerId::PLAYER_ONE, $board->getRawCells()[0]);
        $this->assertEquals(PlayerId::PLAYER_TWO, $board->getRawCells()[4]);
        $this->assertEquals(PlayerId::PLAYER_ONE, $board->getCellOccupant(Position::fromIndex(0)));
        $this->assertEquals(PlayerId::PLAYER_TWO, $board->getCellOccupant(Position::fromIndex(4)));
    }

    public function testEquals(): void
    {
        $board1 = Board::createEmpty();
        $board2 = Board::createEmpty();

        self::assertTrue($board1->equals($board2));

        $board1->makeMove(Position::fromIndex(0), PlayerId::PLAYER_ONE);
        self::assertFalse($board1->equals($board2));

        $board2->makeMove(Position::fromIndex(0), PlayerId::PLAYER_ONE);
        self::assertTrue($board1->equals($board2));
    }
}
