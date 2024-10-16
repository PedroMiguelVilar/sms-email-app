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
        // Get the raw POST content and decode the JSON payload
        $requestContent = $this->getRequest()->getContent();
        $data = json_decode($requestContent, true); // Decode the JSON payload

        // Extract numbers from the decoded JSON data
        $numbers = $data['numbers'] ?? []; // Fallback to an empty array if 'numbers' is not present

        // Check if we have numbers to process
        if (empty($numbers) || !is_array($numbers)) {
            return new JsonModel([
                'status'  => 'error',
                'message' => 'No numbers provided or invalid format.',
            ]);
        }

        $success = true;
        $failedNumbers = [];

        // Loop through each number and run an async task
        foreach ($numbers as $number) {
            $processStarted = $this->runAsyncTask($number);

            // Collect the number if the task failed
            if (!$processStarted) {
                $failedNumbers[] = $number;
                $success = false;
            }
        }

        // Return a JSON response based on whether all tasks started successfully
        if ($success) {
            return new JsonModel([
                'status'  => 'success',
                'message' => 'Tasks are processing in the background for all numbers.',
            ]);
        } else {
            return new JsonModel([
                'status'  => 'partial_success',
                'message' => 'Some tasks failed to start.',
                'failed_numbers' => $failedNumbers,
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
