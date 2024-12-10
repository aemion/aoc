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
}

