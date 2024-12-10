<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

class Day4 extends AbstractSolver
{
    private array $grid;
    private int $xMax;
    private int $yMax;

    public function getDirections(): array
    {
        return [
            'topleft'     => ['x' => static fn(int $x) => $x - 1, 'y' => static fn(int $y) => $y - 1],
            'top'         => ['x' => static fn(int $x) => $x - 1, 'y' => static fn(int $y) => $y],
            'topright'    => ['x' => static fn(int $x) => $x - 1, 'y' => static fn(int $y) => $y + 1],
            'left'        => ['x' => static fn(int $x) => $x, 'y' => static fn(int $y) => $y - 1],
            'right'       => ['x' => static fn(int $x) => $x, 'y' => static fn(int $y) => $y + 1],
            'bottomleft'  => ['x' => static fn(int $x) => $x + 1, 'y' => static fn(int $y) => $y - 1],
            'bottom'      => ['x' => static fn(int $x) => $x + 1, 'y' => static fn(int $y) => $y],
            'bottomright' => ['x' => static fn(int $x) => $x + 1, 'y' => static fn(int $y) => $y + 1],
        ];
    }

    public function getOppositeDirection(string $direction): array
    {
        $directions = $this->getDirections();
        $opposites = [
            'topleft'  => 'bottomright',
            'top'      => 'bottom',
            'topright' => 'bottomleft',
            'left'     => 'right',

            'bottomright' => 'topleft',
            'bottom'      => 'top',
            'bottomleft'  => 'topright',
            'right'       => 'left',
        ];

        return $directions[$opposites[$direction]];
    }

    public function getOrthogonalDirections(string $direction): array
    {
        $directions = $this->getDirections();
        $orthogonals = [

            'topleft'  => ['topright', 'bottomleft'],
            'top'      => ['left', 'right'],
            'topright' => ['topleft', 'bottomright'],
            'left'     => ['top', 'bottom'],

            'bottomright' => ['topright', 'bottomleft'],
            'bottom'      => ['left', 'right'],
            'bottomleft'  => ['topleft', 'bottomright'],
            'right'       => ['top', 'bottom'],
        ];

        $orthogonalDirections = $orthogonals[$direction];
        $result = [];
        foreach ($orthogonalDirections as $directionName) {
            $result[$directionName] = $directions[$directionName];
        }

        return $result;
    }

    public function loadInput(string $path): void
    {
        $this->grid = [];
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

            $this->grid[$x][$y] = $character;
            $y++;
        }
        $this->xMax = \count($this->grid);
        $this->yMax = \count($this->grid[0]);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $directions = $this->getDirections();
        $total = 0;
        for ($x = 0; $x < $this->xMax; $x++) {
            for ($y = 0; $y < $this->yMax; $y++) {
                if ($this->grid[$x][$y] !== 'X') {
                    continue;
                }

                foreach ($directions as $direction) {
                    if ($this->isValidNextLetter($direction, $x, $y, ['M', 'A', 'S'])) {
                        $total++;
                    }
                }
            }
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $directions = $this->getDirections();
        // // Pour éviter les doublons, on ne prend que 4 directions
        $directions = array_filter(
            $directions,
            static fn(string $direction): bool => \in_array(
                $direction,
                ['topleft', 'bottomleft', 'topright', 'bottomright'],
                true
            ),
            ARRAY_FILTER_USE_KEY
        );

        $total = 0;
        // $coords = [];
        for ($x = 0; $x < $this->xMax; $x++) {
            for ($y = 0; $y < $this->yMax; $y++) {
                $counted = false;

                if ($this->grid[$x][$y] !== 'A') {
                    continue;
                }

                foreach ($directions as $name => $direction) {
                    if ($counted) {
                        continue;
                    }

                    // Première branche
                    if (!$this->isValidNextLetter($direction, $x, $y, ['M'])) {
                        continue;
                    }

                    if (!$this->isValidNextLetter($this->getOppositeDirection($name), $x, $y, ['S'])) {
                        continue;
                    }

                    // Deuxième branche
                    $orthogonalDirections = $this->getOrthogonalDirections($name);
                    foreach ($orthogonalDirections as $orthogonalName => $orthogonalDirection) {
                        if (!$this->isValidNextLetter($orthogonalDirection, $x, $y, ['M'])) {
                            continue;
                        }

                        if ($this->isValidNextLetter($this->getOppositeDirection($orthogonalName), $x, $y, ['S'])) {
                            $total++;
                            $counted = true;
                            $direction[$orthogonalName] = $orthogonalDirection;
                        }
                    }
                }
            }
        }

        return (string) $total;
    }

    public function isValidNextLetter(array $direction, int $x, int $y, array $letters): bool
    {
        if (empty($letters)) {
            return true;
        }

        $nextX = $direction['x']($x);
        $nextY = $direction['y']($y);
        if (!$this->hasValue($nextX, $nextY)) {
            return false;
        }

        $letter = array_shift($letters);
        $value = $this->getValue($nextX, $nextY);
        if ($value !== $letter) {
            return false;
        }

        // Check next letter
        return $this->isValidNextLetter($direction, $nextX, $nextY, $letters);
    }

    public function hasValue(int $x, int $y): bool
    {
        return $x >= 0 && $y >= 0 && $x < $this->xMax && $y < $this->yMax;
    }

    public function getValue(int $x, int $y): string
    {
        return $this->grid[$x][$y];
    }

}
