<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand('app:solve')]
final class SolverCommand extends Command
{
    public function __construct(
        #[TaggedIterator('app.solver')] private readonly iterable $solvers,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'day',
            InputArgument::OPTIONAL,
            'Day to solve [Default to current day]',
            (new \DateTimeImmutable())->format('d')
        );
        $this->addOption('test', 't', InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $test = $input->getOption('test');
        $solver = $this->getSolver($input, $output);

        if (null === $solver) {
            $output->writeln('<error>No solver found</error>');

            return Command::FAILURE;
        }

        if ($test) {
            $this->test($solver, $output);

            return Command::SUCCESS;
        }

        $solver->loadInput($this->projectDir . '/inputs/' . $solver::getDay() . '.txt');
        $solver->preSolve();
        $output->writeln(\sprintf('First star result: %s', $solver->firstStar()));
        if ($solver->isFirstStarSolved()) {
            $output->writeln(\sprintf('Second star result: %s', $solver->secondStar()));
        }

        return Command::SUCCESS;
    }

    private function getSolver(InputInterface $input, OutputInterface $output): ?AbstractSolver
    {
        $day = $input->getArgument('day');
        if (null === $day) {
            $day = (new \DateTimeImmutable())->format('d');
        }

        if (!is_numeric($day)) {
            return null;
        }

        $day = (int) $day;
        if ($day < 1 || $day > 25) {
            return null;
        }

        $class = \sprintf('App\Y2024\Day%s', $day);
        foreach ($this->solvers as $solver) {
            if (\get_class($solver) === $class) {
                $solver->setOutput($output);

                return $solver;
            }
        }

        return null;
    }

    private function test(AbstractSolver $solver, OutputInterface $output): void
    {
        $results = fopen($this->projectDir . '/test_inputs/' . $solver::getDay() . '_results.txt', 'rb');
        $expected = trim(fgets($results));
        $solver->loadInput($this->projectDir . '/test_inputs/' . $solver::getDay() . '.txt');
        $solver->preSolve();
        $result = $solver->firstStar();
        if ($result === $expected) {
            $output->writeln('Test first star OK!');
        } else {
            $output->writeln(\sprintf('Test first star KO! Expected %s, Got %s', $expected, $result));
        }

        if (!$solver->isFirstStarSolved()) {
            return;
        }

        $expected = trim(fgets($results));
        $result = $solver->secondStar();
        if ($result === $expected) {
            $output->writeln('Test second star OK!');
        } else {
            $output->writeln(\sprintf('Test second star KO! Expected %s, Got %s', $expected, $result));
        }
    }

}
