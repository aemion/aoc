<?php

declare(strict_types=1);

namespace App\Y2024\Model;

final readonly class Matrix2DInt
{
    public function __construct(
        public int $a,
        public int $b,
        public int $c,
        public int $d
    ) {
    }
}
