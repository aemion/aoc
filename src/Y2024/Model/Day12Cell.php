<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Day12Cell
{
    public ?int $id = null;
    public array $fenceAngles = [];

    public function __construct(
        public readonly string $value,
        public int $fences = 4,
        public bool $visited = false,
    ) {
    }

    public function removeFence(): void
    {
        $this->fences--;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function addAngle(Vector2DInt $direction): void
    {
        $this->fenceAngles[] = $direction;
    }

    public function countAngles(): int
    {
        return \count($this->fenceAngles);
    }
}
