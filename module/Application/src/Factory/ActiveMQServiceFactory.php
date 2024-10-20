<?php

// src/Factory/ActiveMQServiceFactory.php
namespace Application\Factory;

use Application\Service\ActiveMQService;
use Psr\Container\ContainerInterface;

class ActiveMQServiceFactory
{
    public function __invoke(ContainerInterface $container): ActiveMQService
    {
        $config = $container->get('config')['activemq'];
        return new ActiveMQService($config);
    }
}
