<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\UseCase\GetGameStatusUseCase;
use App\Domain\Entity\Game;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Infrastructure\Persistence\Entity\GameEntity;
use App\Tests\Helper\DropAndRecreateDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Integration
 */
class GetGameStatusUseCaseTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private GetGameStatusUseCase $getGameStatusUseCase;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->getGameStatusUseCase = static::getContainer()->get(GetGameStatusUseCase::class);
        DropAndRecreateDatabase::execute($this->entityManager);
    }

    public function testExecuteSuccessfully(): void
    {
        $gameId = GameId::generate();
        $gameEntity = new GameEntity(
            $gameId->toString(),
            board: Board::createEmpty()->getCells(),
            currentPlayer: PlayerId::PLAYER_ONE->value,
            isFinished: false,
            winner: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($gameEntity);
        $this->entityManager->flush();

        $result = $this->getGameStatusUseCase->execute($gameId->toString());
        $game = Game::fromEntity($gameEntity);
        $this->assertTrue($result->equals($game));
    }

    public function testExecuteThrowsExceptionWhenGameNotFound(): void
    {
        $gameId = GameId::fromString('123e4567-e89b-12d3-a456-426614174001');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Game not found');

        $this->getGameStatusUseCase->execute($gameId->toString());
    }
}
