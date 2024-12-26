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

    public function getOrthogonalVectors(): array
    {
        $rotationMatrix90 = new Matrix2DInt(0, 1, -1, 0);
        $orthogonalVectors = [$this->multiplyMatrix2D($rotationMatrix90)];
        $orthogonalVectors[] = $orthogonalVectors[0]->multiplyScalar(-1);

        return $orthogonalVectors;
    }

    public function multiplyScalar(int $a): Vector2DInt
    {
        return new Vector2DInt($this->x * $a, $this->y * $a);
    }

    public function scalarProduct(Vector2DInt $b): int
    {
        return $this->x * $b->x + $this->y * $b->y;
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

    public function substractVector2D(Vector2DInt $vector2D): Vector2DInt
    {
        return new Vector2DInt($this->x - $vector2D->x, $this->y - $vector2D->y);
    }

    public function __toString(): string
    {
        return \sprintf('(%s,%s)', $this->x, $this->y);
    }

    public function equals(Vector2DInt $vector2D): bool
    {
        return $this->x === $vector2D->x && $this->y === $vector2D->y;
    }
}
