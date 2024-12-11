<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Day11Node implements NodeInterface
{
    /**
     * @var array<string, Day10Node>
     */
    private array $edges = [];

    public function __construct(
        private int $id,
    ) {
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function addEdge(NodeInterface $to): void
    {
        if ($to->getId() === $this->id) {
            throw new \Exception('Cannot add an edge to same node');
        }

        if (!isset($this->edges[$to->getId()])) {
            $this->edges[$to->getId()] = new Day11Edge($to);
        }

        $this->edges[$to->getId()]->incrementWeight();
    }
}
