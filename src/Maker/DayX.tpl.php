<?= "<?php\n" ?>

declare(strict_types=1);

namespace App\Y<?= $year ?>;

use App\AbstractSolver;

final class Day<?= $day ?> extends AbstractSolver
{
    public function loadInput(string $path): void
    {
        $file = fopen($path, 'rb');
        while (!feof($file)) {
            $line = trim(fgets($file));
        }
    }

    public function isFirstStarSolved(): bool
    {
        return false;
    }

    public function firstStar(): string
    {
        $total = 0;

        return (string) $total;
    }

    public function secondStar(): string
    {
        $total = 0;

        return (string) $total;
    }
}
