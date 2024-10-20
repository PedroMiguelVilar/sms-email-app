<?php

// src/Controller/SMSProducerController.php
namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Application\Service\ActiveMQService;
use Laminas\View\Model\JsonModel;

class SMSProducerController extends AbstractActionController
{
    private $activeMQService;

    public function __construct(ActiveMQService $activeMQService)
    {
        $this->activeMQService = $activeMQService;
    }

    public function sendBulkSmsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = json_decode($request->getContent(), true);
            $recipients = $data['recipients'];
            $messageContent = $data['message'];

            foreach ($recipients as $recipient) {
                $message = [
                    'recipient' => $recipient,
                    'content' => $messageContent,
                ];
                $this->activeMQService->sendMessage($message);
            }

            return new JsonModel(['status' => 'Messages queued for processing']);
        }

        return new JsonModel(['error' => 'Invalid request'], 400);
    }
}
