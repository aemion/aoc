<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

final class Day23 extends AbstractSolver
{
    private array $connections;
    private Graph $network;

    private array $cache = [];

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->connections = [];
        while (!feof($file)) {
            $line = trim(fgets($file));
            $this->connections[] = explode('-', $line);
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $this->network = new Graph();

        foreach ($this->connections as $connection) {
            [$a, $b] = $connection;
            $aVertex = $this->network->createVertex($a, true);
            $bVertex = $this->network->createVertex($b, true);
            $edge = $aVertex->createEdge($bVertex);
            $edge->setWeight(1);
        }
    }

    public function firstStar(): string
    {
        $vertices = $this->network->getVertices();
        $threeLoops = [];
        /** @var Vertex $vertex */
        foreach ($vertices as $vertex) {
            if (!str_starts_with($vertex->getId(), 't')) {
                continue;
            }

            $adjacentVertices = $vertex->getVerticesEdgeTo();
            $adjacentIds = [];
            foreach ($adjacentVertices as $adjacentVertex) {
                $adjacentIds[] = $adjacentVertex->getId();
            }

            foreach ($adjacentVertices as $adjacentVertex) {
                $level2AjacentVertices = $adjacentVertex->getVerticesEdgeTo();
                foreach ($level2AjacentVertices as $level2AdjacentVertex) {
                    if (\in_array($level2AdjacentVertex->getId(), $adjacentIds, true)) {
                        $validLoop = [$vertex->getId(), $adjacentVertex->getId(), $level2AdjacentVertex->getId()];
                        sort($validLoop);
                        $threeLoops[] = implode(',', $validLoop);
                    }
                }
            }
        }

        $threeLoops = array_unique($threeLoops);

        return (string) \count($threeLoops);
    }

    // Optimization needed (~1m30 execution time...)
    public function secondStar(): string
    {
        $maxSize = 0;
        $computersInNetwork = [];
        $vertices = $this->network->getVertices();

        $initialP = [];
        /** @var Vertex $vertex */
        foreach ($vertices as $vertex) {
            $initialP[$vertex->getId()] = $vertex;
        }

        $this->bronKerbosch([], $initialP, []);

        foreach ($this->maximalCliques as $clique) {
            if (\count($clique) > $maxSize) {
                $maxSize = \count($clique);
                $computersInNetwork = $clique;
            }
        }

        $result = [];
        foreach ($computersInNetwork as $vertex) {
            $result[] = $vertex->getId();
        }

        sort($result);

        return implode(',', $result);
    }

    private array $maximalCliques = [];

    private function bronKerbosch(array $r, array $p, array $x): void
    {
        if (empty($p) && empty($x)) {
            $this->maximalCliques[] = $r;
        }

        /** @var Vertex $vertex */
        foreach ($p as $vertex) {
            $neighbours = $vertex->getVerticesEdge();
            $indexedNeighbours = [];
            foreach ($neighbours as $neighbour) {
                $indexedNeighbours[$neighbour->getId()] = $neighbour;
            }
            $this->bronKerbosch(
                [...$r, $vertex],
                array_uintersect_assoc($p, $indexedNeighbours, $this->compareVertices(...)),
                array_uintersect_assoc($x, $indexedNeighbours, $this->compareVertices(...))
            );
            unset($p[$vertex->getId()]);
            $x[$vertex->getId()] = $vertex;
        }
    }

    private function compareVertices(Vertex $a, Vertex $b): int
    {
        return strcmp($a->getId(), $b->getId());
    }
}
