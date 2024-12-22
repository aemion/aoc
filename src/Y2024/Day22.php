<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day22 extends AbstractSolver
{
    private array $secrets;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->secrets = [];
        while (!feof($file)) {
            $this->secrets[] = (int) trim(fgets($file));
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $total = 0;
        foreach ($this->secrets as $secret) {
            for ($i = 0; $i < 2000; $i++) {
                $secret = $this->nextSecret($secret);
            }
            $total += $secret;
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $bananaInfo = [];
        foreach ($this->secrets as $secret) {
            $diff = [];
            $numbers = [];
            $bananaNumber = $secret % 10;
            for ($i = 0; $i < 2000; $i++) {
                $previousNumber = $bananaNumber;
                $secret = $this->nextSecret($secret);
                $bananaNumber = $secret % 10;
                $numbers[] = $bananaNumber;
                $diff[] = $bananaNumber - $previousNumber;
            }
            $bananaInfo[] = ['diff' => $diff, 'numbers' => $numbers];
        }

        return (string) $this->getMax($bananaInfo);
    }

    private function getMax(array $bananaInfo): int
    {
        $totals = [];
        foreach ($bananaInfo as $bananaDiffAndNumbers) {
            $numbers = $bananaDiffAndNumbers['numbers'];
            $bananaTree = [];
            $diff = $bananaDiffAndNumbers['diff'];
            for ($i = 3; $i < 2000; $i++) {
                $a = $diff[$i - 3];
                $b = $diff[$i - 2];
                $c = $diff[$i - 1];
                $d = $diff[$i];
                if (isset($bananaTree[$a][$b][$c][$d])) {
                    continue;
                }

                $bananaTree[$a][$b][$c][$d] = $numbers[$i];
                $id = implode('|', [$a, $b, $c, $d]);
                if (!isset($totals[$id])) {
                    $totals[$id] = 0;
                }
                $totals[$id] += $numbers[$i];
            }
        }

        return max($totals);
    }

    private function nextSecret(int $secret): int
    {
        $nextSecret = $secret * 64;
        $nextSecret = $this->mix($nextSecret, $secret);
        $nextSecret = $this->prune($nextSecret);
        $secret = $nextSecret;

        // Divide by 32
        $nextSecret >>= 5;
        $nextSecret = $this->mix($nextSecret, $secret);
        $nextSecret = $this->prune($nextSecret);
        $secret = $nextSecret;

        $nextSecret *= 2048;
        $nextSecret = $this->mix($nextSecret, $secret);

        return $this->prune($nextSecret);
    }

    private function mix(int $number, int $secret): int
    {
        return $number ^ $secret;
    }

    private function prune(int $number): int
    {
        // 2^24
        return $number % 16777216;
    }

    public function getBananas(mixed $bananaDiffAndNumbers, array $sequence): mixed
    {
        $sequenceLength = 4; // Always 4 because sequences are of size 4
        $sequenceIndex = 0;
        $bananas = 0;
        foreach ($bananaDiffAndNumbers['diff'] as $index => $diff) {
            $lookingFor = $sequence[$sequenceIndex];
            if ($diff === $lookingFor) {
                $sequenceIndex++;
            } elseif ($sequenceIndex > 0) {
                $sequenceIndex = 0;
            }
            if ($sequenceIndex === $sequenceLength) {
                $bananas = $bananaDiffAndNumbers['numbers'][$index];
                break;
            }
        }

        return $bananas;
    }
}
