<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Day11Edge
{
    private int $weight = 0;

    public function __construct(
        private readonly NodeInterface $nodeTo
    ) {
    }

    public function incrementWeight(): void
    {
        $this->weight++;
    }

    public function getNodeTo(): NodeInterface
    {
        return $this->nodeTo;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }
}
