<?php

namespace OHM\tickets\hubspot;

/**
 * This creates and finds HubSpot contacts.
 *
 * These contacts are intended to be used for association with newly
 * created tickets by OHM\tickets\HubSpotTicketService.
 */
class HubSpotContactService
{
    // We need this so we can create URLs for v4 API requests.
    private string $hubspotApiDomain; // Example: "api.hubapi.com"
    private bool $debugEnabled;

    private HubSpotApiClient $hubSpotApiClient;

    function __construct(HubSpotApiClient $hubSpotApiClient = null)
    {
        $this->hubSpotApiClient = $hubSpotApiClient ?? new HubSpotApiClient();

        $this->hubspotApiDomain = $this->hubSpotApiClient->getHubspotApiDomain();
        $this->debugEnabled = $GLOBALS['CFG']['GEN']['HUBSPOT_API_DEBUG'];
    }

    /**
     * Find a contact ID by email address.
     *
     * @param string $emailAddress
     * @return string|null The contact's ID. Null if not found.
     * @throws HubSpotException Thrown on unexpected HubSpot API responses.
     */
    public function findIdByEmail(string $emailAddress): ?string
    {
        if ($this->debugEnabled) {
            error_log('Looking up HubSpot contact ID for email: ' . $emailAddress);
        }

        /*
         * Prepare the request.
         */

        $apiUrl = sprintf('https://%s/crm/v3/objects/contacts/batch/read',
            $this->hubspotApiDomain);

        $requestBody = [
            "properties" => ["email"], // The properties we want to get back.
            "idProperty" => "email", // The property we are searching by.
            "inputs" => [
                ["id" => $emailAddress]
            ]
        ];

        /*
         * Send the request.
         */

        $hubspotResponse = $this->hubSpotApiClient->sendRequest(
            'POST', $apiUrl, $requestBody, '');
        $rawApiResponse = $hubspotResponse['rawApiResponse'];
        $httpStatus = $hubspotResponse['httpStatus'];

        /*
         * Handle multi-status (HTTP status 207) responses.
         *
         * So far, this response has been seen for valid searches
         * with no contacts found.
         *
         * HubSpot documentation is incomplete here so leaving this
         * open to possibly contain contact ID matches.
         */

        if (207 == $httpStatus) {
            $contactId = $this->handleSearchMultiStatusResponse(
                $emailAddress, $hubspotResponse);
            return $contactId;
        }

        /*
         * Anything other than a 200 response at this point is an
         * unexpected HubSpot API response.
         */

        if (200 != $httpStatus) {
            $errorMessage = sprintf(
                'Error while attempting to find HubSpot contact by'
                . ' email "%s". HubSpot API status: %s, response: %s',
                $emailAddress, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log($errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * If we get this far, we should have a contact ID.
         */

        $hasResults = array_key_exists('results', $rawApiResponse)
            && !empty($rawApiResponse['results']);
        if (!$hasResults) {
            $errorMessage = sprintf('HubSpot returned a 200 status but'
                . ' did not return any results when searching for a contact by'
                . ' email "%s". HubSpot API status: %s, response: %s',
                $emailAddress, $httpStatus, $rawApiResponse['rawApiResponse']);
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * Extract the contact ID from the response.
         */

        $results = $rawApiResponse['results'];
        $contactId = $results[0]['id'];

        if ($this->debugEnabled) {
            error_log(sprintf('Found contact ID "%s" for email "%s".', $contactId, $emailAddress));
        }

        return $contactId;
    }

    /**
     * Create a HubSpot contact and associate it with a ticket.
     *
     * @param string $emailAddress The contact's email address.
     * @param string $firstName The contact's first name.
     * @param string $lastName The contact's last name.
     * @param string $ticketId The contact's associated ticket ID.
     * @param string $lifecycleStage The HubSpot lifecycle stage.
     * @return int The contact's ID.
     * @throws HubSpotException Thrown on unexpected HubSpot API responses.
     */
    public function createAndAssociateWithTicket(
        string $emailAddress,
        string $firstName,
        string $lastName,
        string $ticketId,
        string $lifecycleStage = 'other'
    ): int
    {
        if ($this->debugEnabled) {
            $message = sprintf('Creating HubSpot contact for email "%s" and'
                . ' associating with ticket ID "%s".', $emailAddress, $ticketId);
            error_log($message);
        }

        /*
         * Prepare the request.
         */

        $apiUrl = sprintf('https://%s/crm/v3/objects/contacts',
            $this->hubspotApiDomain);

        $requestBody = [
            "properties" => [
                'email' => $emailAddress,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'lifecyclestage' => $lifecycleStage,
            ],
            "associations" => [
                [
                    'to' => [
                        'id' => $ticketId,
                    ],
                    'types' => [
                        [
                            'associationCategory' => 'HUBSPOT_DEFINED',
                            'associationTypeId' => 15,
                        ]
                    ]
                ],
            ],
        ];

        /*
         * Send the request.
         */

        $hubspotResponse = $this->hubSpotApiClient->sendRequest(
            'POST', $apiUrl, $requestBody, '');
        $rawApiResponse = $hubspotResponse['rawApiResponse'];
        $httpStatus = $hubspotResponse['httpStatus'];

        /*
         * If the contact already exists, then associate it with the ticket
         * and return the existing contact ID.
         */

        if (409 == $httpStatus) {
            $contactId = $this->getContactIdFromConflictResponse(
                $emailAddress, $hubspotResponse);
            // Associate the existing contact with the ticket.
            $this->associateWithTicket($contactId, $ticketId);
            return $contactId;
        }

        /*
         * Anything other than a 201 response at this point is an
         * unexpected HubSpot API response.
         */

        if (201 != $httpStatus) {
            $errorMessage = sprintf(
                'Error while attempting to create a HubSpot contact'
                . ' for email "%s", user "%s". HubSpot API status: %s, response: %s',
                $emailAddress,
                $firstName . ' ' . $lastName,
                $httpStatus,
                print_r($rawApiResponse, true)
            );
            error_log($errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * Extract the contact ID from the response.
         */

        $contactId = $rawApiResponse['id'];
        return $contactId;
    }

    /**
     * Associate an existing contact with a ticket.
     *
     * @param string $contactId The contact's ID.
     * @param string $ticketId The ticket ID.
     * @return bool True if association was successful.
     * @throws HubSpotException Thrown on unexpected HubSpot API responses.
     */
    public function associateWithTicket(string $contactId, string $ticketId): bool
    {
        if ($this->debugEnabled) {
            $message = sprintf('Associating existing HubSpot contact'
                . ' ID "%s" with ticket ID "%s".', $contactId, $ticketId);
            error_log($message);
        }

        // There is no request body. All data is in the URL.
        $apiUrl = sprintf(
            'https://%s/crm/v4/objects/ticket/%s/associations/default/contact/%s',
            $this->hubspotApiDomain, $ticketId, $contactId
        );

        // Send the request.
        $hubspotResponse = $this->hubSpotApiClient->sendRequest(
            'PUT', $apiUrl, [], '');
        $rawApiResponse = $hubspotResponse['rawApiResponse'];
        $httpStatus = $hubspotResponse['httpStatus'];

        /*
         * We should only see 200 status responses.
         */

        if (200 != $httpStatus) {
            $errorMessage = sprintf(
                'HubSpot returned an expected HTTP status while'
                . ' attempting to associate contact ID "%s" with ticket ID'
                . ' "%s". HubSpot API status: %s, response: %s',
                $contactId, $ticketId, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
        * We should always get a status of "COMPLETE".
        */

        $isComplete = array_key_exists('status', $rawApiResponse)
            && 'COMPLETE' == $rawApiResponse['status'];

        if (!$isComplete) {
            $errorMessage = sprintf(
                'Unexpected response ("status" != "COMPLETE") returned'
                . ' while attempting to associate contact ID "%s" with ticket'
                . ' ID "%s". HubSpot API status: %s, response: %s',
                $contactId, $ticketId, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * The "results" array is where we can confirm our association
         * request was successful.
         */

        $hasResults = array_key_exists('results', $rawApiResponse)
            && is_array($rawApiResponse['results']);

        if (!$hasResults) {
            $errorMessage = sprintf(
                'HubSpot did not return "results" when attempting'
                . ' to associate contact ID "%s" with ticket ID "%s".'
                . ' HubSpot API status: %s, response: %s',
                $contactId, $ticketId, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * Ensure two associations were made:
         * - Contact to ticket
         * - Ticket to contact
         */

        $totalResults = count($rawApiResponse['results']);
        if (2 > $totalResults) {
            $errorMessage = sprintf(
                'HubSpot did not return two association results while'
                . ' attempting to associate contact ID "%s" with ticket ID "%s".'
                . ' HubSpot API status: %s, response: %s',
                $contactId, $ticketId, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        return true;
    }

    /**
     * Handle a multi-status response (HTTP status 207) from a request
     * to search for a HubSpot contact ID.
     *
     * This currently means HubSpot did not find the contact we were
     * looking for.
     *
     * HubSpot's response of this type for search results is not very
     * well documented (or not easily found), so there is some
     * defensive error handling in this method with a return signature
     * for future contact ID parsing.
     *
     * @param string $emailAddress The email address used for the search.
     * @param array $hubspotResponse The response array from HubSpotApiClient.
     *                               Example:
     *                                  [
     *                                      'httpStatus' => 207,
     *                                      'rawApiResponse' => [...]
     *                                  ]
     * @return ?string A contact ID if found. Null if no contact ID found.
     * @throws HubSpotException Thrown on unexpected HubSpot API responses.
     */
    private function handleSearchMultiStatusResponse(
        string $emailAddress,
        array  $hubspotResponse
    ): ?string
    {
        $rawApiResponse = $hubspotResponse['rawApiResponse'];
        $httpStatus = $hubspotResponse['httpStatus'];

        /*
         * We should always get a status of "COMPLETE".
         */

        $isComplete = array_key_exists('status', $rawApiResponse)
            && 'COMPLETE' == $rawApiResponse['status'];

        if (!$isComplete) {
            $errorMessage = sprintf(
                'Unexpected multi-status response ("status" != "COMPLETE")'
                . ' returned while attempting to find HubSpot contact by email'
                . ' "%s". HubSpot API status: %s, response: %s',
                $emailAddress, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * The "errors" array is where we can confirm our search was
         * successful but no matching contact was found.
         */

        $hasErrors = array_key_exists('errors', $rawApiResponse)
            && 0 < count($rawApiResponse['errors']);

        if (!$hasErrors) {
            $errorMessage = sprintf(
                'Unexpected multi-status response (missing "errors"'
                . ' array) returned while attempting to find HubSpot contact'
                . ' by email "%s". HubSpot API status: %s, response: %s',
                $emailAddress, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * If the first error is not "OBJECT_NOT_FOUND", then we've
         * encountered an unexpected HubSpot API response.
         */

        $firstError = $rawApiResponse['errors'][0];
        $hasValidCategory = array_key_exists('category', $firstError)
            && 'OBJECT_NOT_FOUND' == $firstError['category'];

        if (!$hasValidCategory) {
            $errorMessage = sprintf(
                'Unexpected or missing error category returned while'
                . ' attempting to find HubSpot contact by email "%s". HubSpot API'
                . ' status: %s, response: %s',
                $emailAddress, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        /*
         * If we get this far, then we made a valid search request,
         * received a valid response, and a contact was not found.
         */

        if ($this->debugEnabled) {
            error_log('HubSpot contact not found for email: ' . $emailAddress);
        }
        return null;
    }

    /**
     * Get an existing contact ID from a HubSpot 409 (conflict)
     * response after attempting to create a duplicate contact.
     *
     * This method includes error logging and throwing exceptions
     * if a contact ID cannot be found.
     *
     * @param string $emailAddress The email address for the contact.
     * @param array $hubspotResponse The entire array returned from
     *                               HubSpotApiClient. Example:
     *                                  [
     *                                      'httpStatus' => 409,
     *                                      'rawApiResponse' => [...]
     *                                  ]
     * @return string The existing contact's ID.
     * @throws HubSpotException Thrown on unexpected parsing errors.
     */
    private function getContactIdFromConflictResponse(
        string $emailAddress,
        array  $hubspotResponse
    ): string
    {
        $httpStatus = $hubspotResponse['httpStatus'];
        $rawApiResponse = $hubspotResponse['rawApiResponse'];

        if ($this->debugEnabled) {
            $message = sprintf('HubSpot API returned status 409'
                . ' (conflict) while attempting to create a new contact.'
                . ' HubSpot API status: %s, response: %s',
                $httpStatus,
                print_r($rawApiResponse, true)
            );
            error_log($message);
        }

        /*
         * Get the existing contact ID from HubSpot's error message.
         */

        $contactId = $this->parseContactIdFromConflictResponse($rawApiResponse);
        if (empty($contactId)) {
            $errorMessage = sprintf(
                'HubSpot did not provide a contact ID for the existing'
                . ' contact with email address "%s". HubSpot API status: %s, response: %s',
                $emailAddress, $httpStatus, print_r($rawApiResponse, true)
            );
            error_log('Error: ' . $errorMessage);
            throw new HubSpotException($errorMessage);
        }

        if ($this->debugEnabled) {
            error_log('Using HubSpot contact ID: ' . $contactId);
        }

        return $contactId;
    }

    /**
     * Get an existing contact's ID from HubSpot's 409 (conflict) HTTP
     * status response.
     *
     * HubSpot's error message in the response is generated when
     * attempting to create a duplicate contact and should look like
     * this:
     *
     *     {
     *         "status": "error",
     *         "message": "Contact already exists. Existing ID: 15875405214",
     *         "correlationId": "5eb98bca-fe69-45ba-bc76-6a3bd3cc3968",
     *         "category": "CONFLICT"
     *     }
     *
     * @param array $rawApiResponse The entire HubSpot API response as an
     *                              associative array.
     * @return string|null The existing contact ID. Null if not found.
     */
    private function parseContactIdFromConflictResponse(array $rawApiResponse): ?string
    {
        if (
            !array_key_exists('message', $rawApiResponse)
            || empty($rawApiResponse['message'])
        ) {
            return null;
        }

        $hubspotMessage = $rawApiResponse['message'];

        // Get the existing contact ID from HubSpot's error message.
        $matches = [];
        preg_match('/Existing ID: (\d+)/i', $hubspotMessage, $matches);
        if (2 > count($matches)) {
            return null;
        }

        $contactId = $matches[1];
        return $contactId;
    }
}