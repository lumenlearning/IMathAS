<?php

namespace OHM\Tests\Tickets;

use OHM\tickets\CreateTicketResult;
use OHM\tickets\hubspot\HubSpotApiClient;
use OHM\tickets\hubspot\HubSpotContactService;
use OHM\tickets\hubspot\HubSpotTicketService;
use OHM\tickets\NewTicketDto;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HubSpotTicketTest extends TestCase
{
    private $hubSpotApiClientMock;
    private $hubSpotContactServiceMock;

    private $hubSpotTicket;

    function setUp(): void
    {
        // We're mocking curl requests but HubSpotApiClient still expects
        // these to be defined in OHM's global config.
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DOMAIN'] = 'localhost';
        $GLOBALS['CFG']['GEN']['HUBSPOT_PIPELINE_STAGE_ID'] = 'id_here';

        $this->hubSpotApiClientMock = $this->createMock(HubSpotApiClient::class);
        $this->hubSpotContactServiceMock = $this->createMock(HubSpotContactService::class);

        $this->hubSpotTicket = new HubSpotTicketService(
            $this->hubSpotApiClientMock,
            $this->hubSpotContactServiceMock
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     * @throws ReflectionException
     */
    function invokePrivateMethod(object &$object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /*
     * create
     */

    function testCreate_Success()
    {
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 201,
            'rawApiResponse' => [
                'id' => '92653074',
            ],
        ]);
        $this->hubSpotContactServiceMock->method('findIdByEmail')->willReturn('42');

        $newTicketDto = new NewTicketDto();
        $newTicketDto->setRequesterName('Dev Tester')
            ->setRequesterEmail('dev@example.com')
            ->setRequesterUserAgent('Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.0')
            ->setSubject('The printer is on fire')
            ->setBody('The fire is very colorful. Please send help.');

        // Class under test
        $createTicketResult = $this->hubSpotTicket->create($newTicketDto);

        // Assertions
        $this->assertInstanceOf(CreateTicketResult::class, $createTicketResult);
        $this->assertTrue($createTicketResult->isCreated());
        $this->assertEquals("92653074", $createTicketResult->getTicketId());
        $this->assertEquals([], $createTicketResult->getErrors());
        $this->assertEquals(201, $createTicketResult->getApiStatusCode());
        $this->assertNull($createTicketResult->getOhmErrorId());

        $flavor = $createTicketResult->getApiResponse()['id'];
        $this->assertEquals($flavor, '92653074');
    }

    function testCreate_Failure()
    {
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 401,
            'rawApiResponse' => [
                'status' => 'error',
                'message' => 'Authentication credentials not found. This API supports OAuth 2.0 authentication and you can find more details at https://developers.hubspot.com/docs/methods/auth/oauth-overview',
                'correlationId' => '50d4724d-96bf-4130-8b56-b091338beb1a',
                'category' => 'INVALID_AUTHENTICATION',
                'meow' => 'This is a canned OHM test response.',
            ],
        ]);

        $newTicketDto = new NewTicketDto();
        $newTicketDto->setRequesterName('Dev Tester')
            ->setRequesterEmail('dev@example.com')
            ->setRequesterUserAgent('Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.0')
            ->setSubject('The printer is on fire')
            ->setBody('The fire is very colorful. Please send help.');

        // Class under test
        $createTicketResult = $this->hubSpotTicket->create($newTicketDto);

        // Assertions
        $this->assertInstanceOf(CreateTicketResult::class, $createTicketResult);
        $this->assertFalse($createTicketResult->isCreated());
        $this->assertEquals(401, $createTicketResult->getApiStatusCode());

        $ohmErrorId = $createTicketResult->getOhmErrorId();
        $this->assertNotEmpty($ohmErrorId);
        $this->assertIsString($ohmErrorId);

        $errors = $createTicketResult->getErrors();
        $this->assertStringContainsString($ohmErrorId, $errors[0]);
        $this->assertStringContainsString("Authentication credentials not found", $errors[1]);
    }

    /*
     * generateCreateRequestBody
     */

    function testGenerateCreateRequestBody()
    {
        $newTicketDto = new NewTicketDto();
        $newTicketDto->setRequesterName('Dev Tester')
            ->setRequesterEmail('dev@example.com')
            ->setRequesterUserAgent('Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.0')
            ->setSubject('The printer is on fire')
            ->setBody('The fire is very colorful. Please send help.');

        $requestBody = $this->invokePrivateMethod($this->hubSpotTicket,
            'generateCreateRequestBody', array($newTicketDto));

        $this->assertEquals('The printer is on fire', $requestBody['properties']['subject']);
        $this->assertStringContainsString('The fire is very colorful. Please send help.',
            $requestBody['properties']['content']);
        // Ensure the requester's info is appended to the ticket body.
        $this->assertStringContainsString('dev@example.com',
            $requestBody['properties']['content']);
        $this->assertStringContainsString('Dev Tester',
            $requestBody['properties']['content']);
    }
}