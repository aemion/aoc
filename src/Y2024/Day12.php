<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Day12Cell;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Matrix2DInt;
use App\Y2024\Model\Vector2DInt;

final class Day12 extends AbstractSolver
{
    /**
     * @var Grid<Day12Cell>
     */
    private Grid $grid;
    private static int $id = 0;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');
        $grid = [];

        while (!feof($file)) {
            $line = trim(fgets($file));

            $grid[] = array_map(
                static fn(string $letter) => new Day12Cell($letter),
                str_split($line)
            );
        }
        $this->grid = new Grid($grid);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $this->discoverAllPlots();
    }

    public function firstStar(): string
    {
        $plots = [];
        foreach ($this->grid->getCells() as $cell) {
            if (!isset($plots[$cell->id])) {
                $plots[$cell->id] = ['area' => 0, 'perimeter' => 0, 'value' => $cell->value];
            }

            $plots[$cell->id]['area']++;
            $plots[$cell->id]['perimeter'] += $cell->fences;
        }

        $total = 0;
        foreach ($plots as $plot) {
            $total += $plot['area'] * $plot['perimeter'];
        }

        return (string) $total;
    }

    public function discoverAllPlots(): void
    {
        $directions = [
            Direction::Right->getVector2D(),
            Direction::Bottom->getVector2D(),
        ];

        $id = 0;

        /** @var Day12Cell $cell */
        foreach ($this->grid->getCells() as $position => $cell) {
            $value = $cell->value;
            if (null === $cell->id) {
                $this->discoverPlot($position, $id++);
            }

            // TODO include the count of fences inside the plot discovery...
            foreach ($directions as $direction) {
                $nextPosition = $position->addVector2D($direction);
                if (!$this->grid->isInside($nextPosition)) {
                    continue;
                }

                $nextCell = $this->grid->getValue($nextPosition);
                if ($nextCell->value === $value) {
                    $cell->removeFence();

                    $nextCell->removeFence();
                }
            }
        }
    }

    public function discoverPlot(Vector2DInt $position, int $id): void
    {
        $directions = Direction::cases();
        $cell = $this->grid->getValue($position);
        $cell->setId($id);
        foreach ($directions as $direction) {
            $nextPosition = $position->addVector2D($direction->getVector2D());
            if (!$this->grid->isInside($nextPosition)) {
                continue;
            }

            $nextCell = $this->grid->getValue($nextPosition);
            if ($nextCell->value !== $cell->value) {
                continue;
            }

            if ($nextCell->id !== null) {
                continue;
            }

            // $cell->removeFence();
            // $nextCell->removeFence();
            $this->discoverPlot($nextPosition, $id);
        }
    }

    public function secondStar(): string
    {
        $total = 0;

        $plots = [];
        $rotationMatrix90 = new Matrix2DInt(0, 1, -1, 0);
        foreach ($this->grid->getCells() as $position => $cell) {
            $direction = Direction::Right->getVector2D();
            $start = $position;
            $needToCountSides = false;
            if (!isset($plots[$cell->id])) {
                $plots[$cell->id] = ['area' => 0, 'sides' => 0, 'value' => $cell->value];
                $needToCountSides = true;
            }
            $plots[$cell->id]['area']++;

            $currentPosition = $start;
            // $nextPosition = $start->addVector2D($direction);

            if (!$needToCountSides) {
                continue;
            }
            // $directionCount = 4;
            $stop = false;
            dump($start);
            while (!$stop) {
                // if ($directionCount === 0) {
                //     die();
                // }
                $nextPosition = $currentPosition->addVector2D($direction);
                $nextCell = $this->grid->tryGetValue($nextPosition);
                if (null === $nextCell || $nextCell->value !== $cell->value) {
                    if ($cell->id === 0) {
                        // dump('ANGLE outside');
                    }
                    $direction = $direction->multiplyMatrix2D($rotationMatrix90);
                    $plots[$cell->id]['sides']++;
                    // $directionCount--;
                } else {
                    // $directionCount = 4;
                    $currentPosition = $nextPosition;
                    dump($currentPosition);
                    // $nextPosition = $nextPosition->addVector2D($direction);
                }

                // if ($cell->id === 0) {
                //     // dump($nextPosition->__toString());
                //     dump($nextPosition->__toString());
                // }
                // // $nextCell->visited = true;
                // if ($directionCount < 4) {
                //     // $plots[$cell->id]['sides']++;
                // }
                if ($currentPosition->equals($start) && $direction->equals(Direction::Right->getVector2D())) {
                    $stop = true;
                }
                if ($currentPosition->equals($start)) {
                    dump($direction);
                }
            }
        }
        dump($plots);

        return (string) $total;
    }

}
