<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Vector2DInt;

final class Day21 extends AbstractSolver
{
    /**
     * @var list<string>
     */
    private array $codes;

    /**
     * @var array<string, array<string, Vector2DInt>>
     */
    private array $keypads = [];

    private array $revertKeypads = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->codes = [];
        while (!feof($file)) {
            $this->codes[] = trim(fgets($file));
        }
    }

    public function preSolve(): void
    {
        [$keypad, $revert] = $this->buildKeypad([
            [7, 8, 9],
            [4, 5, 6],
            [1, 2, 3],
            [null, 0, 'A'],
        ]);

        $this->keypads['num'] = $keypad;
        $this->revertKeypads['num'] = $revert;
        [$keypad, $revert] = $this->buildKeypad([
            [null, '^', 'A'],
            ['<', 'v', '>'],
        ]);
        $this->keypads['dir'] = $keypad;
        $this->revertKeypads['dir'] = $revert;
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    private function movementsToString(array $movements): string
    {
        $convertedMovements = [];
        foreach ($movements as $movement) {
            $convertedMovements[] = match ($movement) {
                Direction::Right => '>',
                Direction::Left => '<',
                Direction::Top => '^',
                Direction::Bottom => 'v',
            };
        }

        return implode('', $convertedMovements);
    }

    /**
     * @param array<string, Vector2DInt> $keypad
     */
    private function shortestPath(string $startKey, string $endKey, array $keypad): string
    {
        $start = $keypad[$startKey];
        $end = $keypad[$endKey];
        $movement = $end->substractVector2D($start);

        $direction = $movement->x > 0 ? Direction::Bottom : Direction::Top;
        $verticalMovement = array_fill(0, abs($movement->x), $direction);
        $direction = $movement->y > 0 ? Direction::Right : Direction::Left;
        $horizontalMovement = array_fill(0, abs($movement->y), $direction);

        $gap = $keypad['gap'];

        if ($end->y > $start->y && !(new Vector2DInt($end->x, $start->y))->equals($gap)) {
            // Safe to move vertically first if heading right and corner point isn't the gap
            $movements = [...$verticalMovement, ...$horizontalMovement];
        } elseif (!(new Vector2DInt($start->x, $end->y))->equals($gap)) {
            // Safe to move horizontally first if corner point isn't the gap
            $movements = [...$horizontalMovement, ...$verticalMovement];
        } else {
            // Must be safe to move vertically first because we can't be in same column as gap.
            $movements = [...$verticalMovement, ...$horizontalMovement];
        }

        return $this->movementsToString($movements) . 'A';
    }

    private function getRobotInstructions(string $code, array $keypad): string
    {
        $key = 'A';
        $path = '';
        foreach (str_split($code) as $character) {
            $path .= $this->shortestPath($key, $character, $keypad);
            $key = $character;
        }

        return $path;
    }

    public function firstStar(): string
    {
        $total = 0;
        // $solutions = [
        //     '029A' => '<vA<AA>>^AvAA<^A>A<v<A>>^AvA^A<vA>^A<v<A>^A>AAvA^A<v<A>A>^AAAvA<^A>A',
        //     '980A' => '<v<A>>^AAAvA^A<vA<AA>>^AvAA<^A>A<v<A>A>^AAAvA<^A>A<vA>^A<A>A',
        //     '179A' => '<v<A>>^A<vA<A>>^AAvAA<^A>A<v<A>>^AAvA^A<vA>^AA<A>A<v<A>A>^AAAvA<^A>A',
        //     '456A' => '<v<A>>^AA<vA<A>>^AAvAA<^A>A<vA>^A<A>A<vA>^A<A>A<v<A>A>^AAvA<^A>A',
        //     '379A' => '<v<A>>^AvA^A<vA<AA>>^AAvA<^A>AAvA^A<vA>^AA<A>A<v<A>A>^AAAvA<^A>A',
        // ];
        // dump($this->revertCode($solutions['029A']));

        foreach ($this->codes as $code) {
            $robot1 = $this->getRobotInstructions($code, $this->keypads['num']);
            $robot2 = $this->getRobotInstructions($robot1, $this->keypads['dir']);
            $robot3 = $this->getRobotInstructions($robot2, $this->keypads['dir']);
            $codeValue = (int) str_replace('A', '', $code);
            $total += \strlen($robot3) * $codeValue;
        }

        return (string) $total;
    }

    public function revertCode(string $code): array
    {
        $oneKeypad = $this->revertString($this->revertKeypads['dir'], $code);
        $twoKeypad = $this->revertString($this->revertKeypads['dir'], $oneKeypad);
        $numKeypad = $this->revertString($this->revertKeypads['num'], $twoKeypad);

        return [$code, $oneKeypad, $twoKeypad, $numKeypad];
    }

    public function revertString(array $keypad, string $string): string
    {
        /** @var Vector2DInt $position */
        $position = $keypad['initialPosition'];
        $chars = str_split($string);
        $next = [];
        foreach ($chars as $char) {
            if ($char === ' ') {
                continue;
            }
            if ($char === 'A') {
                $next[] = $keypad[$position->__toString()]; // TODO revert keypad
            } else {
                $position = $position->addVector2D($this->charToDirection($char)->getVector2D());
            }

            if (!isset($keypad[$position->__toString()])) {
                dump('ERROR!!! ' . $position);
            }
        }

        return implode('', $next);
    }

    private function charToDirection(string $char): Direction
    {
        return match ($char) {
            '>' => Direction::Right,
            '<' => Direction::Left,
            '^' => Direction::Top,
            'v' => Direction::Bottom,
        };
    }

    public function secondStar(): string
    {
        $total = 0;

        return (string) $total;
    }

    private function buildKeypad(array $keypad2D): array
    {
        $keypad = [];
        $revertKeypad = [];
        foreach ($keypad2D as $x => $row) {
            foreach ($row as $y => $value) {
                $position = new Vector2DInt($x, $y);
                if ($value === 'A') {
                    $revertKeypad['initialPosition'] = $position;
                }
                if (null !== $value) {
                    $keypad[(string) $value] = $position;
                    $revertKeypad[$position->__toString()] = (string) $value;
                } else {
                    $keypad['gap'] = $position;
                }
            }
        }

        return [$keypad, $revertKeypad];
    }
}
