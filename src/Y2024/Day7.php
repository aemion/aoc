<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

class Day7 extends AbstractSolver
{
    private array $lines;
    private array $operations = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->lines = [];
        while (!feof($file)) {
            $line = trim(fgets($file));
            [$result, $numbers] = explode(': ', $line);
            $numbers = explode(' ', $numbers);
            $this->lines[] = [
                'result' => (int) $result,
                'numbers' =>  array_map(static fn(string $i) => (int) $i, $numbers)
            ];
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $this->operations = [
            $this->add(...),
            $this->multiply(...)
        ];
        return (string) $this->getTotal();
    }

    public function secondStar(): string
    {
        $this->operations = [
            $this->add(...),
            $this->multiply(...),
            $this->concatenate(...)
        ];
        return (string) $this->getTotal();
    }

    private function getTotal(): int
    {
        $total = 0;

        foreach ($this->lines as $line) {
            $result = $line['result'];
            $numbers = $line['numbers'];
            if ($this->isResultPossible($result, $numbers[0], array_slice($numbers, 1))) {
                $total += $result;
            }
        }

        return $total;
    }

    private function isResultPossible(int $expectedResult, int $currentResult, array $numbers)
    {
        if (empty($numbers)) {
            return $expectedResult === $currentResult;
        }

        $nextNumber = array_shift($numbers);
        $nextResults = [];
        foreach ($this->operations as $operation) {
            $nextResults[] = $operation($currentResult, $nextNumber);
        }

        foreach ($nextResults as $nextResult) {
            $isPossibleForThisOperation = ($nextResult <= $expectedResult) && $this->isResultPossible($expectedResult, $nextResult, $numbers);
            if ($isPossibleForThisOperation) {
                return true;
            }
        }

        return false;
    }

    private function add(int $currentResult, int $nextNumber): int
    {
        return $currentResult + $nextNumber;
    }

    private function multiply(int $currentResult, int $nextNumber): int
    {
        return $currentResult * $nextNumber;
    }

    private function concatenate(int $currentResult, int $nextNumber): int
    {
        return (int) ($currentResult . $nextNumber);
    }
}
