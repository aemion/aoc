<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Day17Computer
{
    private array $registers;

    private int $pointer;

    private array $output;
    private array $instructions;

    private int $outputLength;

    private bool $checkMode = false;

    public function __construct(
        private string $rawInstructions,
        int $initialValueRegisterA,
        int $initialValueRegisterB,
        int $initialValueRegisterC
    ) {
        $operatorsAndOperands = explode(',', $this->rawInstructions);
        $numberOfInstructions = \count($operatorsAndOperands) / 2;
        $this->instructions = [];
        for ($i = 0; $i < $numberOfInstructions; $i++) {
            $this->instructions[] = [
                'operator' => (int) $operatorsAndOperands[2 * $i],
                'operand'  => (int) $operatorsAndOperands[2 * $i + 1],
            ];
        }

        $this->resetProgram($initialValueRegisterA, $initialValueRegisterB, $initialValueRegisterC);
    }

    public function resetProgram(int $a, int $b, int $c): void
    {
        $this->registers = [
            'A' => $a,
            'B' => $b,
            'C' => $c,
        ];
        $this->pointer = 0;
        $this->output = [];
        $this->outputLength = 0;
    }

    public function enableCheckMode(): void
    {
        $this->checkMode = true;
    }

    public function compile()
    {
        // TODO optimize the program
    }

    public function execute(int $maxOutput = -1): void
    {
        $end = \count($this->instructions);
        while ($this->pointer < $end) {
            $instruction = $this->getInstruction();
            $continue = $instruction();
            $this->pointer++;
            if ($this->checkMode) {
                if ($continue === false) {
                    $this->pointer = $end + 1;
                } elseif ($maxOutput > 0 && $this->outputLength > $maxOutput) {
                    $this->pointer = $end + 1;
                }
            }
        }
    }

    private function getInstruction(): callable
    {
        $mapping = [
            $this->adv(...),
            $this->bxl(...),
            $this->bst(...),
            $this->jnz(...),
            $this->bxc(...),
            $this->out(...),
            $this->bdv(...),
            $this->cdv(...),
        ];

        return $mapping[$this->instructions[$this->pointer]['operator']];
    }

    // 0
    private function adv(): bool
    {
        $this->registers['A'] = $this->xdv();

        return true;
    }

    // 1
    private function bxl(): bool
    {
        $this->registers['B'] ^= $this->getLiteralOperand();

        return true;
    }

    private function bst(): bool
    {
        $this->registers['B'] = $this->getComboOperand() % 8;

        return true;
    }

    private function jnz(): bool
    {
        $currentA = $this->registers['A'];
        if ($currentA !== 0) {
            $this->pointer = $this->getLiteralOperand() - 1;
        }

        return true;
    }

    private function bxc(): bool
    {
        $this->registers['B'] ^= $this->registers['C'];

        return true;
    }

    private function out(): bool
    {
        $this->output[] = $this->getComboOperand() % 8;
        $this->outputLength++;

        return $this->output[$this->outputLength - 1] === ((int) ($this->rawInstructions[$this->outputLength - 1] ?? -1));
    }

    private function bdv(): bool
    {
        $this->registers['B'] = $this->xdv();

        return true;
    }

    private function cdv(): bool
    {
        $this->registers['C'] = $this->xdv();

        return true;
    }

    private function xdv(): int
    {
        $numerator = $this->registers['A'];
        $denominator = 2 ** $this->getComboOperand();

        return (int) floor($numerator / $denominator);
    }

    private function getLiteralOperand(): int
    {
        return $this->instructions[$this->pointer]['operand'];
    }

    private function getComboOperand(): int
    {
        $literalOperand = $this->getLiteralOperand();

        return match ($literalOperand) {
            0, 1, 2, 3 => $literalOperand,
            4 => $this->registers['A'],
            5 => $this->registers['B'],
            6 => $this->registers['C'],
        };
    }

    public function getOutput(): string
    {
        return implode(',', $this->output);
    }

    public function isAutoGenerated(): bool
    {
        return $this->getOutput() === $this->rawInstructions;
    }

    public function getNumberOfInstructions(): int
    {
        return \count($this->instructions);
    }
}
