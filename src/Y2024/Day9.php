<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day9 extends AbstractSolver
{
    private array $input;

    private array $disk;

    private int $maxId;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');
        $line = trim(fgets($file));
        $this->input = array_map(static fn(string $c) => (int) $c, str_split($line));
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function resetDisk(): void
    {
        $blocks = [];
        $id = 0;
        foreach ($this->input as $i => $char) {
            if ($i % 2 === 0) {
                $blocks[] = array_fill(0, $char, $id);
                $this->maxId = $id;
                $id++;
            } elseif ($char > 0) {
                $blocks[] = array_fill(0, $char, '.');
            }
        }

        $this->disk = array_merge(...$blocks);
    }

    public function firstStar(): string
    {
        $this->resetDisk();
        $total = 0;

        $startPointer = 0;
        $endPointer = \count($this->disk) - 1;
        while ($startPointer < $endPointer) {
            $value = $this->disk[$startPointer];
            if ($value === '.') {
                do {
                    $endValue = $this->disk[$endPointer];
                    $endPointer--;
                } while ($startPointer < $endPointer && $endValue === '.');

                if ($endValue !== '.') {
                    $this->disk[$startPointer] = $endValue;
                    $this->disk[$endPointer + 1] = '.';
                }
            }
            $startPointer++;
        }

        foreach ($this->disk as $index => $id) {
            if ($id === '.') {
                break;
            }

            $total += ($index * $id);
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $this->resetDisk();
        $total = 0;
        $endPointer = \count($this->disk) - 1;
        for ($id = $this->maxId; $id > 0; $id--) {
            $fileSize = 0;
            while ($this->disk[$endPointer] === $id) {
                $fileSize++;
                $endPointer--;
            }

            $emptySpaceStart = 0;
            $emptySpaceSize = 0;
            $foundEmptySpace = false;

            for ($j = 0; $j < $endPointer + 1; $j++) {
                if ($this->disk[$j] === '.') {
                    $emptySpaceSize++;
                } else {
                    $emptySpaceStart = $j + 1;
                    $emptySpaceSize = 0;
                }

                if ($emptySpaceSize >= $fileSize) {
                    $foundEmptySpace = true;
                    break;
                }
            }

            if ($foundEmptySpace) {
                for ($i = 0; $i < $fileSize; $i++) {
                    $this->disk[$endPointer + $i + 1] = '.';
                    $this->disk[$emptySpaceStart + $i] = $id;
                }
            }

            // On va au prochain
            while ($this->disk[$endPointer] === '.' || $this->disk[$endPointer] >= $id) {
                $endPointer--;
            }
        }

        foreach ($this->disk as $index => $id) {
            if ($id === '.') {
                continue;
            }

            $total += ($index * $id);
        }

        return (string) $total;
    }
}
