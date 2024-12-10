<?php

declare(strict_types=1);

namespace App\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @method string getCommandDescription()
 */
class MakeSolver extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:solver';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->setHelp('Creates a solver and empty inputs/test_input files');
        $command->addArgument(
            'day',
            InputArgument::OPTIONAL,
            'Day to create [Default to current day]',
            (new \DateTimeImmutable())->format('d')
        );
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $day = $this->getDay($input);
        if (!$day) {
            $io->error('Invalid day supplied');

            return;
        }

        $variables = ['day' => $day, 'year' => 2024];
        $className = \sprintf('App\Y%s\Day%s', $variables['year'], $variables['day']);
        $generator->generateClass(
            $className,
            $generator->getRootDirectory() . '/src/Maker/DayX.tpl.php',
            $variables
        );
        $generator->writeChanges();

        $dayFileName = $className::getDay();
        $paths = [
            '/inputs/' . $dayFileName . '.txt',
            '/test_inputs/' . $dayFileName . '.txt',
            '/test_inputs/' . $dayFileName . '_results.txt',
        ];

        foreach ($paths as $path) {
            $generator->dumpFile($generator->getRootDirectory() . $path, '');
        }
        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    private function getDay(InputInterface $input): ?int
    {
        $day = $input->getArgument('day');
        if (!is_numeric($day)) {
            return null;
        }

        $day = (int) $day;
        if ($day < 1 || $day > 25) {
            return null;
        }

        return $day;
    }
}
