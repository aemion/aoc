<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day1 extends AbstractSolver
{
    private array $leftList = [];
    private array $rightList = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');
        while (!feof($file)) {
            $line = trim(fgets($file));
            $values = explode('  ', $line);
            $this->leftList[] = (int) $values[0];
            $this->rightList[] = (int) $values[1];
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        sort($this->leftList);
        sort($this->rightList);
    }

    public function firstStar(): string
    {
        $total = 0;
        foreach ($this->leftList as $key => $value) {
            $total += abs($value - $this->rightList[$key]);
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;
        foreach ($this->leftList as $value) {
            $filtered = array_filter($this->rightList, static fn($b) => $b === $value);
            $total += $value * \count($filtered);
        }

        return (string) $total;
    }
}
