<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Edge
{
    public function __construct(
        private readonly NodeInterface $nodeTo,
        private int $weight = 0
    ) {
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
