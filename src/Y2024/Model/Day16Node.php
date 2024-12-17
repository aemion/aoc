<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Day16Node implements NodeInterface
{
    /**
     * @var array<string, Edge>
     */
    private array $edges = [];

    public function __construct(
        private string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function addEdge(NodeInterface $to, int $weight = 0): void
    {
        if ($to->getId() === $this->id) {
            throw new \Exception('Cannot add an edge to same node');
        }

        if (isset($this->edges[$to->getId()])) {
            throw new \Exception('Cannot add an edge twice');
        }

        $this->edges[$to->getId()] = new Edge($to, $weight);
    }

    public function getEdge(NodeInterface $to): Edge
    {
        return $this->edges[$to->getId()];
    }
}
