<?php

declare(strict_types=1);

namespace Application\Factory;

use Application\Controller\RecipientController;
use Application\Model\RecipientTable;
use Psr\Container\ContainerInterface;

class RecipientControllerFactory
{
    public function __invoke(ContainerInterface $container): RecipientController
    {
        // Retrieve the RecipientTable from the service container
        $recipientTable = $container->get(RecipientTable::class);

        // Return an instance of RecipientController with the RecipientTable injected
        return new RecipientController($recipientTable);
    }
}
