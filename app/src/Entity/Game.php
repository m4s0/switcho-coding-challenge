<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[ORM\Column(type: Types::JSON)]
    private array $board;

    #[ORM\Column]
    private ?int $currentPlayer;

    #[ORM\Column(nullable: true)]
    private ?int $winner = null;

    #[ORM\Column(length: 20)]
    private ?string $status; // 'ongoing', 'won', 'draw'

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->board = array_fill(0, 3, array_fill(0, 3, null));
        $this->currentPlayer = 1;
        $this->status = 'ongoing';
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters and Setters ---

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getBoard(): array
    {
        return $this->board;
    }

    public function setBoard(array $board): static
    {
        $this->board = $board;

        return $this;
    }

    public function getCurrentPlayer(): ?int
    {
        return $this->currentPlayer;
    }

    public function setCurrentPlayer(int $currentPlayer): static
    {
        $this->currentPlayer = $currentPlayer;

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(?int $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    // --- Game Logic ---

    public function isMoveValid(int $row, int $col): bool
    {
        return 'ongoing' === $this->status
            && isset($this->board[$row][$col])
            && null === $this->board[$row][$col];
    }

    public function makeMove(int $player, int $row, int $col): bool
    {
        if ($player !== $this->currentPlayer || !$this->isMoveValid($row, $col)) {
            return false;
        }

        $newBoard = $this->board;
        $newBoard[$row][$col] = $player;
        $this->board = $newBoard; // Trigger change for Doctrine JSON type

        if ($this->checkWin($player, $row, $col)) {
            $this->winner = $player;
            $this->status = 'won';
        } elseif ($this->checkDraw()) {
            $this->status = 'draw';
        } else {
            $this->currentPlayer = (1 === $player) ? 2 : 1;
        }

        return true;
    }

    private function checkWin(int $player, int $row, int $col): bool
    {
        $board = $this->board;

        // Check row
        if ($board[$row][0] === $player && $board[$row][1] === $player && $board[$row][2] === $player) {
            return true;
        }

        // Check column
        if ($board[0][$col] === $player && $board[1][$col] === $player && $board[2][$col] === $player) {
            return true;
        }

        // Check diagonals
        if ($row === $col && $board[0][0] === $player && $board[1][1] === $player && $board[2][2] === $player) {
            return true;
        }
        if (2 === $row + $col && $board[0][2] === $player && $board[1][1] === $player && $board[2][0] === $player) {
            return true;
        }

        return false;
    }

    private function checkDraw(): bool
    {
        foreach ($this->board as $row) {
            foreach ($row as $cell) {
                if (null === $cell) {
                    return false; // Found an empty cell, not a draw
                }
            }
        }

        return 'ongoing' === $this->status; // Only a draw if no one has won yet
    }
}
