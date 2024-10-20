<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Application\Model\RecipientTable;
use Laminas\Http\Response;

class RecipientController extends AbstractRestfulController
{
    private $recipientTable;

    public function __construct(RecipientTable $recipientTable)
    {
        $this->recipientTable = $recipientTable;
    }

    // GET /recipients - Retrieve all recipients
    public function getList(): Response
    {
        $recipients = $this->recipientTable->fetchAll();
        return $this->createResponse(['data' => $recipients]);
    }

    // GET /recipients/:id - Retrieve a recipient by ID
    public function get($id): Response
    {
        if (!is_numeric($id)) {
            return $this->createResponse(['error' => 'Invalid ID provided'], 500);
        }

        $recipient = $this->recipientTable->getRecipient($id);

        if (!$recipient) {
            return $this->createResponse(['error' => 'Recipient not found'], 404);
        }

        return $this->createResponse(['data' => $recipient]);
    }


    // POST /recipients - Create a new recipient
    public function create($data): Response
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return $this->createResponse(['error' => 'No data provided [name, phone_number]'], 500);
        }

        try {
            // Save the validated recipient
            $this->recipientTable->saveRecipient($data);

            return $this->createResponse(['status' => 'created', 'data' => $data], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->createResponse(['error' => $e->getMessage()], 500);
        }
    }




    // PUT /recipients/:id - Update a recipient by ID
    public function update($id, $data): Response
    {
        if (!is_numeric($id)) {
            return $this->createResponse(['error' => 'Invalid ID provided'], 500);
        }

        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return $this->createResponse(['error' => 'No data provided for update'], 500);
        }

        $existingRecord = $this->recipientTable->getRecipient($id);
        if (!$existingRecord) {
            return $this->createResponse(['error' => "Recipient with ID $id not found"], 404);
        }

        try {
            // Add the ID to the data array
            $data['id'] = $id;

            // Let the model handle validation and saving
            $this->recipientTable->saveRecipient($data);

            return $this->createResponse(['status' => 'updated', 'data' => $data], 200);
        } catch (\InvalidArgumentException $e) {
            return $this->createResponse(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return $this->createResponse(['error' => $e->getMessage()], 500);
        }
    }




    // DELETE /recipients/:id - Delete a recipient by ID
    public function delete($id): Response
    {
        if (!is_numeric($id)) {
            return $this->createResponse(['error' => 'Invalid ID provided'], 500);
        }

        $existingRecord = $this->recipientTable->getRecipient($id);
        if (!$existingRecord) {
            return $this->createResponse(['error' => "Recipient with ID $id not found"], 404);
        }

        $this->recipientTable->deleteRecipient($id);
        return $this->createResponse(['status' => "User $id deleted"], 200);
    }


    //Create response format
    private function createResponse($data, int $statusCode = 200): Response
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setContent(json_encode($data));
        return $response;
    }
}
