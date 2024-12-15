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
        return false;
    }

    public function firstStar(): string
    {
        $position = $this->initialPosition;
        foreach ($this->directions as $directionCharacter) {
            $direction = $this->getDirection($directionCharacter);

            $nextEmptySpace = $this->getNextEmptySpace($position, $direction);
            if ($nextEmptySpace === null) {
                continue;
            }

            $oppositeDirection = $direction->multiplyScalar(-1);
            $cellA = $nextEmptySpace;
            $cellB = $cellA->addVector2D($oppositeDirection);
            while (!$cellB->equals($position)) {
                $this->grid->setValue($cellA, $this->grid->getValue($cellB));
                $cellA = $cellB;
                $cellB = $cellA->addVector2D($oppositeDirection);
            }
            $this->grid->setValue($position, '.');
            $position = $position->addVector2D($direction);
        }

        $total = 0;
        foreach ($this->grid->getCells() as $position => $cell) {
            if ($cell === 'O') {
                $total += 100 * $position->x + $position->y;
            }
        }

        return (string) $total;
    }

    private function getNextEmptySpace(Vector2DInt $position, Vector2DInt $direction): ?Vector2DInt
    {
        do {
            $nextPosition = $position->addVector2D($direction);
            $nextValue = $this->grid->tryGetValue($nextPosition);

            if ($nextValue === '.') {
                return $nextPosition;
            }

            $position = $nextPosition;
        } while ($nextValue !== '#');

        return null;
    }

    private function getDirection(string $char): Vector2DInt
    {
        return (match ($char) {
            '^' => Direction::Top,
            '<' => Direction::Left,
            '>' => Direction::Right,
            'v' => Direction::Bottom
        })->getVector2D();
    }

    public function secondStar(): string
    {
        $total = 0;

        return (string) $total;
    }
}
