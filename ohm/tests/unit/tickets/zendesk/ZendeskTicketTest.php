<?php

namespace OHM\Tests\Tickets;

use OHM\Includes\HttpRequest;
use OHM\tickets\CreateTicketResult;
use OHM\tickets\NewTicketDto;
use OHM\tickets\zendesk\ZendeskTicketService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ZendeskTicketTest extends TestCase
{
    private $curlMock;

    function setUp(): void
    {
        $this->curlMock = $this->createMock(HttpRequest::class);

        // We're mocking curl requests but ZendeskTicketService still expects
        // these to be defined in OHM's global config.
        $GLOBALS['CFG']['GEN']['zdurl'] = 'https://localhost/api/v2';
        $GLOBALS['CFG']['GEN']['zduser'] = 'devTester';
        $GLOBALS['CFG']['GEN']['zdapikey'] = 'supahSekrit';
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
        $this->curlMock->method('getInfo')->willReturn(201);
        $this->curlMock->method('execute')->willReturn('{"ticket": {"id": "1234509876"}}');
        $this->curlMock->expects($this->once())->method('reset');
        $this->curlMock->expects($this->once())->method('close');

        $newTicketDto = new NewTicketDto();
        $newTicketDto->setRequesterName('Dev Tester')
            ->setRequesterEmail('dev@example.com')
            ->setRequesterUserAgent('Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.0')
            ->setSubject('The printer is on fire')
            ->setBody('The fire is very colorful. Please send help.');

        // Class under test
        $zendeskTicket = new ZendeskTicketService();
        $zendeskTicket->setHttpRequest($this->curlMock);
        $createTicketResult = $zendeskTicket->create($newTicketDto);

        // Assertions
        $this->assertInstanceOf(CreateTicketResult::class, $createTicketResult);
        $this->assertTrue($createTicketResult->isCreated());
        $this->assertEquals("1234509876", $createTicketResult->getTicketId());
        $this->assertEquals([], $createTicketResult->getErrors());
        $this->assertEquals(201, $createTicketResult->getApiStatusCode());
        $this->assertNull($createTicketResult->getOhmErrorId());

        $flavor = $createTicketResult->getApiResponse()['ticket']['id'];
        $this->assertEquals($flavor, '1234509876');
    }

    function testCreate_Failure()
    {
        $this->curlMock->method('getInfo')->willReturn(401);
        $this->curlMock->method('execute')->willReturn('{"error": "Couldn\'t authenticate you"}');
        $this->curlMock->expects($this->once())->method('reset');
        $this->curlMock->expects($this->once())->method('close');

        $newTicketDto = new NewTicketDto();
        $newTicketDto->setRequesterName('Dev Tester')
            ->setRequesterEmail('dev@example.com')
            ->setRequesterUserAgent('Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.0')
            ->setSubject('The printer is on fire')
            ->setBody('The fire is very colorful. Please send help.');

        // Class under test
        $zendeskTicket = new ZendeskTicketService();
        $zendeskTicket->setHttpRequest($this->curlMock);
        $createTicketResult = $zendeskTicket->create($newTicketDto);

        // Assertions
        $this->assertInstanceOf(CreateTicketResult::class, $createTicketResult);
        $this->assertFalse($createTicketResult->isCreated());
        $this->assertEquals(401, $createTicketResult->getApiStatusCode());

        $ohmErrorId = $createTicketResult->getOhmErrorId();
        $this->assertNotEmpty($ohmErrorId);
        $this->assertIsString($ohmErrorId);

        $errors = $createTicketResult->getErrors();
        $this->assertStringContainsString($ohmErrorId, $errors[0]);
        $this->assertEquals("Couldn't authenticate you", $errors[1]);
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

        $zendeskTicket = new ZendeskTicketService();
        $requestBody = $this->invokePrivateMethod($zendeskTicket,
            'generateCreateRequestBody', array($newTicketDto));

        $this->assertEquals('Dev Tester', $requestBody['ticket']['requester']['name']);
        $this->assertEquals('dev@example.com', $requestBody['ticket']['requester']['email']);
        $this->assertEquals('The printer is on fire', $requestBody['ticket']['subject']);
        $this->assertEquals('The fire is very colorful. Please send help.',
            $requestBody['ticket']['comment']['body']);
    }
}