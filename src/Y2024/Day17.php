<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Day17Computer;

final class Day17 extends AbstractSolver
{
    private Day17Computer $computer;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        [, $a] = explode(': ', trim(fgets($file)));
        [, $b] = explode(': ', trim(fgets($file)));
        [, $c] = explode(': ', trim(fgets($file)));
        fgets($file);
        [, $operatorsAndOperands] = explode(': ', trim(fgets($file)));
        $this->computer = new Day17Computer($operatorsAndOperands, (int) $a, (int) $b, (int) $c);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $this->computer->execute();

        return $this->computer->getOutput();
    }

    public function secondStar(): string
    {
        return (string) $this->computer->resolve();
    }
}
