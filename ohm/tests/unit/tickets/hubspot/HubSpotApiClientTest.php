<?php

namespace OHM\Tests\Tickets;

use OHM\Includes\HttpRequest;
use OHM\tickets\hubspot\HubSpotApiClient;
use OHM\tickets\hubspot\HubSpotClientException;
use PHPUnit\Framework\TestCase;

class HubSpotApiClientTest extends TestCase
{
    private $curlMock;

    function setUp(): void
    {
        $this->curlMock = $this->createMock(HttpRequest::class);

        // We're mocking curl requests but HubSpotApiClient still expects
        // these to be defined in OHM's global config.
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DOMAIN'] = 'localhost';
        $GLOBALS['CFG']['GEN']['HUBSPOT_ACCESS_TOKEN'] = 'token_here';
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DEBUG'] = false;
    }

    /*
     * create
     */

    function testSendRequest_Success()
    {
        $this->curlMock->method('getInfo')->willReturn(201);
        $this->curlMock->method('execute')->willReturn('{"flavor": "purple"}');
        $this->curlMock->expects($this->once())->method('reset');
        $this->curlMock->expects($this->once())->method('close');

        // Class under test
        $apiClient = new HubSpotApiClient($this->curlMock);
        $response = $apiClient->sendRequest('POST',
            'https://localhost/', ['meow'], 'Lynx');

        // Assertions
        $this->assertIsArray($response);

        $this->assertArrayHasKey('httpStatus', $response);
        $this->assertEquals(201, $response['httpStatus']);

        $this->assertArrayHasKey('rawApiResponse', $response);
        $apiResponse = $response['rawApiResponse'];
        $this->assertEquals('purple', $apiResponse['flavor']);
    }

    function testSendRequest_Failure()
    {
        $this->curlMock->method('getInfo')->willReturn(400);
        $this->curlMock->method('execute')->willReturn('{"message": "Invalid request."}');
        $this->curlMock->expects($this->once())->method('reset');
        $this->curlMock->expects($this->once())->method('close');

        // Class under test
        $apiClient = new HubSpotApiClient($this->curlMock);
        $response = $apiClient->sendRequest('POST',
            'https://localhost/', ['meow'], 'Lynx');

        // Assertions
        $this->assertIsArray($response);

        $this->assertArrayHasKey('httpStatus', $response);
        $this->assertEquals(400, $response['httpStatus']);

        $this->assertArrayHasKey('rawApiResponse', $response);
        $apiResponse = $response['rawApiResponse'];
        $this->assertEquals('Invalid request.', $apiResponse['message']);
    }

    function testSendRequest_InvalidRequestMethod()
    {
        $this->expectException(HubSpotClientException::class);

        // Class under test
        $apiClient = new HubSpotApiClient();
        $apiClient->sendRequest('MEOW',
            'https://localhost/', ['meow'], 'Lynx');
    }

}