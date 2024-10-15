<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Application\Model\RecipientTable;

class RecipientController extends AbstractRestfulController
{
    private $recipientTable;

    public function __construct(RecipientTable $recipientTable)
    {
        $this->recipientTable = $recipientTable;
    }

    // GET /recipients - Retrieve all recipients
    public function getList()
    {
        $recipients = $this->recipientTable->fetchAll();
        return new JsonModel(['data' => $recipients]);
    }

    // GET /recipients/:id - Retrieve a recipient by ID
    public function get($id)
    {
        if (!is_numeric($id)) {
            return new JsonModel(['error' => 'Invalid ID provided']);
        }

        $recipient = $this->recipientTable->getRecipient($id);

        if (!$recipient) {
            return new JsonModel(['error' => 'Recipient not found']);
        }

        return new JsonModel(['data' => $recipient]);
    }


    // POST /recipients - Create a new recipient
    public function create($data)
    {
        // Decode the data
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonModel(['error' => 'No data provided']);
        }

        // Define required fields
        $requiredFields = ['name', 'email', 'phone_number'];

        // Check for missing fields
        foreach ($requiredFields as $field) {
            if (empty(trim($data[$field]))) {
                return new JsonModel(['error' => "Missing or empty required field: $field"]);
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonModel(['error' => 'Invalid email format']);
        }

        // Validate phone number
        $phonePattern = '/^(\+351)?[29][0-9]{8}$/'; // +351 is optional, followed by 9 digits
        if (!preg_match($phonePattern, $data['phone_number'])) {
            return new JsonModel(['error' => 'Invalid phone number format']);
        }

        // Create the recipient
        $this->recipientTable->saveRecipient($data);
        return new JsonModel(['status' => 'created', 'data' => $data]);
    }



    // PUT /recipients/:id - Update a recipient by ID
    public function update($id, $data)
    {
        if (!is_numeric($id)) {
            return new JsonModel(['error' => 'Invalid ID provided']);
        }

        // Decode the data (if you're passing JSON)
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonModel(['error' => 'No data provided for update']);
        }

        // Check if the recipient with the provided ID exists
        $existingRecord = $this->recipientTable->getRecipient($id);
        if (!$existingRecord) {
            return new JsonModel(['error' => "Recipient with ID $id not found"]);
        }

        // Define allowed fields for update
        $allowedFields = ['name', 'email', 'phone_number'];
        $updateData = [];

        // Validate provided fields and collect them if valid
        foreach ($data as $field => $value) {
            if (!in_array($field, $allowedFields)) {
                return new JsonModel(['error' => "Field $field is not allowed to be updated"]);
            }

            // Validate and sanitize name
            if ($field === 'name') {
                $trimmedName = trim($value);
                // Ensure name contains only alphabetic characters
                if (empty($trimmedName) || !preg_match('/^[a-zA-Z]+$/', $trimmedName)) {
                    return new JsonModel(['error' => 'Name must contain only letters']);
                }
            }

            // Validate email format
            if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return new JsonModel(['error' => 'Invalid email format']);
            }

            // Validate phone number 
            if ($field === 'phone_number') {
                $phonePattern = '/^(\+351)?[29][0-9]{8}$/'; // +351 is optional, followed by 9 digits
                if (!preg_match($phonePattern, $value)) {
                    return new JsonModel(['error' => 'Invalid Portuguese phone number format']);
                }
            }

            // Add valid data to the updateData array
            $updateData[$field] = $value;
        }

        // If no valid fields are provided, return an error
        if (empty($updateData)) {
            return new JsonModel(['error' => 'At least one valid field (name, email, phone_number) must be provided']);
        }

        // Perform the update
        $updateData['id'] = $id;
        $this->recipientTable->saveRecipient($updateData);
        return new JsonModel(['status' => 'updated', 'data' => $updateData]);
    }



    // DELETE /recipients/:id - Delete a recipient by ID
    public function delete($id)
    {
        if (!is_numeric($id)) {
            return new JsonModel(['error' => 'Invalid ID provided']);
        }

        // Check if the recipient with the provided ID exists
        $existingRecord = $this->recipientTable->getRecipient($id);
        if (!$existingRecord) {
            return new JsonModel(['error' => "Recipient with ID $id not found"]);
        }

        // Perform the deletion
        $this->recipientTable->deleteRecipient($id);
        return new JsonModel(['status' => 'deleted']);
    }
}
