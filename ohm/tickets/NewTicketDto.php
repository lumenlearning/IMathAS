<?php

namespace OHM\tickets;

class NewTicketDto
{
    private string $requesterName;
    private string $requesterEmail;
    private string $requesterUserAgent; // The user's browser version string.
    private string $subject; // "The printer is on fire"
    private string $body; // "The fire is very colorful. Please send help."

    /*
     * requesterName
     */

    public function getRequesterName(): string
    {
        return $this->requesterName;
    }

    public function setRequesterName(string $requesterName): NewTicketDto
    {
        $this->requesterName = $requesterName;
        return $this;
    }

    /*
     * requesterEmail
     */

    public function getRequesterEmail(): string
    {
        return $this->requesterEmail;
    }

    public function setRequesterEmail(string $requesterEmail): NewTicketDto
    {
        $this->requesterEmail = $requesterEmail;
        return $this;
    }

    /*
     * requesterUserAgent
     */

    public function getRequesterUserAgent(): string
    {
        return $this->requesterUserAgent;
    }

    public function setRequesterUserAgent(string $requesterUserAgent): NewTicketDto
    {
        $this->requesterUserAgent = $requesterUserAgent;
        return $this;
    }

    /*
     * subject
     */

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): NewTicketDto
    {
        $this->subject = $subject;
        return $this;
    }

    /*
     * body
     */

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): NewTicketDto
    {
        $this->body = $body;
        return $this;
    }

    /*
     * toString
     */

    public function toString(): string
    {
        return sprintf('Name: %s, Email: %s, Subject: %s, Body: %s',
            $this->getRequesterName(),
            $this->getRequesterEmail(),
            $this->getSubject(),
            $this->getBody()
        );
    }
}