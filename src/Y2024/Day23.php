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
        /** @var Vertex $vertex */
        foreach ($vertices as $vertex) {
            $group = $this->findBiggestGroupIncludingVertices([$vertex]);
            if (\count($group) > $maxSize) {
                $maxSize = \count($group);
                $computersInNetwork = $group;
            }
        }

        $result = [];
        foreach ($computersInNetwork as $vertex) {
            $result[] = $vertex->getId();
        }

        sort($result);

        return implode(',', $result);
    }

    private function findBiggestGroupIncludingVertices(array $vertices): array
    {
        $keyCache = $this->buildCacheKey($vertices, 'group');
        if (isset($this->cache[$keyCache])) {
            return $this->cache[$keyCache];
        }

        $connectedVertices = $this->findVerticesConnectedTo($vertices);
        if (empty($connectedVertices)) {
            $this->cache[$keyCache] = $vertices;

            return $vertices;
        }

        if (\count($connectedVertices) === 1) {
            $vertices[] = $connectedVertices[0];

            $this->cache[$keyCache] = $this->findBiggestGroupIncludingVertices($vertices);

            return $this->cache[$keyCache];
        }

        $maxSize = \count($vertices);
        $biggestGroup = $vertices;
        foreach ($connectedVertices as $vertex) {
            $group = $this->findBiggestGroupIncludingVertices([...$vertices, $vertex]);
            if (\count($group) > $maxSize) {
                $biggestGroup = $group;
                $maxSize = \count($group);
            }
        }

        $this->cache[$keyCache] = $biggestGroup;

        return $this->cache[$keyCache];
    }

    /**
     * @param list<Vertex> $vertices
     *
     * @return list<Vertex>
     */
    private function findVerticesConnectedTo(array $vertices): array
    {
        $keyCache = $this->buildCacheKey($vertices, 'connection');

        if (isset($this->cache[$keyCache])) {
            return $this->cache[$keyCache];
        }
        // On suppose que les verices sont déjà connectées
        $verticesIds = [];
        foreach ($vertices as $vertex) {
            $verticesIds[] = $vertex->getId();
        }
        $potentialVertices = [];
        foreach ($vertices as $vertex) {
            $adjacentVertices = $vertex->getVerticesEdge()->getVerticesDistinct();
            foreach ($adjacentVertices as $potentialVertex) {
                if (\in_array($potentialVertex->getId(), $verticesIds, true)) {
                    continue;
                }

                if ($vertex->hasEdgeTo($potentialVertex)) {
                    if (!isset($potentialVertices[$potentialVertex->getId()])) {
                        $potentialVertices[$potentialVertex->getId()] = ['count' => 0, 'vertex' => $potentialVertex];
                    }

                    $potentialVertices[$potentialVertex->getId()]['count']++;
                }
            }
        }

        $numberOfVertices = \count($vertices);
        $result = [];
        foreach ($potentialVertices as $potentialVertex) {
            if ($potentialVertex['count'] === $numberOfVertices) {
                $result[] = $potentialVertex['vertex'];
            }
        }

        $this->cache[$keyCache] = $result;

        return $this->cache[$keyCache];
    }

    private function buildCacheKey(array $vertices, string $namespace): string
    {
        $ids = [];
        foreach ($vertices as $vertex) {
            $ids[] = $vertex->getId();
        }
        sort($ids);

        return $namespace . '_' . implode(',', $ids);
    }
}
