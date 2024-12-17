<?php

declare(strict_types=1);

namespace App\Y2024\Model;

interface NodeInterface
{
    public function getId(): string|int;

    /**
     * @return list<Edge>
     */
    public function getEdges(): array;

    public function addEdge(NodeInterface $to, int $weight = 0): void;

    public function getEdge(NodeInterface $to): Edge;
}
