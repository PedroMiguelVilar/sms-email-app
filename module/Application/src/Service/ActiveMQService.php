<?php

// src/Service/ActiveMQService.php
namespace Application\Service;

use FuseSource\Stomp\Message;
use Stomp\Network\Connection;
use Stomp\Client;
use Stomp\SimpleStomp;
use Stomp\Exception\StompException;
use Stomp\Transport\Message as TransportMessage;

class ActiveMQService
{
    private $stomp;
    private $queueName;

    public function __construct(array $config)
    {
        try {
            // Establish the connection to the ActiveMQ broker
            $connection = new Connection($config['broker_uri']);
            $connection->connect($config['username'], $config['password']);

            $client = new Client($connection);
            $this->stomp = new SimpleStomp($client);
            $this->queueName = $config['queue_name'];
        } catch (StompException $e) {
            // Handle the exception
            echo 'Could not connect to ActiveMQ: ', $e->getMessage();
        }
    }

    public function sendMessage(array $message)
    {
        try {
            $messageBody = json_encode($message);
            $this->stomp->send($this->queueName, new TransportMessage($messageBody));
        } catch (StompException $e) {
            echo 'Error sending message: ', $e->getMessage();
        }
    }


    public function receiveMessage()
    {
        try {
            // Create a unique subscription ID
            $subscriptionId = 'subscription-' . uniqid();
            $headers = [
                'id' => $subscriptionId,
                'ack' => 'auto',
            ];

            // Subscribe to the queue using the queue name and headers
            $this->stomp->subscribe($this->queueName, $headers);

            // Read the message
            $frame = $this->stomp->read();

            // Prepare headers for unsubscribe
            $unsubscribeHeaders = ['id' => $subscriptionId];

            // Unsubscribe from the queue using the same subscription ID
            $this->stomp->unsubscribe($this->queueName, $unsubscribeHeaders);

            // Return the decoded message if available
            return $frame ? json_decode($frame->body, true) : null;
        } catch (StompException $e) {
            echo 'Error receiving message: ', $e->getMessage();
            return null;
        }
    }

    public function receiveMessages(callable $callback)
    {
        try {
            $subscriptionId = 'subscription-' . uniqid();
            $headers = [
                'id' => $subscriptionId,
                'ack' => 'client', // Explicit client acknowledgment
            ];

            // Subscribe to the queue using the subscription ID and headers
            $this->stomp->subscribe($this->queueName, $headers);
            echo "Consumer subscribed to queue: {$this->queueName}\n";

            while (true) {
                // Read the message from the queue
                $frame = $this->stomp->read();

                // If no frame is received, log and continue
                if (!$frame) {
                    echo "No frame received, sleeping...\n";
                    sleep(1);
                    continue;
                }

                $frameHeaders = $this->getFrameHeaders($frame);

                if (isset($frameHeaders['message-id'])) {
                    $message = json_decode($frame->body, true);
                    $callback($message);
                }
            }

            $this->stomp->unsubscribe($this->queueName, ['id' => $subscriptionId]);
        } catch (StompException $e) {
            echo 'Error receiving message: ', $e->getMessage();
        } catch (\Exception $e) {
            echo 'Unexpected error: ', $e->getMessage();
        }
    }




    private function getFrameHeaders(\Stomp\Transport\Frame $frame)
    {
        $reflectionClass = new \ReflectionClass($frame);
        $property = $reflectionClass->getProperty('headers');
        $property->setAccessible(true);

        return $property->getValue($frame);
    }
}
