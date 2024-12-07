<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day3 extends AbstractSolver
{
    private string $input = '';

    public function loadInput(string $path): void
    {
        $this->input = '';
        $file = fopen($path, 'rb');
        while (!feof($file)) {
            $line = trim(fgets($file));
            $this->input .= $line;
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $regex = '(mul\((\d+),(\d+)\))';
        $matches = [];
        preg_match_all($regex, $this->input, $matches);
        $total = 0;
        foreach ($matches[0] as $i => $match) {
            $total += ($matches[1][$i] * $matches[2][$i]);
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $regex = '/(do\(\)|don\'t\(\)|mul\((\d+),(\d+)\))/';

        $matches = [];
        preg_match_all($regex, $this->input, $matches);
        $isCounting = true;
        $total = 0;
        foreach ($matches[0] as $i => $match) {
            if ($match === 'don\'t()') {
                $isCounting = false;
            } elseif ($match === 'do()') {
                $isCounting = true;
            } elseif ($isCounting) {
                $total += ($matches[2][$i] * $matches[3][$i]);
            }
        }

        return (string) $total;
    }
}
