<?php

declare(strict_types=1);

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class RecipientTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        // Get the result set from the database
        $resultSet = $this->tableGateway->select();

        // Convert the result set to an array
        return $resultSet->toArray();
    }


    public function getRecipient($id)
    {
        return $this->tableGateway->select(['id' => $id])->current();
    }

    public function saveRecipient($data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        // Check if the record with this ID exists
        if ($id === 0) {
            // If no ID is provided, insert a new record
            $this->tableGateway->insert($data);
        } else {
            // Check if the record exists in the database
            $existingRecord = $this->getRecipient($id);
            if ($existingRecord) {
                // If the record exists, update it
                $this->tableGateway->update($data, ['id' => $id]);
            } else {
                // If the record does not exist, handle accordingly (optional)
                throw new \Exception("Recipient with ID $id does not exist");
            }
        }
    }


    public function deleteRecipient($id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }
}
