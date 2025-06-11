<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'games')]
class GameEntity
{
    /**
     * @param array<int|null> $board
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::GUID, unique: true)]
        private readonly string $id,
        #[ORM\Column(type: Types::JSON)]
        private array $board,
        #[ORM\Column(type: Types::INTEGER)]
        private int $currentPlayer,
        #[ORM\Column(type: Types::BOOLEAN)]
        private bool $isFinished,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private ?int $winner,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private readonly \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array<int|null>
     */
    public function getBoard(): array
    {
        return $this->board;
    }

    public function getCurrentPlayer(): int
    {
        return $this->currentPlayer;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param array<int> $board
     */
    public function updateFromDomain(
        array $board,
        int $currentPlayer,
        bool $isFinished,
        ?int $winner,
        \DateTimeImmutable $updatedAt,
    ): void {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
        $this->isFinished = $isFinished;
        $this->winner = $winner;
        $this->updatedAt = $updatedAt;
    }
}
