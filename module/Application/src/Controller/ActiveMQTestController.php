<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Application\Service\ActiveMQService;

class ActiveMQTestController extends AbstractActionController
{
    private $activeMQService;

    public function __construct(ActiveMQService $activeMQService)
    {
        $this->activeMQService = $activeMQService;
    }

    public function sendMessageAction()
    {
        // Example message data
        $message = [
            'type' => 'test',
            'content' => 'This is a test message for ActiveMQ',
        ];

        // Send the message to the ActiveMQ queue
        $this->activeMQService->sendMessage($message);

        return $this->getResponse()->setContent('Message sent to ActiveMQ');
    }

    public function receiveMessageAction()
    {
        // Receive a message from the queue
        $message = $this->activeMQService->receiveMessage();

        return $this->getResponse()->setContent('Received message: ' . json_encode($message));
    }
}