<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day25 extends AbstractSolver
{
    private array $locks;
    private array $keys;
    private const int SIZE = 5;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->locks = [];
        $this->keys = [];
        while (!feof($file)) {
            $line = trim(fgets($file));
            $isLock = str_contains($line, '#');
            $element = array_fill(0, 5, null);
            for ($i = 0; $i <= self::SIZE; $i++) {
                $line = trim(fgets($file));
                $characters = str_split($line);
                foreach ($characters as $j => $character) {
                    if (isset($element[$j])) {
                        continue;
                    }

                    if ($isLock) {
                        if ($character === '.') {
                            $element[$j] = $i;
                        }
                    } elseif ($character === '#') {
                        $element[$j] = self::SIZE - $i;
                    }
                }
            }

            if ($isLock) {
                $this->locks[] = $element;
            } else {
                $this->keys[] = $element;
            }

            fgets($file);
        }
    }

    public function isFirstStarSolved(): bool
    {
        return false;
    }

    public function firstStar(): string
    {
        $total = 0;
        foreach ($this->keys as $key) {
            foreach ($this->locks as $lock) {
                if ($this->doesKeyFit($lock, $key)) {
                    $total++;
                }
            }
        }

        return (string) $total;
    }

    private function doesKeyFit(array $lock, array $key): bool
    {
        foreach ($lock as $index => $value) {
            if ($value + $key[$index] > self::SIZE) {
                return false;
            }
        }

        return true;
    }

    public function secondStar(): string
    {
        $total = 0;

        return (string) $total;
    }
}
