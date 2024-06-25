<?php

namespace OHM\tickets;

use OHM\tickets\hubspot\HubSpotTicketService;
use OHM\tickets\zendesk\ZendeskTicketService;

class SupportTicketServiceFactory
{

    /**
     * Get an instance of a SupportTicketService implementation.
     *
     * The correct support ticket service provider will be chosen
     * based on config from environment variables.
     *
     * @return \OHM\tickets\SupportTicketService
     */
    public static function getSupportTicketService(): SupportTicketService
    {
        $serviceProvider = $GLOBALS['CFG']['GEN']['SUPPORT_TICKET_SERVICE'];
        $serviceProvider = strtolower($serviceProvider);

        if ('hubspot' == $serviceProvider) {
            $supportTicketService = new HubSpotTicketService();
        } else {
            $supportTicketService = new ZendeskTicketService();
        }

        return $supportTicketService;
    }
}