<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day24 extends AbstractSolver
{
    private array $values = [];

    private array $operations = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $regex = '/(\d|\w{3}) (AND|XOR|OR) (\d|\w{3}) -> (\d|\w{3})/';
        while (!feof($file)) {
            $line = trim(fgets($file));

            if (str_contains($line, ':')) {
                [$variable, $value] = explode(': ', $line);
                $this->values[$variable] = (bool) $value;
            } elseif (str_contains($line, '->')) {
                $matches = [];
                preg_match($regex, $line, $matches);
                $this->operations[$matches[4]] = [
                    'operator'       => $matches[2],
                    'operands'       => [$matches[1], $matches[3]],
                    'resultVariable' => $matches[4],
                ];
            }
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
    }

    public function firstStar(): string
    {
        $bits = $this->solveZBits($this->operations, $this->values);

        $result = $this->bitsToInt($bits);

        return (string) $result;
    }

    public function secondStar(): string
    {
        $total = 0;
        $xResult = [];
        $yResult = [];
        foreach ($this->values as $variable => $value) {
            if (str_starts_with($variable, 'x')) {
                $xResult[$variable] = $value;
            } elseif (str_starts_with($variable, 'y')) {
                $yResult[$variable] = $value;
            }
        }

        krsort($xResult);
        krsort($yResult);
        $expectedResult = $this->bitsToInt($xResult) + $this->bitsToInt($yResult);
        $initialResult = $this->solveZBits($this->operations, $this->values);
        $wrongBits = array_keys(array_diff_assoc($this->intToBits($expectedResult), $initialResult));

        return (string) $total;
    }

    private function extractUsefulVariables(
        string $wire,
        array $operations,
        array $usefulVariables,
        array $initialVariables
    ): array {
        $operands = $operations[$wire]['operands'];
        if (!\in_array($operands[0], $usefulVariables, true)) {
            $usefulVariables[] = $operands[0];
            if (!\in_array($operands[0], $initialVariables, true)) {
                $usefulVariables = $this->extractUsefulVariables(
                    $operands[0],
                    $operations,
                    $usefulVariables,
                    $initialVariables
                );
            }
        }
        if (!\in_array($operands[1], $usefulVariables, true)) {
            $usefulVariables[] = $operands[1];

            if (!\in_array($operands[1], $initialVariables, true)) {
                $usefulVariables = $this->extractUsefulVariables(
                    $operands[1],
                    $operations,
                    $usefulVariables,
                    $initialVariables
                );
            }
        }

        return $usefulVariables;
    }

    private function bitsToInt(array $bits): int
    {
        $result = 0;
        $index = \count($bits) - 1;
        foreach ($bits as $bit) {
            if ($bit) {
                $result += (1 << $index);
            }
            $index--;
        }

        return $result;
    }

    private function intToBits(int $number): array
    {
        $binString = decbin($number);

        $bits = str_split($binString);
        $size = \count($bits) - 1;

        return array_combine(
            array_map(
                static fn(int $i) => 'z' . (($size - $i) < 10 ? '0' . ($size - $i) : (string) ($size - $i)),
                array_keys($bits)
            ),
            array_map(static fn(string $char) => $char === '1', $bits)
        );
    }

    public function solveZBits(array $operations, array $values): array
    {
        $bits = [];
        $remainingBits = 0;
        foreach ($operations as $variable => $operation) {
            if (str_starts_with($variable, 'z')) {
                $bits[$variable] = null;
                $remainingBits++;
            }
        }
        krsort($bits);

        while ($remainingBits > 0) {
            foreach ($operations as $variable => $operation) {
                $operands = $operation['operands'];
                if (!isset($values[$operands[0]], $values[$operands[1]])) {
                    continue;
                }

                unset($operations[$variable]);
                $values[$variable] = match ($operation['operator']) {
                    'AND' => $values[$operands[0]] && $values[$operands[1]],
                    'OR' => $values[$operands[0]] || $values[$operands[1]],
                    'XOR' => $values[$operands[0]] xor $values[$operands[1]],
                };
                if (str_starts_with($variable, 'z')) {
                    $bits[$variable] = $values[$variable];
                    $remainingBits--;
                }
            }
        }

        return $bits;
    }

}
