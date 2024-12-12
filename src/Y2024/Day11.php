<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day11 extends AbstractSolver
{
    private array $stones = [];

    private array $cachedStones = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $line = trim(fgets($file));
        $this->stones = array_map(static fn(string $a) => (int) $a, explode(' ', $line));
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $stones = $this->stones;
        for ($i = 0; $i < 25; $i++) {
            $nextStones = [];
            foreach ($stones as $stone) {
                array_push($nextStones, ...$this->getNextStones($stone));
            }
            $stones = $nextStones;
        }

        return (string) \count($stones);
    }

    public function secondStar(): string
    {
        $total = 0;

        $stones = [];
        foreach ($this->stones as $stone) {
            if (!isset($stones[$stone])) {
                $stones[$stone] = 0;
            }
            $stones[$stone]++;
        }

        for ($i = 0; $i < 75; $i++) {
            $nextStones = [];
            foreach ($stones as $stone => $count) {
                foreach ($this->getNextStones($stone) as $nextStone) {
                    if (!isset($nextStones[$nextStone])) {
                        $nextStones[$nextStone] = 0;
                    }
                    $nextStones[$nextStone] += $count;
                }
            }
            $stones = $nextStones;
        }

        foreach ($stones as $count) {
            $total += $count;
        }

        return (string) $total;
    }

    private function getNextStones(int $stone): array
    {
        if (isset($this->cachedStones[$stone])) {
            return $this->cachedStones[$stone];
        }

        if ($stone === 0) {
            $this->cachedStones[$stone] = [1];

            return $this->cachedStones[$stone];
        }

        $stoneString = (string) $stone;
        $stoneLength = \strlen($stoneString);
        if ($stoneLength % 2 === 0) {
            $this->cachedStones[$stone] = array_map(
                static fn(string $a) => (int) $a,
                str_split($stoneString, $stoneLength / 2)
            );
        } else {
            $this->cachedStones[$stone] = [$stone * 2024];
        }

        return $this->cachedStones[$stone];
    }
}
