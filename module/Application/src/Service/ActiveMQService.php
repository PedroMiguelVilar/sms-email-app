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
            // Send the message to the queue
            $this->stomp->send($this->queueName, new TransportMessage(json_encode($message)));
        } catch (StompException $e) {
            // Handle the exception
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
}