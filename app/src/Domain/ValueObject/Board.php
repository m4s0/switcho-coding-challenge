<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

class Board
{
    /**
     * @var array<PlayerId|null>
     */
    private array $cells;

    /**
     * @param array<PlayerId|null> $cells
     */
    private function __construct(array $cells)
    {
        if (9 !== count($cells)) {
            throw new \InvalidArgumentException('Cells must be an array of 9 elements');
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

    public function makeMove(Position $position, PlayerId $playerId): void
    {
        $index = $position->toIndex();
        if (null !== $this->cells[$index]) {
            throw new \DomainException('Position is already occupied');
        }

        $this->cells[$index] = $playerId;
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
        $winningCombinations = [
            [0, 1, 2], // Row
            [3, 4, 5], // Row
            [6, 7, 8], // Row
            [0, 3, 6], // Column
            [1, 4, 7], // Column
            [2, 5, 8], // Column
            [0, 4, 8], // Diagonal
            [2, 4, 6], // Diagonal
        ];

        foreach ($winningCombinations as [$a, $b, $c]) {
            if (null !== $this->cells[$a]
                && $this->cells[$a] === $this->cells[$b]
                && $this->cells[$b] === $this->cells[$c]
            ) {
                return $this->cells[$a];
            }
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
            throw new \DomainException('Position is out of bounds');
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

    public function equals(self $other): bool
    {
        return $this->cells === $other->cells;
    }

    /**
     * @return array<PlayerId|null>
     */
    public static function serialize(Board $board): array
    {
        return array_map(
            static fn (?PlayerId $playerId): ?PlayerId => $playerId ?? null,
            $board->getRawCells()
        );
    }

    /**
     * @param array<PlayerId|null> $data
     */
    public static function deserialize(array $data): Board
    {
        $cells = array_map(
            static fn (?PlayerId $playerId): ?PlayerId => $playerId ?? null,
            $data
        );

        return Board::create($cells);
    }
}
