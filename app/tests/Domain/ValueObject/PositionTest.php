<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class PositionTest extends TestCase
{
    public function testCreateValidPosition(): void
    {
        $position = Position::create(1, 1);

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

        Position::create($row, $col);
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
        $position = Position::create(1, 1);

        $this->assertSame(4, $position->toIndex());
    }

    public function testEquals(): void
    {
        $position1 = Position::create(1, 1);
        $position2 = Position::create(1, 1);
        $position3 = Position::create(0, 0);

        $this->assertTrue($position1->equals($position2));
        $this->assertFalse($position1->equals($position3));
    }

    /**
     * @return array<string, list<int>>
     */
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
