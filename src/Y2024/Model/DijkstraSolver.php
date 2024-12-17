<?php

declare(strict_types=1);

namespace App\Y2024\Model;

class DijkstraSolver
{
    private array $distances;

    private array $predecessors;

    // TODO should be constructor params
    public function solve(OrientedGraph $graph, NodeInterface $startNode): void
    {
        $this->init($graph, $startNode);

        $remainingNodes = $graph->getNodes();
        while (!empty($remainingNodes)) {
            if (count($remainingNodes) % 1000 === 0) {
                dump(count($remainingNodes));
            }
            $closestNode = $this->findMin($remainingNodes);
            $remainingNodes = array_filter(
                $remainingNodes,
                static fn(NodeInterface $node) => $node->getId() !== $closestNode->getId()
            );

            foreach ($closestNode->getEdges() as $edge) {
                $this->updateDistances($closestNode, $edge->getNodeTo());
            }
        }
    }

    public function findShortestPath(NodeInterface $startNode, NodeInterface $endNode): array
    {
        $result = [];
        $node = $endNode;
        while ($node->getId() !== $startNode->getId()) {
            array_unshift($result, $node);
            $node = $this->predecessors[$node->getId()];
        }
        array_unshift($result, $startNode);

        return $result;
    }

    private function init(OrientedGraph $graph, NodeInterface $startNode): void
    {
        $this->distances = [];
        foreach ($graph->getNodes() as $node) {
            $this->distances[$node->getId()] = INF;
        }

        $this->distances[$startNode->getId()] = 0;
        $this->predecessors = [];
    }

    private function updateDistances(NodeInterface $a, NodeInterface $b): void
    {
        $newPotentialDistance = $this->distances[$a->getId()] + $a->getEdge($b)->getWeight();
        if ($this->distances[$b->getId()] > $newPotentialDistance) {
            $this->distances[$b->getId()] = $newPotentialDistance;
            $this->predecessors[$b->getId()] = $a;
        }
    }

    private function findMin(array $outsideNodes): NodeInterface
    {
        $min = INF;
        $minNode = null;
        foreach ($outsideNodes as $node) {
            if ($this->distances[$node->getId()] < $min) {
                $min = $this->distances[$node->getId()];
                $minNode = $node;
            }
        }

        if ($minNode === null) {
            throw new \Exception('CANNOT FIND MIN NODE');
        }

        return $minNode;
    }

    public function getPredecessors(): array
    {
        return $this->predecessors;
    }

    public function getDistances(): array
    {
        return $this->distances;
    }
}
