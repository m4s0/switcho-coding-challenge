<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\ValueObject\Board;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Position;

final class MinimaxStrategy
{
    private const WIN_SCORE = 10;
    private const LOSE_SCORE = -10;
    private const DRAW_SCORE = 0;

    /**
     * @return array<Position>
     */
    public function findOpponentWinningMoves(Board $board, PlayerId $playerId): array
    {
        $winningMoves = [];

        if ($board->isFull() || $board->hasWinner()) {
            return [];
        }

        foreach ($board->getEmptyCells() as $position) {
            $boardCopy = Board::duplicate($board->getRawCells());
            $boardCopy->makeMove($position, $playerId);

            if ($boardCopy->getWinner() === $playerId) {
                $winningMoves[] = $position;
            }
        }

        return $winningMoves;
    }

    public function findBestMove(Board $board, PlayerId $playerId): ?Position
    {
        if ($board->isFull() || $board->hasWinner()) {
            return null;
        }

        $bestScore = -PHP_FLOAT_MAX;
        $bestMove = null;

        foreach ($board->getEmptyCells() as $position) {
            $boardCopy = Board::duplicate($board->getRawCells());
            $boardCopy->makeMove($position, $playerId);

            $score = $this->minimax($boardCopy, 0, false, $playerId);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $position;
            }
        }

        // If no winning move found, check for center and corners
        if ($bestScore <= 0) {
            // Prefer center
            $center = Position::create(1, 1);
            if ($board->isPositionEmpty($center)) {
                return $center;
            }

            // Then corners
            $corners = [
                Position::create(0, 0),
                Position::create(0, 2),
                Position::create(2, 0),
                Position::create(2, 2),
            ];

            foreach ($corners as $corner) {
                if ($board->isPositionEmpty($corner)) {
                    return $corner;
                }
            }
        }

        return $bestMove;
    }

    private function minimax(Board $board, int $depth, bool $isMaximizing, PlayerId $player): int
    {
        $opponent = PlayerId::PLAYER_ONE === $player ? PlayerId::PLAYER_TWO : PlayerId::PLAYER_ONE;

        if ($board->getWinner() === $player) {
            return self::WIN_SCORE - $depth;
        }
        if ($board->getWinner() === $opponent) {
            return self::LOSE_SCORE + $depth;
        }
        if ($board->isFull()) {
            return self::DRAW_SCORE;
        }

        if ($isMaximizing) {
            $bestScore = -PHP_FLOAT_MAX;
            foreach ($board->getEmptyCells() as $position) {
                $boardCopy = Board::duplicate($board->getRawCells());
                $boardCopy->makeMove($position, $player);
                $score = $this->minimax($boardCopy, $depth + 1, false, $player);
                $bestScore = max($bestScore, $score);
            }
        } else {
            $bestScore = PHP_FLOAT_MAX;
            foreach ($board->getEmptyCells() as $position) {
                $boardCopy = Board::duplicate($board->getRawCells());
                $boardCopy->makeMove($position, $opponent);
                $score = $this->minimax($boardCopy, $depth + 1, true, $player);
                $bestScore = min($bestScore, $score);
            }
        }

        return $bestScore;
    }
}
