<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class Day10Node implements NodeInterface
{
    /**
     * @var array<string, Day10Node>
     */
    private array $edges = [];
    private ?array $foundNines = null;

    private ?int $rating = null;

    public function __construct(
        private string $id,
        private int $value,
    ) {
        if ($this->value === 9) {
            $this->foundNines = [$this->id];
            $this->rating = 1;
        }
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

        $this->edges[$to->getId()] = $to;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function findNines(): array
    {
        if ($this->foundNines !== null) {
            return $this->foundNines;
        }

        $foundNines = [];
        foreach ($this->edges as $edge) {
            $foundNines = [...$foundNines, ...$edge->findNines()];
        }

        $this->foundNines = array_unique($foundNines);

        return $this->foundNines;
    }

    public function calculateRating(): int
    {
        if ($this->rating !== null) {
            return $this->rating;
        }

        $this->rating = 0;
        foreach ($this->edges as $edge) {
            $this->rating += $edge->calculateRating();
        }

        return $this->rating;
    }

    public function getScore(): ?int
    {
        return \count($this->foundNines);
    }

    public function getEdge(NodeInterface $to): Edge
    {
        throw new \RuntimeException('Not implemented');
    }
}
