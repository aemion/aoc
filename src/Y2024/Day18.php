<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Vector2DInt;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ShortestPath\BreadthFirst;

final class Day18 extends AbstractSolver
{
    private Grid $grid;
    private int $numberOfBytes;
    /**
     * @var list<Vector2DInt>
     */
    private array $corruptedBytes = [];

    private int $size;

    private Graph $graph;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $this->size = (int) trim(fgets($file));
        $this->numberOfBytes = (int) trim(fgets($file));

        $this->grid = new Grid(array_fill(0, $this->size + 1, array_fill(0, $this->size + 1, '.')));
        while (!feof($file)) {
            $line = trim(fgets($file));
            [$x, $y] = explode(',', $line);
            $this->corruptedBytes[] = new Vector2DInt((int) $x, (int) $y);
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $this->makeBytesFall();
        $this->graph = $this->createGraph();
    }

    public function firstStar(): string
    {
        $startingPoint = $this->graph->getVertex((new Vector2DInt(0, 0))->__toString());
        $endPoint = $this->graph->getVertex((new Vector2DInt($this->size, $this->size))->__toString());
        $solver = new BreadthFirst($startingPoint);
        $distance = $solver->getDistance($endPoint);

        return (string) $distance;
    }

    private function makeBytesFall(): void
    {
        for ($i = 0; $i < $this->numberOfBytes; $i++) {
            $this->grid->setValue($this->corruptedBytes[$i], '#');
        }
    }

    private function createGraph(): Graph
    {
        $graph = new Graph();
        foreach ($this->grid->getCells() as $position => $cell) {
            $graph->createVertex($position->__toString());
        }

        $directions = Direction::vectors();
        foreach ($this->grid->getCells() as $position => $cell) {
            foreach ($directions as $direction) {
                $nextPosition = $position->addVector2D($direction);

                $nextValue = $this->grid->tryGetValue($nextPosition);
                if ($nextValue !== '.') {
                    continue;
                }
                $vertex = $graph->getVertex($position->__toString());
                $nextVertex = $graph->getVertex($nextPosition->__toString());
                $edge = $vertex->createEdgeTo($nextVertex);
                $edge->setWeight(1);
            }
        }

        return $graph;
    }

    public function secondStar(): string
    {
        $startingPoint = $this->graph->getVertex((new Vector2DInt(0, 0))->__toString());
        $endPoint = $this->graph->getVertex((new Vector2DInt($this->size, $this->size))->__toString());
        $solver = new BreadthFirst($startingPoint);
        $numberOfCorruptedBytes = \count($this->corruptedBytes);
        $byte = null;
        $lastPath = null;
        for ($i = $this->numberOfBytes; $i < $numberOfCorruptedBytes; $i++) {
            $byte = $this->corruptedBytes[$i];

            $vertex = $this->graph->getVertex($byte->__toString());
            $vertex->destroy();

            // We redo the calculation only if the fallen byte was on the last known path
            if ($lastPath !== null && $lastPath->getVertices()->hasVertexId($byte->__toString())) {
                $lastPath = null;
            }
            try {
                if ($lastPath === null) {
                    $lastPath = $solver->getWalkTo($endPoint);
                }
            } catch (\OutOfBoundsException $e) {
                break;
            }
        }

        return $byte ? $byte->x . ',' . $byte->y : 'NOT FOUND';
    }
}
