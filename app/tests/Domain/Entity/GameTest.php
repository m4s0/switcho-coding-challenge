<?php

declare(strict_types=1);

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Game;
use App\Domain\ValueObject\GameId;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class GameTest extends TestCase
{
    private Game $game;
    private GameId $gameId;

    protected function setUp(): void
    {
        $this->gameId = GameId::generate();
        $this->game = Game::create($this->gameId);
    }

    public function testInitialGameState(): void
    {
        self::assertEquals($this->gameId, $this->game->getId());
        self::assertEquals(PlayerId::PLAYER_ONE, $this->game->getCurrentPlayer());
        self::assertFalse($this->game->isFinished());
        self::assertNull($this->game->getWinner());
        self::assertFalse($this->game->isDraw());
        self::assertEquals(Game::IN_PROGRESS, $this->game->getStatus());
    }

    public function testMakeValidMove(): void
    {
        $position = Position::create(0, 0);
        $this->game->makeMove(PlayerId::PLAYER_ONE, $position);

        self::assertEquals(PlayerId::PLAYER_TWO, $this->game->getCurrentPlayer());
        self::assertFalse($this->game->isFinished());
    }

    public function testThrowsExceptionWhenMakingMoveOnFinishedGame(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Game is already finished');

        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 0));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(1, 0));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 1));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(1, 1));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 2));

        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(2, 2));
    }

    public function testThrowsExceptionWhenMakingMoveOutOfTurn(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('It is not your turn');

        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(0, 0));
    }

    public function testThrowsExceptionWhenMakingMoveOnOccupiedPosition(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Position is already occupied');

        $position = Position::create(0, 0);
        $this->game->makeMove(PlayerId::PLAYER_ONE, $position);
        $this->game->makeMove(PlayerId::PLAYER_TWO, $position);
    }

    public function testGameEndsWithWinner(): void
    {
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 0));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(1, 0));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 1));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(1, 1));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 2));

        self::assertTrue($this->game->isFinished());
        self::assertEquals(PlayerId::PLAYER_ONE, $this->game->getWinner());
        self::assertEquals(Game::WON, $this->game->getStatus());
    }

    public function testGameEndsInDraw(): void
    {
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 0));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(0, 1));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(0, 2));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(1, 1));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(1, 0));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(1, 2));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(2, 1));
        $this->game->makeMove(PlayerId::PLAYER_TWO, Position::create(2, 0));
        $this->game->makeMove(PlayerId::PLAYER_ONE, Position::create(2, 2));

        self::assertTrue($this->game->isFinished());
        self::assertNull($this->game->getWinner());
        self::assertTrue($this->game->isDraw());
        self::assertEquals(Game::DRAW, $this->game->getStatus());
    }

    public function testEquals(): void
    {
        $gameId = GameId::generate();
        $game1 = Game::create($gameId);
        $game2 = Game::create($gameId);
        $game3 = Game::create(GameId::generate());

        self::assertTrue($game1->equals($game2));
        self::assertTrue($game2->equals($game1));
        self::assertFalse($game1->equals($game3));
        self::assertFalse($game2->equals($game3));
    }
}
