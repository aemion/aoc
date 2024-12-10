<?php

declare(strict_types=1);

namespace App;

abstract class Solver
{
    public function isFirstStarSolved(): bool
    {
        return false;
    }

    abstract public function loadInput(string $path): void;

    public function preSolve(): void
    {
    }

    abstract public function firstStar(): string;

    abstract public function secondStar(): string;
}
