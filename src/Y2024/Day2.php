<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day2 extends AbstractSolver
{
    private array $lines = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');
        while (!feof($file)) {
            $line = trim(fgets($file));
            $values = explode(' ', $line);
            $this->lines[] = array_map(static fn($value) => (int) $value, $values);
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $total = 0;
        foreach ($this->lines as $line) {
            if ($this->isLineSafe($line, 0)) {
                $total++;
            }
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;
        foreach ($this->lines as $line) {
            if ($this->isLineSafe($line, 1)) {
                $total++;
            }
        }

        return (string) $total;
    }

    // This is not generic, it works for one or two allowed errors
    private function isLineSafe(array $line, int $allowedErrors): bool
    {
        $diffs = [];
        $count = \count($line);
        for ($i = 1; $i < $count; $i++) {
            $diffs[] = $line[$i] - $line[$i - 1];
        }

        // TODO generalize
        if ($diffs[0] === 0 && $diffs[1] === 0) {
            return false;
        }
        $countPositive = 0;
        $countZero = 0;
        // TODO generalize
        for ($i = 0; $i < 3; $i++) {
            if ($diffs[$i] === 0) {
                $countZero++;
            } elseif ($diffs[$i] > 0) {
                $countPositive++;
            }
        }

        if ($countZero > $allowedErrors) {
            return false;
        }

        // TODO generalize
        $isAscending = $countPositive >= 2;

        for ($i = 1; $i < $count; $i++) {
            $isSafe = true;
            $diff = $line[$i] - $line[$i - 1];
            if (abs($diff) > 3) {
                $isSafe = false;
            }

            if ($isAscending && $diff <= 0) {
                $isSafe = false;
            } elseif (!$isAscending && $diff >= 0) {
                $isSafe = false;
            }

            if (!$isSafe) {
                if ($allowedErrors === 0) {
                    return false;
                }
                $withoutCurrent = array_values(array_filter($line, static fn($key) => $key !== $i, ARRAY_FILTER_USE_KEY)
                );
                $withoutPrevious = array_values(
                    array_filter(
                        $line,
                        static fn($key) => $key !== ($i - 1),
                        ARRAY_FILTER_USE_KEY
                    )
                );

                return $this->isLineSafe($withoutCurrent, 0) || $this->isLineSafe($withoutPrevious, $allowedErrors - 1);
            }
        }

        return true;
    }
}
