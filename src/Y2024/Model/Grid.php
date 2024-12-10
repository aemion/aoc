<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Grid
{
    private int $xMax;
    private int $yMax;

    public function __construct(
        private array $grid,
    ) {
        $this->xMax = \count($this->grid);
        $this->yMax = \count($this->grid[0]);
    }

    public function getValue(Vector2DInt $vector): string
    {
        return $this->grid[$vector->x][$vector->y];
    }

    public function tryGetValue(Vector2DInt $vector): ?string
    {
        return $this->grid[$vector->x][$vector->y] ?? null;
    }

    public function setValue(Vector2DInt $vector, string $value): void
    {
        $this->grid[$vector->x][$vector->y] = $value;
    }

    public function isInside(Vector2DInt $vector): bool
    {
        return $vector->x >= 0 && $vector->y >= 0 && $vector->x < $this->xMax && $vector->y < $this->yMax;
    }

    /**
     * @return \Generator<Vector2DInt, string>
     */
    public function getCells(): \Generator
    {
        foreach ($this->grid as $x => $line) {
            foreach ($line as $y => $value) {
                yield new Vector2DInt($x, $y) => $value;
            }
        }
    }
}
