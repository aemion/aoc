<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Vector2DInt;

final class Day20 extends AbstractSolver
{
    private Grid $grid;
    private Vector2DInt $start;
    private Vector2DInt $end;
    private array $path;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $grid = [];
        $x = 0;
        while (!feof($file)) {
            $line = trim(fgets($file));
            $characters = str_split($line);
            $startIndex = array_search('S', $characters, true);

            if ($startIndex !== false) {
                $this->start = new Vector2DInt($x, $startIndex);
            }
            $endIndex = array_search('E', $characters, true);

            if ($endIndex !== false) {
                $this->end = new Vector2DInt($x, $endIndex);
            }
            $grid[] = $characters;
            $x++;
        }
        $this->grid = new Grid($grid);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $direction = Direction::Top->getVector2D();
        $position = $this->start;
        $this->path = [$position->__toString() => ['position' => $position, 'index' => 0]];
        $i = 1;
        while (!$position->equals($this->end)) {
            $nextPosition = $position->addVector2D($direction);
            $nextValue = $this->grid->getValue($nextPosition);
            if ($nextValue === '#') {
                // Change direction
                foreach ($direction->getOrthogonalVectors() as $nextDirection) {
                    if ($this->grid->getValue($position->addVector2D($nextDirection)) !== '#') {
                        $direction = $nextDirection;
                        break;
                    }
                }
                continue;
            }

            $position = $nextPosition;
            $this->path[$position->__toString()] = ['position' => $position, 'index' => $i];
            $i++;
        }
    }

    public function firstStar(): string
    {
        $directions = Direction::vectors();

        $result = [];
        foreach ($this->path as $positionAndIndex) {
            /** @var Vector2DInt $position */
            $position = $positionAndIndex['position'];
            foreach ($directions as $direction) {
                $potentialWallPosition = $position->addVector2D($direction);
                if ($this->grid->tryGetValue($potentialWallPosition) !== '#') {
                    continue;
                }

                $nextPosition = $potentialWallPosition->addVector2D($direction);
                if (!isset($this->path[$nextPosition->__toString()])) {
                    continue;
                }

                $currentIndex = $positionAndIndex['index'];
                $nextIndex = $this->path[$nextPosition->__toString()]['index'];
                if ($nextIndex > $currentIndex + 2) {
                    $diff = $nextIndex - ($currentIndex + 2);
                    if (!isset($result[$diff])) {
                        $result[$diff] = 0;
                    }

                    $result[$diff]++;
                }
            }
        }

        $total = 0;
        foreach ($result as $gainedTime => $number) {
            if ($gainedTime >= 100) {
                $total += $number;
            }
        }

        return (string) $total;
    }

    private function manhattanDistance(Vector2DInt $a, Vector2DInt $b): int
    {
        return abs($a->x - $b->x) + abs($a->y - $b->y);
    }

    public function secondStar(): string
    {
        $directions = Direction::vectors();

        $result = [];
        foreach ($this->path as $positionAndIndex) {
            /** @var Vector2DInt $position */
            $position = $positionAndIndex['position'];
            $currentIndex = $positionAndIndex['index'];
            $minX = max(1, $position->x - 20);
            $maxX = min($this->grid->getXMax() - 1, $position->x + 21);
            $minY = max(1, $position->y - 20);
            $maxY = min($this->grid->getYMax() - 1, $position->y + 21);
            for ($x = $minX; $x < $maxX; $x++) {
                for ($y = $minY; $y < $maxY; $y++) {
                    $shortcutEnd = new Vector2DInt($x, $y);
                    $value = $this->grid->getValue($shortcutEnd);
                    if ($value === '#') {
                        continue;
                    }

                    $distance = $this->manhattanDistance($position, $shortcutEnd);
                    if ($distance > 20) {
                        continue;
                    }

                    $nextIndex = $this->path[$shortcutEnd->__toString()]['index'];

                    $diff = $nextIndex - ($currentIndex + $distance);

                    if ($diff >= 50) {
                        if (!isset($result[$diff])) {
                            $result[$diff] = 0;
                        }

                        $result[$diff]++;
                    }
                }
            }
        }

        $total = 0;
        foreach ($result as $gainedTime => $number) {
            if ($gainedTime >= 100) {
                $total += $number;
            }
        }

        return (string) $total;
    }
}
