<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Vector2DInt;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ShortestPath\BreadthFirst;

final class Day20 extends AbstractSolver
{
    private Grid $grid;
    private Vector2DInt $start;
    private Vector2DInt $end;
    private Graph $graph;

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

    public function isFirstStarSolved(): bool
    {
        return false;
    }

    public function preSolve(): void
    {
        $this->graph = $this->createGraph();
    }

    private function createGraph(): Graph
    {
        $graph = new Graph();
        foreach ($this->grid->getCells() as $position => $cell) {
            $graph->createVertex($position->__toString());
        }

        $directions = Direction::vectors();
        foreach ($this->grid->getCells() as $position => $value) {
            if ($value === '#') {
                continue;
            }
            $vertex = $graph->createVertex($position->__toString(), true);
            $vertex->setAttribute('position', $position);
            foreach ($directions as $direction) {
                $nextPosition = $position->addVector2D($direction);

                if ($this->grid->getValue($nextPosition) === '#') {
                    continue;
                }

                $nextVertex = $graph->createVertex($nextPosition->__toString(), true);
                $edge = $vertex->createEdgeTo($nextVertex);
                $edge->setWeight(1);
            }
        }

        return $graph;
    }

    public function firstStar(): string
    {
        $startVertex = $this->graph->getVertex($this->start->__toString());
        $endVertex = $this->graph->getVertex($this->end->__toString());
        $solver = new BreadthFirst($startVertex);
        $path = $solver->getWalkTo($endVertex);

        $pathWithIndex = [];
        foreach ($path->getVertices() as $i => $vertex) {
            $pathWithIndex[$vertex->getId()] = ['position' => $vertex->getAttribute('position'), 'index' => $i];
        }
        $directions = Direction::vectors();

        $result = [];
        foreach ($pathWithIndex as $positionAndIndex) {
            /** @var Vector2DInt $position */
            $position = $positionAndIndex['position'];
            foreach ($directions as $direction) {
                $potentialWallPosition = $position->addVector2D($direction);
                if ($this->grid->tryGetValue($potentialWallPosition) !== '#') {
                    continue;
                }

                $nextPosition = $potentialWallPosition->addVector2D($direction);
                if (!isset($pathWithIndex[$nextPosition->__toString()])) {
                    continue;
                }

                $currentIndex = $positionAndIndex['index'];
                $nextIndex = $pathWithIndex[$nextPosition->__toString()]['index'];
                if ($nextIndex > $currentIndex + 2) {
                    $diff = $nextIndex - ($currentIndex + 2);
                    if (!isset($result[$diff])) {
                        $result[$diff] = 0;
                    }

                    $result[$diff]++;
                }
            }
        }

        $total = 0;
        foreach ($result as $gainedTime => $number) {
            if ($gainedTime >= 100) {
                $total += $number;
            }
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;

        return (string) $total;
    }
}
