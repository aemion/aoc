<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.solver')]
abstract class AbstractSolver
{
    protected OutputInterface $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function isFirstStarSolved(): bool
    {
        return false;
    }

    public static function getDay(): string
    {
        $fqcn = static::class;
        $splitFqcn = explode('\\', $fqcn);

        $className = array_pop($splitFqcn);

        $day = str_replace('Day', '', $className);
        if (\strlen($day) === 1) {
            $day = '0' . $day;
        }

        return $day;
    }

    abstract public function loadInput(string $path): void;

    public function preSolve(): void
    {
    }

    abstract public function firstStar(): string;

    abstract public function secondStar(): string;
}
