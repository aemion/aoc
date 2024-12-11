<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;
use App\Y2024\Model\Day11Edge;
use App\Y2024\Model\Day11Node;
use App\Y2024\Model\OrientedGraph;

final class Day11 extends AbstractSolver
{
    private array $stones = [];

    private OrientedGraph $cachedStones;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $line = trim(fgets($file));
        $this->stones = array_map(static fn(string $a) => (int) $a, explode(' ', $line));
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $stones = $this->stones;
        for ($i = 0; $i < 25; $i++) {
            $nextStones = [];
            foreach ($stones as $stone) {
                array_push($nextStones, ...$this->getNextStones($stone));
            }
            $stones = $nextStones;
        }

        return (string) \count($stones);
    }

    public function secondStar(): string
    {
        $total = 0;
        $this->cachedStones = new OrientedGraph();

        $stones = [];
        foreach ($this->stones as $stone) {
            if (!isset($stones[$stone])) {
                $stones[$stone] = 0;
            }
            $stones[$stone]++;
        }

        for ($i = 0; $i < 75; $i++) {
            $nextStones = [];
            foreach ($stones as $stone => $count) {
                $stoneNode = $this->cachedStones->getNode($stone);
                if ($stoneNode === null) {
                    $stoneNode = $this->prepareNextStones($stone);
                }

                $edges = $stoneNode->getEdges();
                /** @var Day11Edge $edge */
                foreach ($edges as $edge) {
                    if (!isset($nextStones[$edge->getNodeTo()->getId()])) {
                        $nextStones[$edge->getNodeTo()->getId()] = 0;
                    }
                    $nextStones[$edge->getNodeTo()->getId()] += $count * $edge->getWeight();
                }
            }
            $stones = $nextStones;
        }

        foreach ($stones as $count) {
            $total += $count;
        }

        return (string) $total;
    }

    private function prepareNextStones(int $stone): Day11Node
    {
        $node = $this->cachedStones->getNode($stone);
        if ($node === null) {
            $node = new Day11Node($stone);
            $this->cachedStones->addNode($node);
            $nextStones = $this->getNextStones($stone);

            foreach ($nextStones as $nextStone) {
                $nextNode = $this->prepareNextStones($nextStone);
                $this->cachedStones->addEdge($node, $nextNode);
            }
        }

        return $node;
    }

    private function getNextStones(int $stone): array
    {
        if ($stone === 0) {
            return [1];
        }

        $stoneString = (string) $stone;
        $stoneLength = \strlen($stoneString);
        if ($stoneLength % 2 === 0) {
            return array_map(static fn(string $a) => (int) $a, str_split($stoneString, $stoneLength / 2));
        }

        return [$stone * 2024];
    }
}
