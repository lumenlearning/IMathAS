<?php

namespace OHM\tickets;

use PDO;
use Sanitize;

class QuestionBugTicketService
{
    private PDO $dbh;

    function __construct(PDO $dbh = null)
    {
        $this->dbh = $dbh ?? $GLOBALS['DBH'];
    }

    /**
     * Create a support ticket for a question bug report.
     *
     * @param string $reporterUserId The OHM user ID for the person reporting the bug.
     * @param string $reporterUserAgent The bug reporter's browser user agent string.
     * @param string $subject The ticket title.
     * @param string $message The bug report.
     * @param int|null $courseId The question's course ID, if any.
     * @return bool True if a ticket was successfully created. False if not.
     */
    public function createTicket(
        string $reporterUserId,
        string $reporterUserAgent,
        string $subject,
        string $message,
        int    $courseId = null
    ): bool
    {
        $stm = $this->dbh->prepare("SELECT id, FirstName, LastName, email FROM imas_users WHERE id = :id");
        $stm->execute([':id' => $reporterUserId]);
        $userdata = $stm->fetch(PDO::FETCH_ASSOC);

        $reporterName = $userdata['FirstName'] . ' ' . $userdata['LastName'];
        $reporterEmail = $userdata['email'];

        if ($courseId) {
            $message = $message . "\n \nCourse ID: " . $courseId;
        }

        // Messages are created using TinyMCE, so we need to replace
        // a few things and strip HTML tags from the message.
        $message = str_replace('&nbsp;', " ", $message);
        $message = str_replace(['</p>', '<br />'], "\n", $message);
        $message = Sanitize::stripHtmlTags($message);

        $newTicketDto = new NewTicketDto();
        $newTicketDto
            ->setRequesterName($reporterName)
            ->setRequesterEmail($reporterEmail)
            ->setRequesterUserAgent($reporterUserAgent)
            ->setSubject($subject)
            ->setBody($message);
        $supportTicketService = SupportTicketServiceFactory::getSupportTicketService();
        $createTicketResult = $supportTicketService->create($newTicketDto);

        if (!$createTicketResult->isCreated()) {
            $errorMessage = sprintf('Failed to create support ticket for question'
                . ' bug report for user ID %s.'
                . "\nErrors: %s"
                . "\nAPI status: %s"
                . "\nAPI response: %s",
                $reporterUserId,
                $createTicketResult->getErrors(),
                $createTicketResult->getApiStatusCode(),
                $createTicketResult->getApiResponse()
            );
            error_log($errorMessage);
        }

        return $createTicketResult->isCreated();
    }
}