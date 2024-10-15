<?php

declare(strict_types=1);

namespace Application\Factory;

use Application\Model\RecipientTable;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RecipientTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        // Get the DB Adapter from the service manager
        $dbAdapter = $container->get(Adapter::class);

        // Correct the table name to 'recipients'
        $tableGateway = new TableGateway('recipients', $dbAdapter);

        // Return the RecipientTable with the injected TableGateway
        return new RecipientTable($tableGateway);
    }
}
