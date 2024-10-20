<?php

declare(strict_types=1);

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class RecipientTable
{
    private $tableGateway;
    private $name;
    private $phoneNumber;
    private $email;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    // Setters with validation logic
    public function setName(string $name): void
    {
        $trimmedName = trim($name);
        if (empty($trimmedName)) {
            throw new \InvalidArgumentException('Name is required and cannot be empty.');
        }
        if (!preg_match('/^[a-zA-Z]+(\s+[a-zA-Z]+)*$/', $trimmedName)) {
            throw new \InvalidArgumentException('Name must contain at least one letter and can only include letters and spaces.');
        }
        $this->name = $trimmedName;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $phonePattern = '/^(\+351)?[29][0-9]{8}$/';
        if (!preg_match($phonePattern, $phoneNumber)) {
            throw new \InvalidArgumentException('Invalid phone number format.');
        }
        $this->phoneNumber = $phoneNumber;
    }

    public function setEmail(string $email): void
    {
        if (empty(trim($email))) {
            throw new \InvalidArgumentException('Email is required and cannot be empty.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }
        $this->email = trim($email);
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getEmail(): string
    {
        return $this->email;
    }


    public function saveRecipient(array $data): void
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        // Validate fields
        $this->validateFields($data);

        $filteredData = [
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'phone_number' => $this->getPhoneNumber()
        ];

        // Check if the record with this ID exists
        if ($id === 0) {
            // If no ID is provided, insert a new record
            $this->tableGateway->insert($filteredData);
        } else {
            // Check if the record exists in the database
            $existingRecord = $this->getRecipient($id);
            if ($existingRecord) {
                // If the record exists, update it
                $this->tableGateway->update($filteredData, ['id' => $id]);
            } else {
                throw new \Exception("Recipient with ID $id does not exist");
            }
        }
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

    public function deleteRecipient($id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }

    private function validateFields(array $data): void
    {
        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['email'])) {
            $this->setEmail($data['email']);
        }

        if (isset($data['phone_number'])) {
            $this->setPhoneNumber($data['phone_number']);
        }
    }
}
