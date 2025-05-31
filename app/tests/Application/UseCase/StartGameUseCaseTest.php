<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\UseCase\StartGameUseCase;
use App\Domain\Entity\Game;
use App\Infrastructure\Persistence\Entity\GameEntity;
use App\Tests\Helper\DropAndRecreateDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Integration
 */
class StartGameUseCaseTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private StartGameUseCase $startGameUseCase;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->startGameUseCase = static::getContainer()->get(StartGameUseCase::class);
        DropAndRecreateDatabase::execute($this->entityManager);
    }

    public function testExecuteSuccessfully(): void
    {
        $game = $this->startGameUseCase->execute();

        $gameEntity = $this->entityManager->find(GameEntity::class, $game->getId()->toString());
        self::assertNotNull($gameEntity);

        $persistedGame = Game::fromEntity($gameEntity);
        self::assertTrue($game->equals($persistedGame));
        self::assertFalse($game->isFinished());
        self::assertNull($game->getWinner());
    }
}
