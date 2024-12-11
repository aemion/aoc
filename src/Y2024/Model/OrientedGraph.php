<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class OrientedGraph
{
    private array $nodes = [];

    public function addNode(NodeInterface $node): void
    {
        if (isset($this->nodes[$node->getId()])) {
            throw new \Exception('Node already exists.');
        }
        $this->nodes[$node->getId()] = $node;
    }

    public function getNode(string|int $id): ?NodeInterface
    {
        return $this->nodes[$id] ?? null;
    }

    public function addEdge(NodeInterface $from, NodeInterface $to): void
    {
        $from->addEdge($to);
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }
}
