<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

class Day5 extends AbstractSolver
{
    private array $rawOrderingRules;

    private array $pageUpdates;

    public function loadInput(string $path): void
    {
        $this->rawOrderingRules = [];
        $this->pageUpdates = [];
        $file = fopen($path, 'rb');
        while (!feof($file)) {
            $line = trim(fgets($file));
            if (str_contains($line, '|')) {
                $this->rawOrderingRules[] = array_map(static fn(string $i) => (int) $i, explode('|', $line));
            } elseif (str_contains($line, ',')) {
                $this->pageUpdates[] = array_map(static fn(string $i) => (int) $i, explode(',', $line));
            }
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function firstStar(): string
    {
        $rulesByBefore = $this->getRulesByBefore();

        $total = 0;
        foreach ($this->pageUpdates as $pageUpdate) {
            $countIt = $this->isCorrectlyOrderedPage($pageUpdate, $rulesByBefore);

            if ($countIt) {
                $total += $this->getPageUpdateValue($pageUpdate);
            }
        }

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;
        $rulesByBefore = $this->getRulesByBefore();

        $toSort = array_filter(
            $this->pageUpdates,
            fn(array $pageUpdate) => !$this->isCorrectlyOrderedPage($pageUpdate, $rulesByBefore),
        );

        foreach ($toSort as $pageUpdate) {
            usort($pageUpdate, function (int $a, int $b) use ($rulesByBefore) {
                if ($a === $b) {
                    return 0;
                }
                $pagesAllowedAfterA = $rulesByBefore[$a] ?? [];
                if (\in_array($b, $pagesAllowedAfterA, true)) {
                    return -1;
                }

                return 1;
            });

            $total += $this->getPageUpdateValue($pageUpdate);
        }

        return (string) $total;
    }

    public function getPageUpdateValue(array $pageUpdate): int
    {
        $middle = (\count($pageUpdate) - 1) / 2;

        return $pageUpdate[$middle];
    }

    public function isCorrectlyOrderedPage(array $pageUpdate, array $rulesByBefore): bool
    {
        foreach ($pageUpdate as $i => $page) {
            $pagesAfter = \array_slice($pageUpdate, $i + 1);
            $pagesAllowedAfter = $rulesByBefore[$page] ?? [];
            $invalidPages = array_diff($pagesAfter, $pagesAllowedAfter);
            if (\count($invalidPages) > 0) {
                return false;
            }
        }

        return true;
    }

    public function getRulesByBefore(): array
    {
        $rulesByBefore = [];
        foreach ($this->rawOrderingRules as $rule) {
            [$before, $after] = $rule;
            if (!isset($rulesByBefore[$before])) {
                $rulesByBefore[$before] = [];
            }
            $rulesByBefore[$before][] = $after;
        }

        return $rulesByBefore;
    }

}
