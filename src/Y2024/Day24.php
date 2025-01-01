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
                $operands = [$matches[1], $matches[3]];
                sort($operands);
                $this->operations[$matches[4]] = [
                    'operator' => $matches[2],
                    'operands' => $operands,
                    'out'      => $matches[4],
                ];
            }
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $bits = $this->solveZBits($this->operations, $this->values);

        $result = $this->bitsToInt($bits);

        return (string) $result;
    }

    private function extractResults(string $id): array
    {
        $operations = $this->findByOperand($id);
        if (\count($operations) !== 2) {
            throw new \Exception(\sprintf('Incorrect operation count (%d) for id %s', \count($operations), $id));
        }
        $and = null;
        $xor = null;
        foreach ($operations as $outWire => $operation) {
            if ($operation['operator'] === 'AND') {
                if ($and !== null) {
                    throw new \Exception('Too many AND operators for id ' . $id);
                }
                $and = $outWire;
            } elseif ($operation['operator'] === 'XOR') {
                if ($xor !== null) {
                    throw new \Exception('Too many XOR operators for id ' . $id);
                }
                $xor = $outWire;
            } else {
                throw new \Exception('Unknown operator for id ' . $id . ' / operator ' . $operation['operator']);
            }
        }

        return ['xor' => $xor, 'and' => $and];
    }

    public function secondStar(): string
    {
        $swaps = [];

        foreach ($swaps as $swap) {
            $this->swap($swap[0], $swap[1]);
        }

        $previousCarry = 'rhk';
        for ($i = 1; $i < 45; $i++) {
            $id = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            ['and' => $leftHandCarry, 'xor' => $sumXY] = $this->extractResults('x' . $id);
            ['and' => $rightHandCarry, 'xor' => $z] = $this->extractResults($previousCarry);

            $zId = 'z' . $id;
            if ($z !== $zId) {
                $swaps[] = $this->swap($zId, $z);
                ['and' => $leftHandCarry, 'xor' => $sumXY] = $this->extractResults('x' . $id);
                ['and' => $rightHandCarry, 'xor' => $z] = $this->extractResults($previousCarry);
            }

            $carryOperations = $this->findByOperand($leftHandCarry);

            if (\count($carryOperations) !== 1) {
                $swaps[] = $this->swap($leftHandCarry, $sumXY);

                $carryOperations = $this->findByOperand($rightHandCarry);
            }
            $previousCarry = array_key_first($carryOperations);
            $operation = $carryOperations[$previousCarry];
            if ($operation['operator'] !== 'OR') {
                throw new \Exception(
                    'Unknown operator for id ' . $previousCarry . ' / operator ' . $operation['operator']
                );
            }
        }
        $flatSwaps = [];

        foreach ($swaps as $swap) {
            $flatSwaps[] = $swap[0];
            $flatSwaps[] = $swap[1];
        }
        sort($flatSwaps);

        return implode(',', $flatSwaps);
    }

    private function swap(string $a, string $b): array
    {
        $swap = $this->operations[$a];
        $this->operations[$a] = $this->operations[$b];
        $this->operations[$b] = $swap;

        return [$a, $b];
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

    private function findByOperand(string $operand): array
    {
        return array_filter(
            $this->operations,
            static fn(array $operation) => \in_array($operand, $operation['operands'], true)
        );
    }
}
