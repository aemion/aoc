<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Matrix2DInt;
use App\Y2024\Model\Vector2DInt;

final class Day4 extends AbstractSolver
{
    private Grid $grid;

    public function getOppositeVector(Vector2DInt $direction): Vector2DInt
    {
        $rotationMatrix = new Matrix2DInt(-1, 0, 0, -1);
        return $direction->multiplyMatrix2D($rotationMatrix);
    }

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

    public function firstStar(): string
    {
        $directions = Direction::vectorsWithDiagonals();
        $total = 0;
        foreach ($this->grid->toArray() as $x => $line) {
            foreach ($line as $y => $value) {
                if ($value !== 'X') {
                    continue;
                }

                foreach ($directions as $direction) {
                    if ($this->isValidNextLetter($direction, new Vector2DInt($x, $y), ['M', 'A', 'S'])) {
                        $total++;
                    }
                }
            }
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $directions = Direction::diagonals();

        $rotationMatrix90 = new Matrix2DInt(0, 1, -1, 0);
        $total = 0;
        foreach ($this->grid->toArray() as $x => $line) {
            foreach ($line as $y => $value) {
                $counted = false;
                $position = new Vector2DInt($x, $y);

                if ($this->grid->getValue($position) !== 'A') {
                    continue;
                }

                foreach ($directions as $name => $direction) {
                    if ($counted) {
                        continue;
                    }

                    // Première branche
                    $mPositionCheck = $position->addVector2D($direction);
                    if ($this->grid->tryGetValue($mPositionCheck) !== 'M') {
                        continue;
                    }

                    $oppositeDirection = $this->getOppositeVector($direction);
                    $sPositionCheck = $position->addVector2D($oppositeDirection);
                    if ($this->grid->tryGetValue($sPositionCheck) !== 'S') {
                        continue;
                    }

                    $orthogonalDirection1 = $direction->multiplyMatrix2D($rotationMatrix90);
                    $orthogonalDirection2 = $this->getOppositeVector($orthogonalDirection1);

                    $corner1 = $position->addVector2D($orthogonalDirection1);
                    $corner2 = $position->addVector2D($orthogonalDirection2);

                    $corner1Value = $this->grid->tryGetValue($corner1);
                    $corner2Value = $this->grid->tryGetValue($corner2);
                    if (
                        ($corner1Value === 'M' && $corner2Value === 'S')
                        || ($corner2Value === 'M' && $corner1Value === 'S')
                    ) {
                        $counted = true;
                        $total++;
                    }
                }
            }
        }

        return (string) $total;
    }

    public function isValidNextLetter(Vector2DInt $direction, Vector2DInt $position, array $letters): bool
    {
        if (empty($letters)) {
            return true;
        }

        $nextPosition = $position->addVector2D($direction);
        if (!$this->grid->isInside($nextPosition)) {
            return false;
        }

        $letter = array_shift($letters);
        $value = $this->grid->getValue($nextPosition);
        if ($value !== $letter) {
            return false;
        }

        // Check next letter
        return $this->isValidNextLetter($direction, $nextPosition, $letters);
    }
}
