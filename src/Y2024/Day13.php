<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Vector2DInt;

final class Day13 extends AbstractSolver
{
    private array $machines;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->machines = [];
        $regex = '/.*X[+=](\d+).*Y[+=](\d+)/';
        while (!feof($file)) {
            $line1 = trim(fgets($file));
            $line2 = trim(fgets($file));
            $line3 = trim(fgets($file));
            $matches = [];
            preg_match($regex, $line1, $matches);
            $a = new Vector2DInt((int) $matches[1], (int) $matches[2]);
            $matches = [];
            preg_match($regex, $line2, $matches);
            $b = new Vector2DInt((int) $matches[1], (int) $matches[2]);
            $matches = [];
            preg_match($regex, $line3, $matches);
            $p = new Vector2DInt((int) $matches[1], (int) $matches[2]);
            $this->machines[] = ['a' => $a, 'b' => $b, 'p' => $p];
            fgets($file);
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $total = 0;
        $costVector = new Vector2DInt(3, 1);
        foreach ($this->machines as $machine) {
            $neededActions = $this->getNeededActions($machine['a'], $machine['b'], $machine['p']);
            $cost = $neededActions ? $costVector->scalarProduct($neededActions) : 0;
            $total += $cost;
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;
        $error = 10000000000000;
        $errorVector = new Vector2DInt($error, $error);
        $costVector = new Vector2DInt(3, 1);
        foreach ($this->machines as $machine) {
            $neededActions = $this->getNeededActions(
                $machine['a'],
                $machine['b'],
                $machine['p']->addVector2D($errorVector)
            );
            $cost = $neededActions ? $costVector->scalarProduct($neededActions) : 0;
            $total += $cost;
        }

        return (string) $total;
    }

    public function f(Vector2DInt $a, Vector2DInt $b): int
    {
        return ($a->x * $b->y) - ($a->y * $b->x);
    }

    public function getNeededActions(Vector2DInt $a, Vector2DInt $b, Vector2DInt $p): ?Vector2DInt
    {
        $fap = $this->f($a, $p);
        $fab = $this->f($a, $b);
        $nb = $fap / $fab;
        if (!\is_int($nb)) {
            return null;
        }
        $na = ($p->x - $nb * $b->x) / $a->x;
        if (!\is_int($na)) {
            return null;
        }

        return new Vector2DInt($na, $nb);
    }
}
