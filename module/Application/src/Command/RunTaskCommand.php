<?php

declare(strict_types=1);

namespace Application\Command;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunTaskCommand extends AbstractParamAwareCommand
{
    protected static $defaultName = 'run-task';

    protected function configure(): void
    {
        $this
            ->setName('run-task')
            ->setDescription('Processes a task asynchronously.')
            ->addOption(
                'number',
                null,
                InputOption::VALUE_REQUIRED, // Ensure the number option requires a value
                'The number to process'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $number = $input->getOption('number');

        // Log the start of the task
        $this->logToFile("Starting task for number: {$number}");

        // Pretend its doing something
        sleep(10);

        $this->logToFile("Completed task for number: {$number}");

        $output->writeln("Task completed for number: {$number}");

        return 0;
    }

    private function logToFile(string $message): void
    {
        // Format the log message with timestamp
        $formattedMessage = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;

        // Write to log file
        file_put_contents(__DIR__ . '/../../../../data/logs/task.log', $formattedMessage, FILE_APPEND);
    }
}
