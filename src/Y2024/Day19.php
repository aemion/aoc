<?php

declare(strict_types=1);

namespace App\Y2024;

use App\AbstractSolver;

final class Day19 extends AbstractSolver
{
    private array $rawPatterns;
    private array $patterns;

    private array $designs;

    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');

        $line = trim(fgets($file));
        $this->rawPatterns = explode(', ', $line);
        fgets($file);
        $this->designs = [];
        while (!feof($file)) {
            $this->designs[] = trim(fgets($file));
        }
    }

    public function isFirstStarSolved(): bool
    {
        return true;
    }

    public function preSolve(): void
    {
        $this->patterns = [];

        foreach ($this->rawPatterns as $pattern) {
            $current = &$this->patterns;
            $patternLength = \strlen($pattern);
            foreach (str_split($pattern) as $i => $character) {
                $isEnd = $i === $patternLength - 1;
                if (!isset($current[$character])) {
                    $current[$character] = [
                        'character' => $character,
                        'isEnd'     => $isEnd,
                        'id'        => $character . $i,
                        'children'  => [],
                    ];
                }
                $current[$character]['isEnd'] = $isEnd || $current[$character]['isEnd'];
                $current = &$current[$character]['children'];
            }
        }
    }

    public function firstStar(): string
    {
        $total = 0;

        foreach ($this->designs as $design) {
            if ($this->isValidDesign(str_split($design), $this->patterns)) {
                $total++;
            }
        }

        return (string) $total;
    }

    private function isValidDesign(array $design, array $patterns): bool
    {
        return $this->memoizeCount($design, $patterns) > 0;
    }

    private function countValidDesigns(array $design, array $patterns, string $id = 'root'): int
    {
        $character = array_shift($design);
        if (!isset($patterns[$character])) {
            return 0;
        }

        if (empty($design)) {
            return $patterns[$character]['isEnd'] ? 1 : 0;
        }

        $count = 0;
        if (!empty($patterns[$character]['children'])) {
            $count += $this->memoizeCount(
                $design,
                $patterns[$character]['children'],
                $id . '>' . $patterns[$character]['id']
            );
        }

        if ($patterns[$character]['isEnd']) {
            $count += $this->memoizeCount($design, $this->patterns);
        }

        return $count;
    }

    private array $cache = [];

    private function memoizeCount(array $design, array $patterns, string $id = 'root'): int
    {
        $string = implode('', $design);
        $key = $string . '||' . $id;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $this->cache[$key] = $this->countValidDesigns($design, $patterns, $id);

        return $this->cache[$key];
    }

    public function secondStar(): string
    {
        $total = 0;

        foreach ($this->designs as $i => $design) {
            $count = $this->memoizeCount(str_split($design), $this->patterns);
            $total += $count;
        }

        return (string) $total;
    }
}
