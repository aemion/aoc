<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\DijkstraSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Vector2DInt;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

final class Day16 extends AbstractSolver
{
    private Grid $grid;
    private Vector2DInt $start;
    private Vector2DInt $end;

    private Vertex $endNode;
    private Vertex $startNode;

    private DijkstraSolver $solver;

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
        $graph = new Graph();
        $directions = Direction::vectors();
        foreach ($this->grid->getCells() as $position => $value) {
            if ($value === '#') {
                continue;
            }

            foreach ($directions as $direction) {
                $nextPosition = $position->addVector2D($direction);

                if ($this->grid->getValue($nextPosition) === '#') {
                    continue;
                }
                $nextNodeId = $this->getNodeId($nextPosition, $direction);
                $currentNodeId = $this->getNodeId($position, $direction);

                $currentNode = $graph->createVertex($currentNodeId, true);
                $nextNode = $graph->createVertex($nextNodeId, true);
                $edge = $currentNode->createEdgeTo($nextNode);
                $edge->setWeight(1);
            }
            foreach ($directions as $direction) {
                $orthogonalDirections = $direction->getOrthogonalVectors();
                foreach ($orthogonalDirections as $orthogonalDirection) {
                    $nextNodeId = $this->getNodeId($position, $orthogonalDirection);
                    $currentNodeId = $this->getNodeId($position, $direction);
                    $currentNode = $graph->createVertex($currentNodeId, true);
                    $nextNode = $graph->createVertex($nextNodeId, true);
                    $edge = $currentNode->createEdgeTo($nextNode);
                    $edge->setWeight(1000);
                }
            }
        }

        $endNode = $graph->createVertex('END');
        foreach ($directions as $direction) {
            $nodeId = $this->getNodeId($this->end, $direction);
            $node = $graph->createVertex($nodeId, true);

            $edge = $node->createEdgeTo($endNode);
            $edge->setWeight(0);

            $nodeId = $this->getNodeId($this->start, $direction);
            $graph->createVertex($nodeId, true);
        }

        $this->startNode = $graph->getVertex($this->getNodeId($this->start, Direction::Right->getVector2D()));
        $this->endNode = $graph->getVertex('END');
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
        $cells = [];
        $vertices = $this->solver->getAllPossibleVertices($this->startNode, $this->endNode);
        foreach ($vertices as $vertex) {
            [$cell] = explode('|', $vertex->getId());
            $cells[] = $cell;
        }

        $uniqueCells = array_unique($cells);

        return (string) (\count($uniqueCells) - 1);
    }
}
