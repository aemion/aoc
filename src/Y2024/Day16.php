<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Day16Node;
use App\Y2024\Model\DijkstraSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Matrix2DInt;
use App\Y2024\Model\NodeInterface;
use App\Y2024\Model\OrientedGraph;
use App\Y2024\Model\Vector2DInt;

final class Day16 extends AbstractSolver
{
    private Grid $grid;
    private Vector2DInt $start;
    private Vector2DInt $end;

    private DijkstraSolver $solver;

    private NodeInterface $startNode;
    private NodeInterface $endNode;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $grid = [];
        $x = 0;
        while (!feof($file)) {
            $line = trim(fgets($file));
            $characters = str_split($line);
            $startIndex = array_search('S', $characters, true);

            if ($startIndex !== false) {
                $this->start = new Vector2DInt($x, $startIndex);
            }
            $endIndex = array_search('E', $characters, true);

            if ($endIndex !== false) {
                $this->end = new Vector2DInt($x, $endIndex);
            }
            $grid[] = $characters;
            $x++;
        }

        $this->grid = new Grid($grid);
    }

    public function preSolve(): void
    {
        $graph = new OrientedGraph();
        $directions = Direction::vectors();
        $rotationMatrix90 = new Matrix2DInt(0, 1, -1, 0);
        foreach ($this->grid->getCells() as $position => $value) {
            if ($value === '#') {
                continue;
            }

            $possibleDirections = [];
            foreach ($directions as $direction) {
                $nextPosition = $position->addVector2D($direction);

                if ($this->grid->getValue($nextPosition) === '#') {
                    continue;
                }
                $possibleDirections[] = $direction;
                $nextNodeId = $this->getNodeId($nextPosition, $direction);
                $currentNodeId = $this->getNodeId($position, $direction);
                $currentNode = $graph->getNode($currentNodeId);
                if ($currentNode === null) {
                    $currentNode = new Day16Node($currentNodeId);
                    $graph->addNode($currentNode);
                }
                $nextNode = $graph->getNode($nextNodeId);
                if ($nextNode === null) {
                    $nextNode = new Day16Node($nextNodeId);
                    $graph->addNode($nextNode);
                }

                $graph->addEdge($currentNode, $nextNode, 1);
            }
            // dump($position, $possibleDirections);

            // if (\count($possibleDirections) > 2
            //     || !$possibleDirections[0]->equals($possibleDirections[1]->multiplyScalar(-1))) {
            foreach ($directions as $direction) {
                $orthogonalDirections = [$direction->multiplyMatrix2D($rotationMatrix90)];
                $orthogonalDirections[] = $orthogonalDirections[0]->multiplyScalar(-1);
                foreach ($orthogonalDirections as $orthogonalDirection) {
                    $nextNodeId = $this->getNodeId($position, $orthogonalDirection);
                    $currentNodeId = $this->getNodeId($position, $direction);
                    $currentNode = $graph->getNode($currentNodeId);
                    if ($currentNode === null) {
                        $currentNode = new Day16Node($currentNodeId);
                        $graph->addNode($currentNode);
                    }
                    $nextNode = $graph->getNode($nextNodeId);
                    if ($nextNode === null) {
                        $nextNode = new Day16Node($nextNodeId);
                        $graph->addNode($nextNode);
                    }
                    $graph->addEdge($currentNode, $nextNode, 1000);
                }
                // }
            }
        }

        $endNode = new Day16Node('END');
        $graph->addNode($endNode);
        foreach ($directions as $direction) {
            $nodeId = $this->getNodeId($this->end, $direction);
            $node = $graph->getNode($nodeId);
            if ($node === null) {
                $node = new Day16Node($nodeId);
                $graph->addNode($node);
            }

            $graph->addEdge($node, $endNode, 0);

            $nodeId = $this->getNodeId($this->start, $direction);
            $node = $graph->getNode($nodeId);
            if ($node === null) {
                $node = new Day16Node($nodeId);
                $graph->addNode($node);
            }
        }

        $this->startNode = $graph->getNode($this->getNodeId($this->start, Direction::Right->getVector2D()));
        $this->endNode = $graph->getNode('END');
        if ($this->startNode === null || $this->endNode === null) {
            throw new \RuntimeException('Start or end node not found');
        }
        $this->solver = new DijkstraSolver();
        $this->solver->solve($graph, $this->startNode);
    }

    private function getNodeId(Vector2DInt $position, Vector2DInt $direction): string
    {
        return $position->__toString() . '|' . $direction->__toString();
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $distances = $this->solver->getDistances();

        return (string) $distances[$this->endNode->getId()];
    }

    public function secondStar(): string
    {
        $path = $this->solver->findShortestPath($this->startNode, $this->endNode);

        // Compte les angles et ne prend en compte qu'un seul des chemins le plus court donc ca va pas...
        foreach ($path as $node) {
            dump($node->getId());
        }

        return (string) \count($path);
    }
}
