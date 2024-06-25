<?php

namespace OHM\tickets;

interface SupportTicketService
{
    /**
     * Create a new support ticket.
     *
     * @param NewTicketDto $newTicketDto
     * @return CreateTicketResult
     */
    public function create(NewTicketDto $newTicketDto): CreateTicketResult;
}