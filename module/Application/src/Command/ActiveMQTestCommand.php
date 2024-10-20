<?php

// src/Command/ActiveMQTestCommand.php
namespace Application\Command;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Application\Service\ActiveMQService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActiveMQTestCommand extends AbstractParamAwareCommand
{
    protected static $defaultName = 'app:test-activemq';

    private $activeMQService;

    public function __construct(ActiveMQService $activeMQService)
    {
        parent::__construct();
        $this->activeMQService = $activeMQService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = [
            'type' => 'test',
            'content' => 'Command test message for ActiveMQ',
        ];

        $this->activeMQService->sendMessage($message);
        $output->writeln('Message sent to ActiveMQ from command.');

        return 0;
    }
}
