<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Vector2DInt;

final class Day15 extends AbstractSolver
{

    private Grid $grid;
    private Vector2DInt $initialPosition;

    private array $directions;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->directions = [];
        $grid = [];
        $x = 0;
        while (!feof($file)) {
            $line = trim(fgets($file));
            if (str_contains($line, '#')) {
                $characters = str_split($line);
                $robotIndex = array_search('@', $characters, true);

                if ($robotIndex !== false) {
                    $this->initialPosition = new Vector2DInt($x, $robotIndex);
                }
                $grid[] = $characters;
                $x++;
            }

            if (str_contains($line, '^')) {
                array_push($this->directions, ...str_split($line));
            }
        }

        $this->grid = new Grid($grid);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $position = $this->initialPosition;
        $grid = clone $this->grid;
        foreach ($this->directions as $directionCharacter) {
            $direction = $this->getDirection($directionCharacter)->getVector2D();

            $nextEmptySpace = $this->getNextEmptySpace($grid, $position, $direction);
            if ($nextEmptySpace === null) {
                continue;
            }

            $this->moveLine($direction, $nextEmptySpace, $position, $grid);
            $position = $position->addVector2D($direction);
        }

        $total = 0;
        foreach ($grid->getCells() as $position => $cell) {
            if ($cell === 'O') {
                $total += 100 * $position->x + $position->y;
            }
        }

        return (string) $total;
    }

    private function getNextEmptySpace(Grid $grid, Vector2DInt $position, Vector2DInt $direction): ?Vector2DInt
    {
        do {
            $nextPosition = $position->addVector2D($direction);
            $nextValue = $grid->tryGetValue($nextPosition);

            if ($nextValue === '.') {
                return $nextPosition;
            }

            $position = $nextPosition;
        } while ($nextValue !== '#');

        return null;
    }

    private function getDirection(string $char): Direction
    {
        return match ($char) {
            '^' => Direction::Top,
            '<' => Direction::Left,
            '>' => Direction::Right,
            'v' => Direction::Bottom
        };
    }

    public function secondStar(): string
    {
        $grid = $this->doubleGrid($this->grid);
        $position = new Vector2DInt($this->initialPosition->x, $this->initialPosition->y * 2);

        foreach ($this->directions as $directionCharacter) {
            $dir = $this->getDirection($directionCharacter);
            $direction = $dir->getVector2D();

            if (\in_array($dir, [Direction::Left, Direction::Right], true)) {
                $nextEmptySpace = $this->getNextEmptySpace($grid, $position, $direction);
                if ($nextEmptySpace === null) {
                    continue;
                }

                $this->moveLine($direction, $nextEmptySpace, $position, $grid);
                $position = $position->addVector2D($direction);
                continue;
            }

            $linesToMove = $this->getLinesToMove($grid, $position, $direction);
            if (empty($linesToMove)) {
                continue;
            }

            foreach ($linesToMove as $line) {
                $this->moveLine($direction, $line['end'], $line['start'], $grid);
            }
            $position = $position->addVector2D($direction);
        }

        $total = 0;
        foreach ($grid->getCells() as $position => $cell) {
            if ($cell === '[') {
                $total += 100 * $position->x + $position->y;
            }
        }

        return (string) $total;
    }

    private function doubleGrid(Grid $grid): Grid
    {
        $newGrid = [];
        foreach ($grid->toArray() as $line) {
            $newLine = [];
            foreach ($line as $value) {
                $duplicatedValue = match ($value) {
                    'O' => ['[', ']'],
                    '@' => ['@', '.'],
                    '.' => ['.', '.'],
                    '#' => ['#', '#']
                };
                array_push($newLine, ...$duplicatedValue);
            }
            $newGrid[] = $newLine;
        }

        return new Grid($newGrid);
    }

    public function moveLine(
        Vector2DInt $direction,
        Vector2DInt $nextEmptySpace,
        Vector2DInt $position,
        Grid $grid
    ): void {
        $oppositeDirection = $direction->multiplyScalar(-1);
        $cellA = $nextEmptySpace;
        while (!$cellA->equals($position)) {
            $cellB = $cellA->addVector2D($oppositeDirection);
            $grid->setValue($cellA, $grid->getValue($cellB));
            $cellA = $cellB;
        }
        $grid->setValue($position, '.');
    }

    private function getLinesToMove(Grid $grid, Vector2DInt $position, Vector2DInt $direction): array
    {
        $result = null;
        $start = $position;
        $otherStarts = [];
        do {
            $nextPosition = $position->addVector2D($direction);
            $nextValue = $grid->tryGetValue($nextPosition);

            if ($nextValue === '[') {
                $otherStarts[] = $nextPosition->addVector2D(Direction::Right->getVector2D());
            } elseif ($nextValue === ']') {
                $otherStarts[] = $nextPosition->addVector2D(Direction::Left->getVector2D());
            }
            if ($nextValue === '.') {
                $result = [
                    ['start' => $start, 'end' => $nextPosition],
                ];
            }
            if ($nextValue === '#') {
                $result = [];
            }
            $position = $nextPosition;
        } while ($result === null);

        if (empty($result)) {
            return [];
        }
        foreach ($otherStarts as $otherStart) {
            $linesToMove = $this->getLinesToMove($grid, $otherStart, $direction);
            if (empty($linesToMove)) {
                return [];
            }

            array_push($result, ...$linesToMove);
        }

        $count = \count($result);
        $notIncluded = [];
        foreach ($result as $i => $existingLine) {
            for ($j = $i + 1; $j < $count; ++$j) {
                $otherLine = $result[$j];
                if ($this->isIncluded($existingLine, $otherLine)) {
                    $notIncluded[] = $j;
                }
            }
        }

        return array_filter($result, static fn(int $i) => !\in_array($i, $notIncluded, true), ARRAY_FILTER_USE_KEY);
    }

    // No need to check for y because of the shape of the boxes
    private function isIncluded(array $existingLine, array $line): bool
    {
        $startA = $existingLine['start'];
        $endA = $existingLine['end'];
        $startB = $line['start'];
        $endB = $line['end'];

        if ($startA->y !== $startB->y) {
            return false;
        }

        $maxXA = max($startA->x, $endA->x);
        $maxXB = max($startB->x, $endB->x);
        $minXA = min($startA->x, $endA->x);
        $minXB = min($startB->x, $endB->x);

        return $minXA <= $minXB && $maxXA >= $maxXB;
    }
}
