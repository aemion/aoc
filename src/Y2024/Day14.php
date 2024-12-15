<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Vector2DInt;

final class Day14 extends AbstractSolver
{
    private int $xMax;
    private int $yMax;
    private array $initialRobots;

    // To load correctly, add in the input the width and height of the grid in the first line

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');
        $line = trim(fgets($file));
        [$this->xMax, $this->yMax] = array_map('\intval', explode(' ', $line));
        $regex = '/p=(-?\d+),(-?\d+) v=(-?\d+),(-?\d+)/';

        while (!feof($file)) {
            $line = trim(fgets($file));

            $matches = [];
            preg_match($regex, $line, $matches);
            $this->initialRobots[] = [
                'position' => new Vector2DInt((int) $matches[1], (int) $matches[2]),
                'velocity' => new Vector2DInt((int) $matches[3], (int) $matches[4]),
            ];
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $quadrants = array_fill(0, 4, 0);

        foreach ($this->initialRobots as $robot) {
            /** @var Vector2DInt $notWrappedPosition */
            $notWrappedPosition = $robot['position']->addVector2D($robot['velocity']->multiplyScalar(100));
            $finalPosition = new Vector2DInt(
                $this->positiveModulo($notWrappedPosition->x, $this->xMax),
                $this->positiveModulo($notWrappedPosition->y, $this->yMax),
            );
            $quadrant = $this->getQuadrantIndex($finalPosition);
            if ($quadrant !== -1) {
                $quadrants[$quadrant]++;
            }
        }

        $total = 1;
        foreach ($quadrants as $count) {
            $total *= $count;
        }

        return (string) $total;
    }

    private function positiveModulo(int $a, int $b): int
    {
        return (($a % $b) + $b) % $b;
    }

    public function getQuadrantIndex(Vector2DInt $position): int
    {
        $midX = ($this->xMax - 1) / 2;
        $midY = ($this->yMax - 1) / 2;
        if ($position->x < $midX) {
            if ($position->y < $midY) {
                return 0;
            }

            if ($position->y > $midY) {
                return 2;
            }
        }

        if ($position->x > $midX) {
            if ($position->y < $midY) {
                return 1;
            }

            if ($position->y > $midY) {
                return 3;
            }
        }

        return -1;
    }

    public function secondStar(): string
    {
        $robots = $this->initialRobots;
        // How to find a christmas tree...?
        $result = 0;
        for ($i = 0; $i < $this->xMax * $this->yMax; $i++) {
            $nextRobots = [];
            foreach ($robots as $robot) {
                /** @var Vector2DInt $notWrappedPosition */
                $notWrappedPosition = $robot['position']->addVector2D($robot['velocity']);
                $finalPosition = new Vector2DInt(
                    $this->positiveModulo($notWrappedPosition->x, $this->xMax),
                    $this->positiveModulo($notWrappedPosition->y, $this->yMax),
                );
                $nextRobots[] = ['position' => $finalPosition, 'velocity' => $robot['velocity']];
            }
            $robots = $nextRobots;
            if ($this->canBeChristmasTree($robots)) {
                $result = $i + 1;
                break;
            }
        }
        $this->print($result, $robots);

        return (string) $result;
    }

    public function canBeChristmasTree(array $robots): bool
    {
        $countByX = [];
        $countByY = [];
        foreach ($robots as $robot) {
            /** @var Vector2DInt $position */
            $position = $robot['position'];
            if (!isset($countByX[$position->x])) {
                $countByX[$position->x] = 0;
            }
            if (!isset($countByY[$position->y])) {
                $countByY[$position->y] = 0;
            }

            $countByX[$position->x]++;
            $countByY[$position->y]++;
        }

        $possibleX = false;
        foreach ($countByX as $count) {
            if ($count > 20) {
                $possibleX = true;
            }
        }

        $possibleY = false;
        foreach ($countByY as $count) {
            if ($count > 20) {
                $possibleY = true;
            }
        }

        return $possibleX && $possibleY;
    }

    public function print(int $numberOfSeconds, array $robots): void
    {
        $lines = [];
        for ($j = 0; $j < $this->yMax; $j++) {
            $lines[] = array_fill(0, $this->xMax, '.');
        }

        foreach ($robots as $robot) {
            /** @var Vector2DInt $position */
            $position = $robot['position'];
            $lines[$position->y][$position->x] = 'X';
        }

        $this->output->writeln('Seconds: ' . $numberOfSeconds);

        foreach ($lines as $line) {
            $this->output->writeln(implode('', $line));
        }
    }
}
