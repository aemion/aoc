<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Vector2DInt;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

final class Day14 extends AbstractSolver
{
    private int $xMax;
    private int $yMax;
    private array $initialRobots;
    private ?ConsoleSectionOutput $section = null;

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
        $this->print(0, $this->initialRobots);
        $robots = $this->initialRobots;
        // How to find a christmas tree...?
        for ($i = 0; $i < 100; $i++) {
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
            $this->print($i + 1, $robots);
            usleep(500000);
        }

        return 'NA';
    }

    public function print(int $numberOfSeconds, array $robots): void
    {
        if ($this->section === null) {
            $this->section = $this->output->section();
        }
        $this->section->clear();
        $lines = [];
        for ($j = 0; $j < $this->yMax; $j++) {
            $lines[] = array_fill(0, $this->xMax, '.');
        }

        foreach ($robots as $robot) {
            /** @var Vector2DInt $position */
            $position = $robot['position'];
            $lines[$position->y][$position->x] = 'X';
        }

        $this->section->writeln('Seconds: ' . $numberOfSeconds);

        foreach ($lines as $line) {
            $this->section->writeln(implode('', $line));
        }
    }
}
