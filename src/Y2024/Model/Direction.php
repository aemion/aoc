<?php

declare(strict_types=1);

namespace App\Y2024\Model;

enum Direction
{
    case Top;
    case Bottom;
    case Left;
    case Right;

    public function getVector2D(): Vector2DInt
    {
        return match ($this) {
            self::Top => new Vector2DInt(-1, 0),
            self::Bottom => new Vector2DInt(1, 0),
            self::Left => new Vector2DInt(0, -1),
            self::Right => new Vector2DInt(0, 1),
        };
    }

    /**
     *  @return list<Vector2DInt>
     */
    public static function vectors(): array
    {
        return array_map(static fn(Direction $d) => $d->getVector2D(), self::cases());
    }

    /**
     *  @return list<Vector2DInt>
     */
    public static function diagonals(): array
    {
        return [
            self::Top->getVector2D()->addVector2D(self::Left->getVector2D()),
            self::Top->getVector2D()->addVector2D(self::Right->getVector2D()),
            self::Bottom->getVector2D()->addVector2D(self::Left->getVector2D()),
            self::Bottom->getVector2D()->addVector2D(self::Right->getVector2D()),
        ];
    }


    /**
     *  @return list<Vector2DInt>
     */
    public static function vectorsWithDiagonals(): array
    {
        return array_merge(self::vectors(), self::diagonals());
    }
}
