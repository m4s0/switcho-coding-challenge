<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
final class PositionTest extends TestCase
{
    public function testCreateValidPosition(): void
    {
        $position = new Position(1, 1);

        $this->assertSame(1, $position->row);
        $this->assertSame(1, $position->col);
    }

    /**
     * @dataProvider invalidPositionProvider
     */
    public function testThrowsExceptionForInvalidPosition(int $row, int $col): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Position must be between 0 and 2');

        new Position($row, $col);
    }

    public function testCreateFromValidIndex(): void
    {
        $position = Position::fromIndex(4);

        $this->assertSame(1, $position->row);
        $this->assertSame(1, $position->col);
    }

    public function testThrowsExceptionForInvalidIndex(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Index must be between 0 and 8');

        Position::fromIndex(9);
    }

    public function testToIndexConversion(): void
    {
        $position = new Position(1, 1);

        $this->assertSame(4, $position->toIndex());
    }

    public function testEquals(): void
    {
        $position1 = new Position(1, 1);
        $position2 = new Position(1, 1);
        $position3 = new Position(0, 0);

        $this->assertTrue($position1->equals($position2));
        $this->assertFalse($position1->equals($position3));
    }

    public function invalidPositionProvider(): array
    {
        return [
            'negative row' => [-1, 0],
            'negative col' => [0, -1],
            'row too large' => [3, 0],
            'col too large' => [0, 3],
        ];
    }
}
