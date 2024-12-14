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
        $directions = Direction::cases();
        /**  @var Day12Cell $cell */
        foreach ($this->grid->getCells() as $position => $cell) {
            foreach ($directions as $direction) {
                $nextPositionInDirection = $position->addVector2D($direction->getVector2D());
                $orthogonalDirection = $direction->getVector2D()->multiplyMatrix2D($rotationMatrix90);
                $nextPositionInOrthogonalDirection = $position->addVector2D($orthogonalDirection);

                $diagonalDirection = $direction->getVector2D()->addVector2D($orthogonalDirection);
                $nextPositionInDiagonalDirection = $position->addVector2D($diagonalDirection);

                $value = $cell->value;
                $nextValue = $this->grid->tryGetValue($nextPositionInDirection)?->value;
                $nextOrthogonalValue = $this->grid->tryGetValue($nextPositionInOrthogonalDirection)?->value;
                $nextDiagonalValue = $this->grid->tryGetValue($nextPositionInDiagonalDirection)?->value;
                if ($nextValue !== $value && $nextOrthogonalValue !== $value) {
                    $cell->addAngle($diagonalDirection);
                } elseif ($nextValue === $value && $nextOrthogonalValue === $value && $nextDiagonalValue !== $value) {
                    $cell->addAngle($diagonalDirection);
                }
            }
        }

        foreach ($this->grid->getCells() as $cell) {
            if (!isset($plots[$cell->id])) {
                $plots[$cell->id] = ['area' => 0, 'perimeter' => 0, 'value' => $cell->value];
            }

            $plots[$cell->id]['area']++;
            $plots[$cell->id]['perimeter'] += $cell->countAngles();
        }

        $total = 0;
        foreach ($plots as $plot) {
            $total += $plot['area'] * $plot['perimeter'];
        }

        return (string) $total;
    }

}
