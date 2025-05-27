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
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::JSON)]
    private array $board = [];

    #[ORM\Column]
    private ?int $currentPlayer = null;

    #[ORM\Column]
    private bool $isFinished = false;

    #[ORM\Column(nullable: true)]
    private ?int $winner = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null; // 'ongoing', 'won', 'draw'

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        //        $this->board = array_fill(0, 9, null); // 3x3 board represented as 1D array
        //        $this->createdAt = new \DateTimeImmutable();

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

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): static
    {
        $this->isFinished = $isFinished;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getBoardAs2D(): array
    {
        return [
            [$this->board[0], $this->board[1], $this->board[2]],
            [$this->board[3], $this->board[4], $this->board[5]],
            [$this->board[6], $this->board[7], $this->board[8]],
        ];
    }
}

// migrations/VersionXXXXXXXXXXXXXX.php (example migration)

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create game table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE game (
            id INT AUTO_INCREMENT NOT NULL, 
            board JSON NOT NULL, 
            current_player INT NOT NULL, 
            is_finished TINYINT(1) NOT NULL, 
            winner INT DEFAULT NULL, 
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE game');
    }
}
