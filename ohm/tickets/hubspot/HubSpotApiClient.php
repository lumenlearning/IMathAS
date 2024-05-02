<?php

namespace OHM\tickets\hubspot;

use OHM\Includes\CurlRequest;
use OHM\Includes\HttpRequest;

class HubSpotApiClient
{
    const ALLOWED_METHODS = ['GET', 'POST', 'PUT'];

    private string $hubspotApiDomain; // Example: "api.hubapi.com"
    private string $hubspotAccessToken;
    private bool $debugEnabled;
    private int $curlTimeout = 10; // in seconds

    private HttpRequest $httpRequest;

    function __construct(HttpRequest $httpRequest = null)
    {
        $this->httpRequest = $httpRequest ?? new CurlRequest();

        $this->hubspotApiDomain = $GLOBALS['CFG']['GEN']['HUBSPOT_API_DOMAIN'];
        $this->hubspotAccessToken = $GLOBALS['CFG']['GEN']['HUBSPOT_ACCESS_TOKEN'];
        $this->debugEnabled = $GLOBALS['CFG']['GEN']['HUBSPOT_API_DEBUG'];
    }

    /**
     * Get the domain name used in HubSpot API request URLs.
     *
     * @return string The HubSpot API domain name.
     */
    public function getHubspotApiDomain(): string
    {
        return $this->hubspotApiDomain;
    }

    /**
     * Send a HubSpot API request.
     *
     * @param string $httpMethod One of self::ALLOWED_METHODS.
     * @param string $url The API URL to send the request to.
     * @param array $requestBody The API request body to send.
     * @param string $userAgent The user's browser version string.
     * @return array The API response and HTTP status.
     *               Example:
     *                  [
     *                      'httpStatus' => 201,
     *                      'rawApiResponse' => [],
     *                  ]
     * @throws HubSpotClientException Thrown on invalid HTTP methods.
     */
    public function sendRequest(
        string $httpMethod,
        string $url,
        array  $requestBody,
        string $userAgent
    ): array
    {
        $httpMethod = strtoupper($httpMethod);
        if (!in_array($httpMethod, self::ALLOWED_METHODS)) {
            $errorMessage = sprintf(
                'Invalid HTTP method "%s" requested in %s. Allowed methods: %s',
                $httpMethod,
                HubSpotApiClient::class,
                implode(', ', self::ALLOWED_METHODS)
            );
            throw new HubSpotClientException($errorMessage);
        }

        $this->httpRequest->reset();
        $this->httpRequest->setUrl($url);

        $headers = [
            'Authorization: Bearer ' . $this->hubspotAccessToken,
            'Content-Type: application/json',
            'Accept: application/json',
            'Host: ' . $this->hubspotApiDomain,
        ];
        $curlPostFields = json_encode($requestBody);

        // Set options for curl transfer
        $this->httpRequest->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->httpRequest->setOption(CURLOPT_MAXREDIRS, 10);
        $this->httpRequest->setOption(CURLOPT_CUSTOMREQUEST, $httpMethod);
        $this->httpRequest->setOption(CURLOPT_POSTFIELDS, $curlPostFields);
        $this->httpRequest->setOption(CURLOPT_HTTPHEADER, $headers);
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