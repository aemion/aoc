<?php

declare(strict_types=1);

namespace App\Y2024\Model;

use Fhaculty\Graph\Edge\Base;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

class DijkstraSolver
{
    private array $distances;

    private array $predecessors;

    private array $visited;
    private array $alternatePaths;

    // TODO should be constructor params
    public function solve(Graph $graph, Vertex $start): void
    {
        $this->init($graph, $start);
        $queue = new \SplPriorityQueue();
        $queue->insert($start, 0);

        while (!$queue->isEmpty()) {
            /** @var Vertex $vertex */
            $vertex = $queue->extract();

            if ($this->visited[$vertex->getId()]) {
                continue;
            }

            $this->visited[$vertex->getId()] = true;
            /** @var Base $edge */
            foreach ($vertex->getEdgesOut() as $edge) {
                $toVertex = $edge->getVertexToFrom($vertex);
                if (!$this->visited[$toVertex->getId()]) {
                    $distance = $this->distances[$vertex->getId()] + $edge->getWeight();
                    if ($distance < $this->distances[$toVertex->getId()]) {
                        $this->predecessors[$toVertex->getId()] = $vertex;
                        $this->distances[$toVertex->getId()] = $distance;
                        $queue->insert($toVertex, -$distance);
                    } elseif ($distance === $this->distances[$toVertex->getId()]) {
                        if (!isset($this->alternatePaths[$toVertex->getId()])) {
                            $this->alternatePaths[$toVertex->getId()] = [];
                        }
                        $this->alternatePaths[$toVertex->getId()][] = $vertex;
                    }
                }
            }
        }
    }

    public function getAllPossibleVertices(Vertex $start, Vertex $end): array
    {
        $shortest = $this->findShortestPath($start, $end);
        $result = [];
        $visited = [];
        $queue = new \SplQueue();
        foreach ($shortest as $vertex) {
            $queue->enqueue($vertex);
            $visited[$vertex->getId()] = true;
        }
        while (!$queue->isEmpty()) {
            $vertex = $queue->dequeue();
            $result[] = $vertex;
            $alternatives = $this->alternatePaths[$vertex->getId()] ?? [];
            foreach ($alternatives as $alternative) {
                if (!isset($visited[$alternative->getId()])) {
                    $visited[$alternative->getId()] = true;
                    $queue->enqueue($alternative);
                }

                $otherPath = $this->findShortestPath($start, $alternative);
                foreach ($otherPath as $vertex) {
                    if (!isset($visited[$vertex->getId()])) {
                        $visited[$vertex->getId()] = true;
                        $queue->enqueue($vertex);
                    }
                }
            }
        }

        return $result;
    }

    public function findShortestPath(Vertex $start, Vertex $end): array
    {
        $result = [];
        $vertex = $end;
        while ($vertex->getId() !== $start->getId()) {
            array_unshift($result, $vertex);
            $vertex = $this->predecessors[$vertex->getId()];
        }
        array_unshift($result, $start);

        return $result;
    }

    private function init(Graph $graph, Vertex $start): void
    {
        $this->distances = [];
        foreach ($graph->getVertices() as $vertex) {
            $this->distances[$vertex->getId()] = INF;
            $this->visited[$vertex->getId()] = false;
        }

        $this->distances[$start->getId()] = 0;
        $this->predecessors = [];
    }

    public function getDistances(): array
    {
        return $this->distances;
    }
}
