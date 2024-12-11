<?php

declare(strict_types=1);

namespace App\Y2024\Model;

interface NodeInterface
{
    public function getId(): string|int;

    public function getEdges(): array;

    public function addEdge(NodeInterface $to): void;
}
