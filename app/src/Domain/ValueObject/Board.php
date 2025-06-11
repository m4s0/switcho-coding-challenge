<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\DomainException;

class Board
{
    public const GRID_SIZE = 3;
    public const TOTAL_CELLS = self::GRID_SIZE * self::GRID_SIZE;

    /**
     * @var array<PlayerId|null>
     */
    private array $cells;

    /**
     * @param array<PlayerId|null> $cells
     */
    private function __construct(array $cells)
    {
        if (self::TOTAL_CELLS !== count($cells)) {
            throw new DomainException('Cells must be an array of '.self::TOTAL_CELLS.' elements');
        }

        $this->cells = $cells;
    }

    /**
     * @param array<PlayerId|null> $cells
     */
    public static function create(array $cells): self
    {
        return new self($cells);
    }

    public static function createEmpty(): self
    {
        return new self(array_fill(0, 9, null));
    }

    /**
     * @param array<PlayerId|null> $cells
     */
    public static function duplicate(array $cells): self
    {
        return new self($cells);
    }

    public function makeMove(Position $position, PlayerId $playerId): void
    {
        if (!$this->isPositionEmpty($position)) {
            throw new DomainException('Position is already occupied');
        }

        $this->cells[$position->toIndex()] = $playerId;
    }

    public function isPositionEmpty(Position $position): bool
    {
        return null === $this->cells[$position->toIndex()];
    }

    public function isFull(): bool
    {
        return !in_array(null, $this->cells, true);
    }

    public function hasWinner(): bool
    {
        return null !== $this->getWinner();
    }

    public function getWinner(): ?PlayerId
    {
        // Check rows
        for ($i = 0; $i < self::GRID_SIZE; ++$i) {
            $offset = $i * self::GRID_SIZE;
            if (null !== $this->cells[$offset]
                && $this->cells[$offset] === $this->cells[$offset + 1]
                && $this->cells[$offset] === $this->cells[$offset + 2]
            ) {
                return $this->cells[$offset];
            }
        }

        // Check columns
        for ($i = 0; $i < self::GRID_SIZE; ++$i) {
            if (null !== $this->cells[$i]
                && $this->cells[$i] === $this->cells[$i + self::GRID_SIZE]
                && $this->cells[$i] === $this->cells[$i + (2 * self::GRID_SIZE)]
            ) {
                return $this->cells[$i];
            }
        }

        // Check main diagonal
        if (null !== $this->cells[0]
            && $this->cells[0] === $this->cells[4]
            && $this->cells[0] === $this->cells[8]
        ) {
            return $this->cells[0];
        }

        // Check anti-diagonal
        if (null !== $this->cells[2]
            && $this->cells[2] === $this->cells[4]
            && $this->cells[2] === $this->cells[6]
        ) {
            return $this->cells[2];
        }

        return null;
    }

    /**
     * @return array<int|null>
     */
    public function getCells(): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?int => $playerId?->value,
            $this->cells
        );
    }

    /**
     * @return array<string|null>
     */
    public function getCellsPlayerName(): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?string => $playerId?->name,
            $this->cells
        );
    }

    /**
     * @return array<string|null>
     */
    public function getCellsPlayerSymbols(): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?string => $playerId?->getSymbol(),
            $this->cells
        );
    }

    /**
     * @return array<PlayerId>
     */
    public function getRawCells(): array
    {
        return $this->cells;
    }

    public function getCellOccupant(Position $fromIndex): PlayerId
    {
        $index = $fromIndex->toIndex();
        if (!isset($this->cells[$index])) {
            throw new DomainException('Position is out of bounds');
        }

        return $this->cells[$index];
    }

    public function getCellOccupantName(Position $fromIndex): string
    {
        $occupant = $this->getCellOccupant($fromIndex);

        return $occupant->name;
    }

    public function getCellOccupantValue(Position $fromIndex): int
    {
        $occupant = $this->getCellOccupant($fromIndex);

        return $occupant->value;
    }

    /**
     * @return array<Position>
     */
    public function getEmptyCells(): array
    {
        $emptyCells = [];
        foreach ($this->cells as $index => $cell) {
            if (null === $cell) {
                $emptyCells[] = Position::fromIndex($index);
            }
        }

        return $emptyCells;
    }

    public function equals(self $other): bool
    {
        return $this->cells === $other->cells;
    }

    /**
     * @return array<int|null>
     */
    public static function serialize(Board $board): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?int => $playerId->value ?? null,
            $board->getRawCells()
        );
    }

    /**
     * @param array<int|null> $data
     */
    public static function deserialize(array $data): Board
    {
        $cells = array_map(
            static fn (?int $playerId): ?PlayerId => $playerId ? PlayerId::from($playerId) : null,
            $data
        );

        return Board::create($cells);
    }
}
