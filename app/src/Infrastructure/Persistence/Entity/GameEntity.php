<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use App\Infrastructure\Persistence\Repository\DoctrineGameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineGameRepository::class)]
#[ORM\Table(name: 'games')]
class GameEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::JSON)]
    private array $board = [];

    #[ORM\Column]
    private int $currentPlayer = 1;

    #[ORM\Column]
    private bool $isFinished = false;

    #[ORM\Column(nullable: true)]
    private ?int $winner = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBoard(): array
    {
        return $this->board;
    }

    public function setBoard(array $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getCurrentPlayer(): int
    {
        return $this->currentPlayer;
    }

    public function setCurrentPlayer(int $currentPlayer): self
    {
        $this->currentPlayer = $currentPlayer;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): self
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(?int $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
