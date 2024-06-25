<?php

namespace OHM\Tests\Tickets;

use OHM\tickets\hubspot\HubSpotApiClient;
use OHM\tickets\hubspot\HubSpotContactService;
use OHM\tickets\hubspot\HubSpotException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HubSpotContactServiceTest extends TestCase
{
    // When searching for a contact that doesn't exist.
    const CONTACT_NOT_FOUND = [
        "status" => "COMPLETE",
        "results" => [],
        "numErrors" => 1,
        "errors" => [
            [
                "status" => "error",
                "category" => "OBJECT_NOT_FOUND",
                "message" => "Could not get some CONTACT objects, they may be deleted or not exist. Check that ids are valid.",
                "context" => [
                    "ids" => [
                        'meows@loudly.lol',
                    ],
                ],
            ],
        ],
        "startedAt" => "2024-04-24T19:21:09.694Z",
        "completedAt" => "2024-04-24T19:21:09.747Z",
    ];

    // When associating an existing contact to an existing ticket.
    const ASSOCIATION_SUCCESS = [
        "status" => "COMPLETE",
        "results" => [
            [
                "from" => [
                    "id" => "2644992430",
                ],
                "to" => [
                    "id" => "9532426638",
                ],
                "associationSpec" => [
                    "associationCategory" => "HUBSPOT_DEFINED",
                    "associationTypeId" => 16,
                ],
            ],
            [
                "from" => [
                    "id" => "9532426638",
                ],
                "to" => [
                    "id" => "2644992430",
                ],
                "associationSpec" => [
                    "associationCategory" => "HUBSPOT_DEFINED",
                    "associationTypeId" => 15,
                ],
            ],
        ],
        "startedAt" => "2024-04-24T19:09:58.035Z",
        "completedAt" => "2024-04-24T19:09:58.116Z",
    ];

    // When creating a new contact and associating it to an existing
    // ticket, all in a single request.
    const CREATE_AND_ASSOCIATE_SUCCESS = [
        "id" => "15875405214",
        "properties" => [
            "createdate" => "2024-04-25T20:48:27.793Z",
            "email" => 'meows@loudly.lol',
            "firstname" => "Lumen",
            "hs_all_contact_vids" => "15875405214",
            "hs_email_domain" => "loudly.lol",
            "hs_is_contact" => "true",
            "hs_is_unworked" => "true",
            "hs_lifecyclestage_other_date" => "2024-04-25T20:48:27.793Z",
            "hs_marketable_until_renewal" => "false",
            "hs_object_id" => "15875405214",
            "hs_object_source" => "INTEGRATION",
            "hs_object_source_id" => "3217786",
            "hs_object_source_label" => "INTEGRATION",
            "hs_pipeline" => "contacts-lifecycle-pipeline",
            "lastmodifieddate" => "2024-04-25T20:48:27.793Z",
            "lastname" => "Learning",
            "lifecyclestage" => "other",
        ],
        "createdAt" => "2024-04-25T20:48:27.793Z",
        "updatedAt" => "2024-04-25T20:48:27.793Z",
        "archived" => false,
    ];

    // When creating a new contact and associating it to an existing
    // ticket, all in a single request, but the contact already exists.
    const CREATE_AND_ASSOCIATE_ERROR_CONTACT_EXISTS = [
        "status" => "error",
        "message" => "Contact already exists. Existing ID: 15875483046",
        "correlationId" => "5eb98bca-fe69-45ba-bc76-6a3bd3cc3968",
        "category" => "CONFLICT"
    ];

    private HubSpotApiClient $hubSpotApiClientMock;

    private HubSpotContactService $hubSpotContactService;

    function setUp(): void
    {
        // We're mocking curl requests but HubSpotApiClient still expects
        // these to be defined in OHM's global config.
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DOMAIN'] = 'localhost';
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DEBUG'] = false;

        $this->hubSpotApiClientMock = $this->createMock(HubSpotApiClient::class);

        $this->hubSpotContactService = new HubSpotContactService($this->hubSpotApiClientMock);
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
     * createAndAssociateWithTicket
     */

    public function testCreateAndAssociateWithTicket()
    {
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 201,
            'rawApiResponse' => self::CREATE_AND_ASSOCIATE_SUCCESS,
        ]);

        $contactId = $this->hubSpotContactService->createAndAssociateWithTicket(
            'meows@loudly.lol', 'FirstName', 'LastName', '1234');

        $this->assertEquals('15875405214', $contactId);
    }

    public function testCreateAndAssociateWithTicket_UnexpectedHttpStatus()
    {
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 418,
            'rawApiResponse' => ['oops' => 'No success for you!'],
        ]);

        $this->expectException(HubSpotException::class);

        $this->hubSpotContactService->createAndAssociateWithTicket(
            'meows@loudly.lol', 'FirstName', 'LastName', '1234');
    }

    public function testCreateAndAssociateWithTicket_ContactAlreadyExists()
    {
        $createAndAssociateResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => self::CREATE_AND_ASSOCIATE_ERROR_CONTACT_EXISTS,
        ];
        $associateResponse = [
            'httpStatus' => 200,
            'rawApiResponse' => self::ASSOCIATION_SUCCESS,
        ];
        $this->hubSpotApiClientMock->method('sendRequest')
            ->willReturnOnConsecutiveCalls($createAndAssociateResponse, $associateResponse);

        $contactId = $this->hubSpotContactService->createAndAssociateWithTicket(
            'meows@loudly.lol', 'FirstName', 'LastName', '1234');

        $this->assertEquals('15875483046', $contactId);
    }

    /*
     * associateWithTicket
     */

    public function testAssociateWithTicket()
    {
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 200,
            'rawApiResponse' => self::ASSOCIATION_SUCCESS,
        ]);

        $result = $this->hubSpotContactService->associateWithTicket(
            '2644992430', '9532426638');

        $this->assertTrue($result);
    }

    public function testAssociateWithTicket_UnexpectedHttpStatus()
    {
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 418,
            'rawApiResponse' => self::ASSOCIATION_SUCCESS,
        ]);

        $this->expectException(HubSpotException::class);

        $this->hubSpotContactService->associateWithTicket('2644992430', '9532426638');
    }

    public function testAssociateWithTicket_UnexpectedBodyStatus()
    {
        $unknownStatus = self::ASSOCIATION_SUCCESS;
        $unknownStatus['status'] = 'meow';
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 200,
            'rawApiResponse' => $unknownStatus,
        ]);

        $this->expectException(HubSpotException::class);

        $this->hubSpotContactService->associateWithTicket('2644992430', '9532426638');
    }

    public function testAssociateWithTicket_MissingResults()
    {
        $unexpectedResults = self::ASSOCIATION_SUCCESS;
        unset($unexpectedResults['results']);
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 200,
            'rawApiResponse' => $unexpectedResults,
        ]);

        $this->expectException(HubSpotException::class);

        $this->hubSpotContactService->associateWithTicket('2644992430', '9532426638');
    }

    public function testAssociateWithTicket_UnexpectedResults()
    {
        $unexpectedResults = self::ASSOCIATION_SUCCESS;
        $unexpectedResults['results'] = 'meow';
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 200,
            'rawApiResponse' => $unexpectedResults,
        ]);

        $this->expectException(HubSpotException::class);

        $this->hubSpotContactService->associateWithTicket('2644992430', '9532426638');
    }

    public function testAssociateWithTicket_WrongResultCount()
    {
        $unexpectedResults = self::ASSOCIATION_SUCCESS;
        unset($unexpectedResults['results'][1]);
        $this->hubSpotApiClientMock->method('sendRequest')->willReturn([
            'httpStatus' => 200,
            'rawApiResponse' => $unexpectedResults,
        ]);

        $this->expectException(HubSpotException::class);

        $this->hubSpotContactService->associateWithTicket('2644992430', '9532426638');
    }

    /*
     * handleSearchMultiStatusResponse
     */

    public function testHandleSearchMultiStatusResponse()
    {
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => self::CONTACT_NOT_FOUND,
        ];

        $contactId = $this->invokePrivateMethod($this->hubSpotContactService,
            'handleSearchMultiStatusResponse', ['meows@loudly.lol', $hubspotResponse]);

        $this->assertNull($contactId);
    }

    public function testHandleSearchMultiStatusResponse_NoStatus()
    {
        $noStatus = self::CONTACT_NOT_FOUND;
        unset($noStatus['status']);
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => $noStatus,
        ];

        $this->expectException(HubSpotException::class);

        $this->invokePrivateMethod($this->hubSpotContactService,
            'handleSearchMultiStatusResponse', ['meows@loudly.lol', $hubspotResponse]);
    }

    public function testHandleSearchMultiStatusResponse_WrongStatus()
    {
        $wrongStatus = self::CONTACT_NOT_FOUND;
        $wrongStatus['status'] = 'meow';
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => $wrongStatus,
        ];

        $this->expectException(HubSpotException::class);

        $this->invokePrivateMethod($this->hubSpotContactService,
            'handleSearchMultiStatusResponse', ['meows@loudly.lol', $hubspotResponse]);
    }

    public function testHandleSearchMultiStatusResponse_NoErrors()
    {
        $noErrors = self::CONTACT_NOT_FOUND;
        unset($noErrors['errors']);
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => $noErrors,
        ];

        $this->expectException(HubSpotException::class);

        $this->invokePrivateMethod($this->hubSpotContactService,
            'handleSearchMultiStatusResponse', ['meows@loudly.lol', $hubspotResponse]);
    }

    public function testHandleSearchMultiStatusResponse_EmptyErrors()
    {
        $emptyErrors = self::CONTACT_NOT_FOUND;
        $emptyErrors['errors'] = [];
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => $emptyErrors,
        ];

        $this->expectException(HubSpotException::class);

        $this->invokePrivateMethod($this->hubSpotContactService,
            'handleSearchMultiStatusResponse', ['meows@loudly.lol', $hubspotResponse]);
    }

    public function testHandleSearchMultiStatusResponse_UnknownError()
    {
        $unknownError = self::CONTACT_NOT_FOUND;
        $unknownError['errors'] = [
            [
                "status" => "error",
                "category" => "KITTY_NEVER_FED",
                "message" => "Cat claims they have never been fed.",
                "context" => [
                    "ids" => [
                        "meows@loudly.lol"
                    ]
                ]
            ]
        ];
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => $unknownError,
        ];

        $this->expectException(HubSpotException::class);

        $this->invokePrivateMethod($this->hubSpotContactService,
            'handleSearchMultiStatusResponse', ['meows@loudly.lol', $hubspotResponse]);
    }

    /*
     * getContactIdFromConflictResponse
     */

    public function testGetContactIdFromConflictResponse()
    {
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => [
                'status' => 'error',
                'message' => 'Contact already exists. Existing ID: 15875405214',
                'correlationId' => '5eb98bca-fe69-45ba-bc76-6a3bd3cc3968',
                'category' => 'CONFLICT'
            ],
        ];

        $contactId = $this->invokePrivateMethod($this->hubSpotContactService,
            'getContactIdFromConflictResponse', ['meows@loudly.lol', $hubspotResponse]);

        $this->assertEquals('15875405214', $contactId);
    }

    public function testGetContactIdFromConflictResponse_MessageMissing()
    {
        $hubspotResponse = [
            'httpStatus' => 409,
            'rawApiResponse' => [
                'status' => 'error',
                'correlationId' => '5eb98bca-fe69-45ba-bc76-6a3bd3cc3968',
                'category' => 'CONFLICT'
            ],
        ];

        $this->expectException(HubSpotException::class);

        $this->invokePrivateMethod($this->hubSpotContactService,
            'getContactIdFromConflictResponse', ['meows@loudly.lol', $hubspotResponse]);
    }

    /*
     * parseContactIdFromConflictResponse
     */

    function testParseContactIdFromConflictResponse()
    {
        $rawApiResponse = [
            'status' => 'error',
            'message' => 'Contact already exists. Existing ID: 15875405214',
            'correlationId' => '5eb98bca-fe69-45ba-bc76-6a3bd3cc3968',
            'category' => 'CONFLICT'
        ];

        $contactId = $this->invokePrivateMethod($this->hubSpotContactService,
            'parseContactIdFromConflictResponse', array($rawApiResponse));

        $this->assertEquals('15875405214', $contactId);
    }

    function testParseContactIdFromConflictResponse_NoMatch()
    {
        $rawApiResponse = [
            'status' => 'error',
            'message' => 'Contact already exists. But no existing ID for you.',
            'correlationId' => '5eb98bca-fe69-45ba-bc76-6a3bd3cc3968',
            'category' => 'CONFLICT'
        ];

        $contactId = $this->invokePrivateMethod($this->hubSpotContactService,
            'parseContactIdFromConflictResponse', array($rawApiResponse));

        $this->assertNull($contactId);
    }

    function testParseContactIdFromConflictResponse_MessageMissing()
    {
        $rawApiResponse = [
            'status' => 'error',
            'correlationId' => '5eb98bca-fe69-45ba-bc76-6a3bd3cc3968',
            'category' => 'CONFLICT'
        ];

        $contactId = $this->invokePrivateMethod($this->hubSpotContactService,
            'parseContactIdFromConflictResponse', array($rawApiResponse));

        $this->assertNull($contactId);
    }

}