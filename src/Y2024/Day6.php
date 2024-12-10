<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Matrix2DInt;
use App\Y2024\Model\Vector2DInt;

class Day6 extends AbstractSolver
{
    private Grid $grid;
    private Vector2DInt $initialPosition;
    private Matrix2DInt $rotationMatrix;
    private Direction $initialDirection;

    public function loadInput(string $path): void
    {
        $grid = [];
        $file = fopen($path, 'rb');
        $x = 0;
        $y = 0;
        while (!feof($file)) {
            $character = fgetc($file);
            if ($character === "\n" || $character === false) {
                $x++;
                $y = 0;
                continue;
            }

            if ($character === '^') {
                $character = 'X';
                $this->initialPosition = new Vector2DInt($x, $y);
                $this->initialDirection = Direction::Top;
            }

            $grid[$x][$y] = $character;
            $y++;
        }

        $this->grid = new Grid($grid);
    }

    public function preSolve(): void
    {
        $this->rotationMatrix = new Matrix2DInt(0, 1, -1, 0);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $total = 0;
        $inTheGrid = true;
        $grid = clone $this->grid;
        $position = $this->initialPosition;
        $this->grid->setValue($position, 'X');
        $direction = $this->initialDirection->getVector2D();
        while ($inTheGrid) {
            $nextPotentialPosition = $position->addVector2D($direction);
            if (!$grid->isInside($nextPotentialPosition)) {
                $inTheGrid = false;
            } else {
                $nextValue = $grid->getValue($nextPotentialPosition);
                if ($nextValue === '#') {
                    $direction = $direction->multiplyMatrix2D($this->rotationMatrix);
                } else {
                    $position = $nextPotentialPosition;
                    $grid->setValue($position, 'X');
                }
            }
        }

        foreach ($grid->toArray() as $line) {
            foreach ($line as $value) {
                if ($value === 'X') {
                    $total++;
                }
            }
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;
        // TODO method to iterate on all coords in grid
        foreach ($this->grid->toArray() as $x => $line) {
            foreach ($line as $y => $value) {
                if (\in_array($value, ['X', '#'], true)) {
                    continue;
                }

                $stuck = false;
                $inTheGrid = true;
                $grid = clone $this->grid;
                $position = $this->initialPosition;
                $this->grid->setValue($position, 'X');
                $direction = $this->initialDirection->getVector2D();

                // On met un obstacle en plus
                $grid->setValue(new Vector2DInt($x, $y), '#');

                $cycleDetectionList = [$position . '|' . $direction => true];

                while ($inTheGrid && !$stuck) {
                    $nextPotentialPosition = $position->addVector2D($direction);
                    if (!$grid->isInside($nextPotentialPosition)) {
                        $inTheGrid = false;
                    } else {
                        $nextValue = $grid->getValue($nextPotentialPosition);
                        if ($nextValue === '#') {
                            $direction = $direction->multiplyMatrix2D($this->rotationMatrix);
                        } else {
                            $position = $nextPotentialPosition;
                            $index = $position . '|' . $direction;
                            if (isset($cycleDetectionList[$index])) {
                                $stuck = true;
                            } else {
                                $cycleDetectionList[$index] = true;
                            }
                        }
                    }
                }

                if ($stuck) {
                    $total++;
                }
            }
        }

        return (string) $total;
    }
}
