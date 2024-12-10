<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Direction;
use App\Y2024\Model\Grid;
use App\Y2024\Model\Day10Node;
use App\Y2024\Model\OrientedGraph;
use App\Y2024\Model\Vector2DInt;

final class Day10 extends AbstractSolver
{
    private Grid $grid;
    private OrientedGraph $graph;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $grid = [];
        while (!feof($file)) {
            $line = trim(fgets($file));

            $grid[] = str_split($line);
        }

        $this->grid = new Grid($grid);
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $this->graph = new OrientedGraph();
        foreach ($this->grid->getCells() as $position => $value) {
            $node = new Day10Node($position->__toString(), (int) $value);
            $this->graph->addNode($node);
            $this->addEdgeIfNeeded($position->addVector2D(Direction::Top->getVector2D()), $node);
            $this->addEdgeIfNeeded($position->addVector2D(Direction::Left->getVector2D()), $node);
        }
    }

    private function addEdgeIfNeeded(Vector2DInt $neighbourCell, Day10Node $node): void
    {
        $value = $node->getValue();
        if ($this->grid->isInside($neighbourCell)) {
            $neighbourCellValue = $this->grid->getValue($neighbourCell);
            if ((int) $neighbourCellValue === $value + 1) {
                $this->graph->addEdge($node, $this->graph->getNode($neighbourCell->__toString()));
            } elseif ((int) $neighbourCellValue === $value - 1) {
                $this->graph->addEdge($this->graph->getNode($neighbourCell->__toString()), $node);
            }
        }
    }

    public function firstStar(): string
    {
        $total = 0;

        /** @var Day10Node $node */
        foreach ($this->graph->getNodes() as $node) {
            $value = $node->getValue();
            if ($value !== 0) {
                continue;
            }

            $node->findNines();
            $total += $node->getScore();
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;

        /** @var Day10Node $node */
        foreach ($this->graph->getNodes() as $node) {
            $value = $node->getValue();
            if ($value !== 0) {
                continue;
            }

            $total += $node->calculateRating();
        }

        return (string) $total;
    }

}
