<?php

namespace OHM\Tests\Tickets;

use OHM\tickets\hubspot\HubSpotTicketService;
use OHM\tickets\SupportTicketServiceFactory;
use OHM\tickets\zendesk\ZendeskTicketService;
use PHPUnit\Framework\TestCase;

class SupportTicketServiceFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DOMAIN'] = 'localhost';
        $GLOBALS['CFG']['GEN']['HUBSPOT_ACCESS_TOKEN'] = 'meow';
        $GLOBALS['CFG']['GEN']['HUBSPOT_PIPELINE_STAGE_ID'] = 42;
        $GLOBALS['CFG']['GEN']['HUBSPOT_API_DEBUG'] = false;

        $GLOBALS['CFG']['GEN']['zdapikey'] = 'meow';
        $GLOBALS['CFG']['GEN']['zdurl'] = 'https://localhost/';
        $GLOBALS['CFG']['GEN']['zduser'] = 'user';
    }

    public function testGetSupportTicketService_Default(): void
    {
        $GLOBALS['CFG']['GEN']['SUPPORT_TICKET_SERVICE'] = '';
        $supportTicketService = SupportTicketServiceFactory::getSupportTicketService();
        $this->assertInstanceOf(ZendeskTicketService::class, $supportTicketService);
    }

    public function testGetSupportTicketService_HubSpot(): void
    {
        $GLOBALS['CFG']['GEN']['SUPPORT_TICKET_SERVICE'] = 'hubspot';
        $supportTicketService = SupportTicketServiceFactory::getSupportTicketService();
        $this->assertInstanceOf(HubSpotTicketService::class, $supportTicketService);
    }

    public function testGetSupportTicketService_Zendesk(): void
    {
        $GLOBALS['CFG']['GEN']['SUPPORT_TICKET_SERVICE'] = 'zendesk';
        $supportTicketService = SupportTicketServiceFactory::getSupportTicketService();
        $this->assertInstanceOf(ZendeskTicketService::class, $supportTicketService);
    }
}