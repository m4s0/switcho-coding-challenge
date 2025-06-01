<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Serializer;

use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use App\Infrastructure\Serializer\BoardSerializer;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
final class BoardSerializerTest extends TestCase
{
    private BoardSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new BoardSerializer();
    }

    public function testSerialize(): void
    {
        $board = new Board();
        $board->makeMove(Position::fromIndex(0), PlayerId::PLAYER_ONE);
        $board->makeMove(Position::fromIndex(4), PlayerId::PLAYER_TWO);

        $serialized = $this->serializer->serialize($board);

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

        $board = $this->serializer->deserialize($data);

        $this->assertEquals(PlayerId::PLAYER_ONE, $board->getRawCells()[0]);
        $this->assertEquals(PlayerId::PLAYER_TWO, $board->getRawCells()[4]);
        $this->assertEquals(PlayerId::PLAYER_ONE, $board->getCellOccupant(Position::fromIndex(0)));
        $this->assertEquals(PlayerId::PLAYER_TWO, $board->getCellOccupant(Position::fromIndex(4)));
    }
}
