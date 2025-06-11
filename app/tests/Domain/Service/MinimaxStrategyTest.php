<?php

declare(strict_types=1);

namespace App\Tests\Domain\Service;

use App\Domain\Service\MinimaxStrategy;
use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
final class MinimaxStrategyTest extends TestCase
{
    /**
     * @param array<int|null> $boardData
     * @param Position[]      $expectedMoves
     *
     * @dataProvider findOpponentWinningMovesProvider
     */
    public function testFindOpponentWinningMoves(array $boardData, PlayerId $playerId, array $expectedMoves): void
    {
        $board = empty($boardData) ? Board::createEmpty() : Board::deserialize($boardData);

        $minimaxStrategy = new MinimaxStrategy();
        $winningMoves = $minimaxStrategy->findOpponentWinningMoves($board, $playerId);

        if (empty($expectedMoves)) {
            $this->assertEmpty($winningMoves);
        } else {
            $this->assertEquals($expectedMoves, $winningMoves);
        }
    }

    /**
     * @param array<int|null> $boardData
     *
     * @dataProvider findBestMoveProvider
     */
    public function testFindBestMove(array $boardData, PlayerId $playerId, ?Position $expectedMove): void
    {
        $board = Board::deserialize($boardData);

        $minimaxStrategy = new MinimaxStrategy();
        $bestMove = $minimaxStrategy->findBestMove($board, $playerId);

        $this->assertEquals($expectedMove, $bestMove);
    }

    /**
     * @return array<string, array{
     *     boardData: array<int, int|null>,
     *     playerId: PlayerId,
     *     expectedMoves: array<int, Position>
     * }>
     */
    public function findOpponentWinningMovesProvider(): array
    {
        return [
            'empty board' => [
                'boardData' => [],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMoves' => [],
            ],
            'winning moves available diagonal' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMoves' => [
                    Position::create(2, 0),
                    Position::create(2, 2),
                ],
            ],
            'winning moves horizontal' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMoves' => [
                    Position::create(0, 2),
                ],
            ],
            'winning moves vertical' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMoves' => [
                    Position::create(2, 0),
                ],
            ],
            'multiple winning moves across different directions' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                    PlayerId::PLAYER_ONE->value,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMoves' => [
                    Position::create(0, 2),
                    Position::create(2, 0),
                ],
            ],
            'no winning moves with nearly full board' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMoves' => [],
            ],
            'player two winning moves diagonal' => [
                'boardData' => [
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_TWO,
                'expectedMoves' => [
                    Position::create(2, 2),
                ],
            ],
            'player two multiple winning opportunities' => [
                'boardData' => [
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_TWO,
                'expectedMoves' => [
                    Position::create(2, 0),
                    Position::create(2, 2),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *     boardData: array<int, int|null>,
     *     playerId: PlayerId,
     *     expectedMove: Position|null
     * }>
     */
    public function findBestMoveProvider(): array
    {
        return [
            'full board' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => null,
            ],
            'choose winning move diagonal' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => Position::create(2, 2),
            ],
            'take center if available' => [
                'boardData' => [
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => Position::create(1, 1),
            ],
            'take corner when center taken' => [
                'boardData' => [
                    null,
                    null,
                    null,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => Position::create(0, 0),
            ],
            //            'block opponent win' => [
            //                'boardData' => [
            //                    PlayerId::PLAYER_ONE->value,
            //                    PlayerId::PLAYER_ONE->value,
            //                    null,
            //                    PlayerId::PLAYER_TWO->value,
            //                    null,
            //                    null,
            //                    null,
            //                    null,
            //                    null,
            //                ],
            //                'playerId' => PlayerId::PLAYER_TWO,
            //                'expectedMove' => Position::fromIndex(2),
            //            ],
            //            'block opponent diagonal' => [
            //                'boardData' => [
            //                    PlayerId::PLAYER_TWO->value,
            //                    PlayerId::PLAYER_ONE->value,
            //                    null,
            //                    null,
            //                    PlayerId::PLAYER_TWO->value,
            //                    null,
            //                    null,
            //                    null,
            //                    null,
            //                ],
            //                'playerId' => PlayerId::PLAYER_ONE,
            //                'expectedMove' => Position::fromIndex(8),
            //            ],
            //            'block opponent horizontal' => [
            //                'boardData' => [
            //                    PlayerId::PLAYER_TWO->value,
            //                    PlayerId::PLAYER_TWO->value,
            //                    null,
            //                    PlayerId::PLAYER_ONE->value,
            //                    null,
            //                    null,
            //                    null,
            //                    null,
            //                    null,
            //                ],
            //                'playerId' => PlayerId::PLAYER_ONE,
            //                'expectedMove' => Position::fromIndex(2),
            //            ],
            'block opponent vertical' => [
                'boardData' => [
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => Position::fromIndex(7),
            ],
            'prefer winning move over blocking' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_TWO->value,
                    null,
                    null,
                    null,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => Position::create(0, 2),
            ],
            'only one move left' => [
                'boardData' => [
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_TWO->value,
                    PlayerId::PLAYER_ONE->value,
                    null,
                ],
                'playerId' => PlayerId::PLAYER_ONE,
                'expectedMove' => Position::create(2, 2),
            ],
        ];
    }
}
