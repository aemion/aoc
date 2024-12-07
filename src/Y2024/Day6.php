<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Matrix2DInt;
use App\Y2024\Model\Vector2DInt;

final class Day6 extends AbstractSolver
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
        $this->initialDirection = Direction::Top;
        while (!feof($file)) {
            $line = trim(fgets($file));
            $characters = str_split($line);
            $guardIndex = array_search('^', $characters);

            if ($guardIndex !== false) {
                $this->initialPosition = new Vector2DInt($x, $guardIndex);
            }

            $grid[] = $characters;
            $x++;
        }

        $this->grid = new Grid($grid);
        $this->grid->setValue($this->initialPosition, 'X');
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
        $path = $this->getNonObstrucatedPath();

        return (string) \count($path);
    }

    public function secondStar(): string
    {
        $total = 0;
        $path = $this->getNonObstrucatedPath();
        foreach ($path as $potentialObstaclePosition) {
            if ($potentialObstaclePosition->equals($this->initialPosition)) {
                continue;
            }
            $stuck = false;
            $inTheGrid = true;
            $grid = clone $this->grid;
            $position = $this->initialPosition;
            $this->grid->setValue($position, 'X');
            $direction = $this->initialDirection->getVector2D();

            // On met un obstacle en plus
            $grid->setValue($potentialObstaclePosition, '#');

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

        return (string) $total;
    }

    /**
     * @return list<Vector2DInt>
     */
    public function getNonObstrucatedPath(): array
    {
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
        $path = [];
        foreach ($grid->toArray() as $x => $line) {
            foreach ($line as $y => $value) {
                if ($value === 'X') {
                    $path[] = new Vector2DInt($x, $y);
                }
            }
        }

        return $path;
    }
}
