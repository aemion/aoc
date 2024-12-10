<?php

declare(strict_types=1);

namespace App\Y2024\Model;

final readonly class Vector2DInt implements \Stringable
{
    public function __construct(
        public int $x,
        public int $y
    ) {
    }

    public function multiplyMatrix2D(Matrix2DInt $matrix): Vector2DInt
    {
        return new Vector2DInt(
            $this->x * $matrix->a + $this->y * $matrix->b,
            $this->x * $matrix->c + $this->y * $matrix->d
        );
    }

    public function addVector2D(Vector2DInt $vector2D): Vector2DInt
    {
        return new Vector2DInt($this->x + $vector2D->x, $this->y + $vector2D->y);
    }

    public function __toString(): string
    {
        return \sprintf('(%s,%s)', $this->x, $this->y);
    }
}
