<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\Repository;

use App\Domain\Entity\Game;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use App\Infrastructure\Persistence\Repository\DoctrineGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Integration
 */
final class DoctrineGameRepositoryTest extends KernelTestCase
{
    private DoctrineGameRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(DoctrineGameRepository::class);
        $this->dropAndRecreateDatabase($this->entityManager, 'postgres');
    }

    protected function dropAndRecreateDatabase(EntityManagerInterface $entityManager, string $type): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $tool = new SchemaTool($entityManager);

            $connection = $entityManager->getConnection();
            $connection->executeQuery('DROP SCHEMA public CASCADE');
            $connection->executeQuery('CREATE SCHEMA public');
            $connection->executeQuery('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
            $connection->close();

            $tool->createSchema($metadata);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }

    public function testSaveAndFindGame(): void
    {
        $gameId = GameId::generate();
        $game = new Game($gameId);

        $game->makeMove(PlayerId::PLAYER_ONE, Position::fromIndex(0));
        $game->makeMove(PlayerId::PLAYER_TWO, Position::fromIndex(4));

        $this->repository->save($game);

        $foundGame = $this->repository->findById($gameId);

        $this->assertNotNull($foundGame);
        $this->assertEquals($game->getId(), $foundGame->getId());
        $this->assertEquals($game->getCurrentPlayer(), $foundGame->getCurrentPlayer());
        $this->assertEquals($game->isFinished(), $foundGame->isFinished());
        $this->assertEquals($game->getWinner(), $foundGame->getWinner());
    }

    public function testDeleteGame(): void
    {
        $gameId = GameId::generate();
        $game = new Game($gameId);

        $this->repository->save($game);
        $this->repository->delete($gameId);

        $this->assertNull($this->repository->findById($gameId));
    }
}
