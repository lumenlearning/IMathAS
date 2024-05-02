<?php

namespace OHM\tickets\hubspot;

use OHM\tickets\CreateTicketResult;
use OHM\tickets\NewTicketDto;
use OHM\tickets\SupportTicketService;
use Ramsey\Uuid\Uuid;

/**
 * This creates HubSpot support tickets.
 *
 * It currently does NOT associate tickets with contacts or companies.
 */
class HubSpotTicketService implements SupportTicketService
{
    // We need this so we can create URLs for v3 API requests.
    // (v4 for tickets is not currently documented)
    private string $hubspotApiDomain; // Example: "api.hubapi.com"
    private string $hubspotPipelineStageId;

    private HubSpotApiClient $hubSpotApiClient;
    private HubSpotContactService $hubSpotContactService;

    function __construct(
        HubSpotApiClient      $hubSpotApiClient = null,
        HubSpotContactService $hubSpotContactService = null
    )
    {
        $this->hubSpotApiClient = $hubSpotApiClient ?? new HubSpotApiClient();
        $this->hubSpotContactService = $hubSpotContactService ?? new HubSpotContactService();

        $this->hubspotApiDomain = $this->hubSpotApiClient->getHubspotApiDomain();
        $this->hubspotPipelineStageId = $GLOBALS['CFG']['GEN']['HUBSPOT_PIPELINE_STAGE_ID'];
    }

    /**
     * Create a ticket and associate it with a HubSpot contact.
     *
     * A contact will be created if it does not already exist.
     *
     * @param NewTicketDto $newTicketDto
     * @return CreateTicketResult
     */
    public function create(NewTicketDto $newTicketDto): CreateTicketResult
    {
        /*
         * Create ticket.
         */

        $createTicketResult = $this->createTicket($newTicketDto);
        if (!$createTicketResult->isCreated()) {
            return $createTicketResult;
        }

        /*
         * Find/create contact and associate it with the ticket.
         */

        $emailAddress = $newTicketDto->getRequesterEmail();
        $ticketId = $createTicketResult->getTicketId();

        try {
            $contactId = $this->hubSpotContactService->findIdByEmail($emailAddress);
            if (is_null($contactId)) {
                $firstName = $this->getFirstName($newTicketDto->getRequesterName());
                $lastName = $this->getLastName($newTicketDto->getRequesterName());
                $this->hubSpotContactService->createAndAssociateWithTicket(
                    $emailAddress, $firstName, $lastName, $ticketId);
            } else {
                $this->hubSpotContactService->associateWithTicket($contactId, $ticketId);
            }
        } catch (HubSpotException $exception) {
            /*
             * If an exception is caught here, we've likely failed to find/create
             * a HubSpot contact and associate it with the ticket we just created.
             *
             * We include the requester's name and email in the ticket body, so
             * log the exception and continue.
             *
             * This allows the OHM UI to notify the user that their ticket has
             * been created.
             */
            $message = sprintf('Exception caught while attempting to find/create'
                . ' a HubSpot contact and associate it with ticket ID "%s" for email "%s".'
                . ' %s: %s -- %s',
                $ticketId,
                $emailAddress,
                get_class($exception),
                $exception->getMessage(),
                $exception->getTraceAsString()
            );
            error_log($message);
        }

        return $createTicketResult;
    }

    /**
     * Create a ticket.
     *
     * This does NOT handle associating a contact with the ticket.
     *
     * @param NewTicketDto $newTicketDto
     * @return CreateTicketResult
     */
    private function createTicket(NewTicketDto $newTicketDto): CreateTicketResult
    {
        $requestBody = $this->generateCreateRequestBody($newTicketDto);
        $hubspotResponse = $this->submitTicket($requestBody,
            $newTicketDto->getRequesterUserAgent());

        $createTicketResult = $this->buildTicketCreationResult($hubspotResponse);

        if (!$createTicketResult->isCreated()) {
            $errorMessage = sprintf('Failed to create HubSpot ticket.'
                . ' OHM error ID: %s'
                . "\n\nRequest data: %s"
                . "\n\nHubSpot API status code: %d"
                . "\n\nHubSpot API response: %s",
                $createTicketResult->getOhmErrorId(),
                $newTicketDto->toString(),
                $createTicketResult->getApiStatusCode(),
                print_r($createTicketResult->getApiResponse(), true)
            );
            error_log($errorMessage);
        }

        return $createTicketResult;
    }

    /**
     * Build a CreateTicketResult object using a HubSpot response.
     *
     * @param array $hubspotResponse The result of an API request to
     *                               HubSpot for ticket creation.
     *                               Format:
     *                                  [
     *                                      'httpStatus' => 201,
     *                                      'rawApiResponse' => [],
     *                                  ]
     * @return CreateTicketResult
     */
    private function buildTicketCreationResult(array $hubspotResponse): CreateTicketResult
    {
        $rawApiResponse = $hubspotResponse['rawApiResponse'];
        $httpStatus = $hubspotResponse['httpStatus'];

        $createTicketResult = new CreateTicketResult();
        $createTicketResult->setApiResponse($rawApiResponse);
        $createTicketResult->setApiStatusCode($httpStatus);

        $isTicketCreated = 201 == $hubspotResponse['httpStatus'];
        $createTicketResult->setCreated($isTicketCreated);

        if ($isTicketCreated) {
            $ticketId = $rawApiResponse['id'];
            $createTicketResult->setTicketId($ticketId);
        } else {
            $errorId = Uuid::uuid4();
            $createTicketResult->setOhmErrorId($errorId);

            $userErrorMessage = sprintf(
                'HubSpot API did not return a 201 status. OHM error ID: %s', $errorId);
            $createTicketResult->addError($userErrorMessage);

            if (
                array_key_exists('message', $rawApiResponse)
                && is_string($rawApiResponse['message'])
            ) {
                $createTicketResult->addError($rawApiResponse['message']);
            }
        }

        return $createTicketResult;
    }

    /**
     * Generate the API request body to send to HubSpot using the
     * provided ticket data.
     *
     * @param NewTicketDto $createTicketDto
     * @return array An associative array of ticket data correctly
     *               formatted for the HubSpot API.
     */
    private function generateCreateRequestBody(NewTicketDto $createTicketDto): array
    {
        $requesterInfo = sprintf("\n\nRequester name: %s\nRequester email: %s\n",
            $createTicketDto->getRequesterName(), $createTicketDto->getRequesterEmail());

        // We can't create tickets and contacts in a single HubSpot API
        // request, so let's append the requester's info to the ticket
        // body as a safeguard against a failure to find/create and
        // associate a contact to the ticket.
        $ticketBody = $createTicketDto->getBody() . $requesterInfo;

        $requestBody = [
            'properties' => [
                'subject' => $createTicketDto->getSubject(),
                'content' => $ticketBody,
                // This is the "hs_pipeline_stage" ID for new tickets.
                // View an existing ticket here:
                //   https://api.hubapi.com/crm/v3/objects/tickets/{{ticketId}}
                //   to get a valid "hs_pipeline_stage" for this config variable.
                //   Be sure to do this in the correct environment. (production vs sandbox)
                'hs_pipeline_stage' => $this->hubspotPipelineStageId,
            ],
        ];
        return $requestBody;
    }

    /**
     * Send the API request to HubSpot to create a ticket.
     *
     * @param array $requestBody The API request body to send.
     * @param string $userAgent The user's browser version string.
     * @return array The API response and HTTP status.
     *               Example:
     *                  [
     *                      'httpStatus' => 201,
     *                      'rawApiResponse' => [],
     *                  ]
     */
    private function submitTicket(array $requestBody, string $userAgent): array
    {
        $apiUrl = sprintf('https://%s/crm/v3/objects/tickets',
            $this->hubspotApiDomain);
        $apiResponse = $this->hubSpotApiClient
            ->sendRequest('POST', $apiUrl, $requestBody, $userAgent);
        return $apiResponse;
    }

    /**
     * Get the first name in a full name string.
     *
     * Notes:
     * - This does NOT handle first names containing spaces.
     *   - "Billie Jean" will return "Billie".
     *
     * @param string $fullName The person's full name.
     * @return string The person's first name.
     */
    private function getFirstName(string $fullName): string
    {
        $names = explode(' ', $fullName);
        $firstName = $names[0];
        return $firstName;
    }

    /**
     * Get the last name in a full name string.
     *
     * Notes:
     * - This does NOT handle surnames containing spaces.
     *   - "Ríos-Prado" will return "Ríos-Prado".
     *   - "La Vallée Poussin" will return "Poussin".
     *
     * @param string $fullName The person's full name.
     * @return string The person's last name.
     */
    private function getLastName(string $fullName): string
    {
        $names = explode(' ', $fullName);
        $totalNames = count($names);
        if (1 == $totalNames) {
            return '';
        }
        $idx = $totalNames - 1;
        $lastName = $names[$idx];
        return $lastName;
    }
}