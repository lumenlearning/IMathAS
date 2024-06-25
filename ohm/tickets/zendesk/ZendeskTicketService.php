<?php

namespace OHM\tickets\zendesk;

use OHM\Includes\CurlRequest;
use OHM\Includes\HttpRequest;
use OHM\tickets\CreateTicketResult;
use OHM\tickets\NewTicketDto;
use OHM\tickets\SupportTicketService;
use Ramsey\Uuid\Uuid;

/**
 * This creates Zendesk support tickets.
 */
class ZendeskTicketService implements SupportTicketService
{
    // Zendesk domain + "/api/v2".
    // Example: "https://lumenlearning.zendesk.com/api/v2"
    private string $zendeskApiBaseUrl;
    private string $zendeskApiUser;
    private string $zendeskApiKey;

    private int $curlTimeout = 10; // in seconds

    private HttpRequest $httpRequest;

    function __construct()
    {
        $this->zendeskApiBaseUrl = $GLOBALS['CFG']['GEN']['zdurl'];
        $this->zendeskApiUser = $GLOBALS['CFG']['GEN']['zduser'];
        $this->zendeskApiKey = $GLOBALS['CFG']['GEN']['zdapikey'];
        $this->httpRequest = new CurlRequest();
    }

    /**
     * Set the implementation of HttpRequest to use.
     * This method is used in tests.
     *
     * @param HttpRequest $httpRequest An implementation of HttpRequest.
     * @return ZendeskTicketService
     */
    public function setHttpRequest(HttpRequest $httpRequest): ZendeskTicketService
    {
        $this->httpRequest = $httpRequest;
        return $this;
    }

    /**
     * Create a new support ticket.
     *
     * @param NewTicketDto $newTicketDto
     * @return CreateTicketResult
     */
    public function create(NewTicketDto $newTicketDto): CreateTicketResult
    {
        $requestBody = $this->generateCreateRequestBody($newTicketDto);
        $zendeskResponse = $this->submitTicket($requestBody,
            $newTicketDto->getRequesterUserAgent());

        $createTicketResult = $this->buildTicketCreationResult($zendeskResponse);

        if (!$createTicketResult->isCreated()) {
            $errorMessage = sprintf('Failed to create Zendesk ticket.'
                . ' OHM error ID: %s'
                . "\n\nRequest data: %s"
                . "\n\nZendesk API status code: %d"
                . "\n\nZendesk API response: %s",
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
     * Build a CreateTicketResult object using a Zendesk response.
     *
     * @param array $zendeskResponse The result of an API request to
     *                               Zendesk for ticket creation.
     *                               Format:
     *                                  [
     *                                      'httpStatus' => 201,
     *                                      'rawApiResponse' => [],
     *                                  ]
     * @return CreateTicketResult
     */
    private function buildTicketCreationResult(array $zendeskResponse): CreateTicketResult
    {
        $rawApiResponse = $zendeskResponse['rawApiResponse'];
        $httpStatus = $zendeskResponse['httpStatus'];

        $createTicketResult = new CreateTicketResult();
        $createTicketResult->setApiResponse($rawApiResponse);
        $createTicketResult->setApiStatusCode($httpStatus);

        $isTicketCreated = 201 == $zendeskResponse['httpStatus'];
        $createTicketResult->setCreated($isTicketCreated);

        if ($isTicketCreated) {
            $ticketId = $rawApiResponse['ticket']['id'];
            $createTicketResult->setTicketId($ticketId);
        } else {
            $errorId = Uuid::uuid4();
            $createTicketResult->setOhmErrorId($errorId);

            $userErrorMessage = sprintf(
                'Zendesk API did not return a 201 status. OHM error ID: %s', $errorId);
            $createTicketResult->addError($userErrorMessage);
        }

        if (array_key_exists('error', $rawApiResponse)) {
            $createTicketResult->addError($rawApiResponse['error']);
        }

        return $createTicketResult;
    }

    /**
     * Generate the API request body to send to Zendesk using the
     * provided ticket data.
     *
     * @param NewTicketDto $createTicketDto
     * @return array An associative array of ticket data correctly
     *               formatted for the Zendesk API.
     */
    private function generateCreateRequestBody(NewTicketDto $createTicketDto): array
    {
        $requestBody = [
            'ticket' => [
                'subject' => $createTicketDto->getSubject(),
                'comment' => [
                    'body' => $createTicketDto->getBody(),
                ],
                'requester' => [
                    'name' => $createTicketDto->getRequesterName(),
                    'email' => $createTicketDto->getRequesterEmail(),
                ],
            ],
        ];
        return $requestBody;
    }

    /**
     * Send the API request to Zendesk to create a ticket.
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
        $this->httpRequest->reset();

        $basicAuth = sprintf('%s/token:%s',
            $this->zendeskApiUser, $this->zendeskApiKey);

        $apiUrl = $this->zendeskApiBaseUrl . '/tickets.json';
        $this->httpRequest->setUrl($apiUrl);

        $curlPostFields = json_encode($requestBody);

        // Set options for curl transfer
        $this->httpRequest->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->httpRequest->setOption(CURLOPT_MAXREDIRS, 10);
        $this->httpRequest->setOption(CURLOPT_USERPWD, $basicAuth);
        $this->httpRequest->setOption(CURLOPT_CUSTOMREQUEST, "POST");
        $this->httpRequest->setOption(CURLOPT_POSTFIELDS, $curlPostFields);
        $this->httpRequest->setOption(CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        $this->httpRequest->setOption(CURLOPT_USERAGENT, $userAgent);
        $this->httpRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->httpRequest->setOption(CURLOPT_TIMEOUT, $this->curlTimeout);

        // Perform curl session and close
        $response = $this->httpRequest->execute();
        $statusCode = $this->httpRequest->getInfo(CURLINFO_HTTP_CODE);
        $this->httpRequest->close();

        // JSON decode the output and return
        $responseAsArray = json_decode($response, true) ?? [];

        return [
            'httpStatus' => $statusCode,
            'rawApiResponse' => $responseAsArray,
        ];
    }
}