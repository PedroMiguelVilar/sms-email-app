<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Symfony\Component\Process\Process;

class SMSController extends AbstractActionController
{
    public function asyncProcessAction()
    {
        // Get the number from the POST request
        $number = $this->params()->fromPost('number', 0); // Accept number from POST

        // Run the background process and capture the success state
        $processStarted = $this->runAsyncTask($number);

        // Return a JSON response based on whether the process started successfully
        if ($processStarted) {
            return new JsonModel([
                'status'  => 'success',
                'message' => 'Task is processing in the background.',
            ]);
        } else {
            return new JsonModel([
                'status'  => 'error',
                'message' => 'Failed to start the background task.',
            ]);
        }
    }

    private function runAsyncTask($number): bool
    {
        try {
            // Log the start of the process
            $this->logToFile("Starting async task with number: {$number}");

            // Check if the environment is Windows or Unix
            if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                // For Windows
                $command = "start /B php vendor/bin/laminas run-task --number={$number}";
                pclose(popen($command, "r"));
            } else {
                // For Unix-like systems
                $command = "nohup php vendor/bin/laminas run-task --number={$number} > /dev/null 2>&1 &";
                exec($command);
            }

            // Log success
            $this->logToFile("Async task for number {$number} started successfully in the background.");
            return true;
        } catch (\Exception $e) {
            // Log the exception
            $this->logToFile("Exception while starting async task for number {$number}: " . $e->getMessage());
            return false;
        }
    }




    private function logToFile(string $message): void
    {
        // Format the log message with timestamp
        $formattedMessage = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;

        // Write to log file
        file_put_contents('data/logs/sms.log', $formattedMessage, FILE_APPEND);
    }
}
