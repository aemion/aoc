<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Vector2DInt;

final class Day8 extends AbstractSolver
{
    private Grid $grid;

    /**
     * @var array<string, list<Vector2DInt>>
     */
    private array $antennas;

    public function loadInput(string $path): void
    {
        $grid = [];
        $file = fopen($path, 'rb');
        while (!feof($file)) {
            $line = trim(fgets($file));

            $grid[] = str_split($line);
        }
        $this->grid = new Grid($grid);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $this->antennas = [];
        foreach ($this->grid->toArray() as $x => $line) {
            foreach ($line as $y => $value) {
                if ($value === '.') {
                    continue;
                }

                if (!isset($this->antennas[$value])) {
                    $this->antennas[$value] = [];
                }

                $this->antennas[$value][] = new Vector2DInt($x, $y);
            }
        }
    }

    public function firstStar(): string
    {
        $countedPositions = [];
        foreach ($this->antennas as $value => $positions) {
            $count = \count($positions);
            foreach ($positions as $i => $position) {
                // Dernière antenne déjà comptée
                if ($i === $count - 1) {
                    break;
                }

                for ($j = $i + 1; $j < $count; $j++) {
                    $secondPosition = $this->antennas[$value][$j];
                    $distance = $secondPosition->substractVector2D($position);

                    $antinode1 = $position->substractVector2D($distance);
                    $antinode2 = $secondPosition->addVector2D($distance);
                    if ($this->grid->isInside($antinode1)) {
                        $countedPositions[$antinode1->__toString()] = true;
                    }

                    if ($this->grid->isInside($antinode2)) {
                        $countedPositions[$antinode2->__toString()] = true;
                    }
                }
            }
        }

        return (string) \count($countedPositions);
    }

    public function secondStar(): string
    {
        $countedPositions = [];
        foreach ($this->antennas as $value => $positions) {
            $count = \count($positions);
            foreach ($positions as $i => $position) {
                // Dernière antenne déjà comptée
                if ($i === $count - 1) {
                    break;
                }

                $countedPositions[$position->__toString()] = true;
                for ($j = $i + 1; $j < $count; $j++) {
                    $secondPosition = $this->antennas[$value][$j];
                    $distance = $secondPosition->substractVector2D($position);
                    $nextAntinode = $position->addVector2D($distance);
                    while ($this->grid->isInside($nextAntinode)) {
                        $countedPositions[$nextAntinode->__toString()] = true;
                        $nextAntinode = $nextAntinode->addVector2D($distance);
                    }

                    $nextAntinode = $position->substractVector2D($distance);
                    while ($this->grid->isInside($nextAntinode)) {
                        $countedPositions[$nextAntinode->__toString()] = true;
                        $nextAntinode = $nextAntinode->substractVector2D($distance);
                    }
                }
            }
        }

        return (string) \count($countedPositions);
    }
}
