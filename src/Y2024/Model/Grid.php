<?php

declare(strict_types=1);

namespace App\Y2024\Model;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @template T
 */
class Grid
{
    private int $xMax;
    private int $yMax;

    /**
     * @param list<list<T>> $grid
     */
    public function __construct(
        private array $grid,
    ) {
        $this->xMax = \count($this->grid);
        $this->yMax = \count($this->grid[0]);
    }

    /**
     * @return T
     */
    public function getValue(Vector2DInt $vector): mixed
    {
        return $this->grid[$vector->x][$vector->y];
    }

    /**
     * @return T|null
     */
    public function tryGetValue(Vector2DInt $vector): mixed
    {
        return $this->grid[$vector->x][$vector->y] ?? null;
    }

    /**
     * @param T $value
     */
    public function setValue(Vector2DInt $vector, mixed $value): void
    {
        $this->grid[$vector->x][$vector->y] = $value;
    }

    public function isInside(Vector2DInt $vector): bool
    {
        return $vector->x >= 0 && $vector->y >= 0 && $vector->x < $this->xMax && $vector->y < $this->yMax;
    }

    /**
     * @return \Generator<Vector2DInt, T>
     */
    public function getCells(): \Generator
    {
        foreach ($this->grid as $x => $line) {
            foreach ($line as $y => $value) {
                yield new Vector2DInt($x, $y) => $value;
            }
        }
    }

    public function toArray(): array
    {
        return $this->grid;
    }

    public function print(OutputInterface $output): void
    {
        foreach ($this->grid as $line) {
            $output->writeln(implode('', $line));
        }
    }

    public function getXMax(): int
    {
        return $this->xMax;
    }

    public function getYMax(): int
    {
        return $this->yMax;
    }
}
