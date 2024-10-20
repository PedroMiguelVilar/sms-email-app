<?php

// src/Command/SMSConsumerCommand.php
namespace Application\Command;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Application\Service\ActiveMQService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SMSConsumerCommand extends AbstractParamAwareCommand
{
    protected static $defaultName = 'app:sms-consumer';

    private $activeMQService;

    public function __construct(ActiveMQService $activeMQService)
    {
        parent::__construct();
        $this->activeMQService = $activeMQService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting SMS Consumer...');

        $this->activeMQService->receiveMessages(function ($message) use ($output) {
            // Simulate sending an SMS
            $recipient = $message['recipient'];
            $content = $message['content'];

            $output->writeln("Sending SMS to $recipient: $content");
            // Simulate success/failure
            sleep(1);
            $output->writeln("SMS to $recipient sent successfully.");
        });

        return 0;
    }
}
