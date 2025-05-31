<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\UseCase\MakeMoveUseCase;
use App\Domain\Entity\Game;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use App\Infrastructure\Persistence\Entity\GameEntity;
use App\Tests\Helper\DropAndRecreateDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Integration
 */
class MakeMoveUseCaseTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private MakeMoveUseCase $makeMoveUseCase;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->makeMoveUseCase = static::getContainer()->get(MakeMoveUseCase::class);
        DropAndRecreateDatabase::execute($this->entityManager);
    }

    public function testExecuteSuccessfully(): void
    {
        $gameId = GameId::generate();
        $gameEntity = new GameEntity(
            $gameId->toString(),
            board: Board::createEmpty()->getRawCells(),
            currentPlayer: PlayerId::PLAYER_ONE->value,
            isFinished: false,
            winner: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($gameEntity);
        $this->entityManager->flush();

        $result = $this->makeMoveUseCase->execute(
            $gameId->toString(),
            PlayerId::PLAYER_ONE->value,
            0,
            0
        );

        $updatedGameEntity = $this->entityManager->find(GameEntity::class, $gameId->toString());
        $expectedGame = Game::fromEntity($updatedGameEntity);

        self::assertTrue($result->equals($expectedGame));
        self::assertSame(PlayerId::PLAYER_TWO->value, $result->getCurrentPlayer()->value);
        self::assertSame(PlayerId::PLAYER_ONE->value, $result->getBoard()->getCellOccupantValue(Position::create(0, 0)));
    }

    public function testExecuteThrowsExceptionWhenGameNotFound(): void
    {
        $gameId = GameId::fromString('123e4567-e89b-12d3-a456-426614174001');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Game not found');

        $this->makeMoveUseCase->execute(
            $gameId->toString(),
            PlayerId::PLAYER_ONE->value,
            0,
            0
        );
    }
}
