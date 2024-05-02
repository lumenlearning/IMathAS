<?php
  require '../init_without_validate.php';

use OHM\tickets\hubspot\HubSpotTicketService;
use OHM\tickets\NewTicketDto;
use OHM\tickets\zendesk\ZendeskTicketService;

/**
   * Loop over $_POST data, find the ones with 'z_' at the beginning, and
   * strip the html and php tags.
   */
  $formData = [];
  foreach($_POST as $key => $value){
    if(preg_match('/^z_/i',$key)){
      $formData[strip_tags($key)] = strip_tags($value);
    }
  }

$ticketBody = $formData['z_description'] . "\n \n" . 'Course ID: '
    . $formData['z_cid'];

$newTicketDto = new NewTicketDto();
$newTicketDto
    ->setRequesterName($formData['z_name'])
    ->setRequesterEmail($formData['z_email'])
    ->setRequesterUserAgent($_SERVER['HTTP_USER_AGENT'])
    ->setSubject($formData['z_subject'])
    ->setBody($ticketBody);

$supportTicketService = $GLOBALS['CFG']['GEN']['SUPPORT_TICKET_SERVICE'];
$supportTicketService = strtolower($supportTicketService);

// This conditional may be removed after migration to HubSpot is
// completed. See OHM-1233.
if ('hubspot' == $supportTicketService) {
    $supportTicket = new HubSpotTicketService();
} else {
    $supportTicket = new ZendeskTicketService();
}
$createTicketResult = $supportTicket->create($newTicketDto);

if ($createTicketResult->isCreated()) {
    if ($createTicketResult->getTicketId()) {
        echo json_encode(['ticket_id' => $createTicketResult->getTicketId()]);
    }
} else {
    // Returning a status 500 beacuse there's no reliable way to determine
    // if we failed due to client input or an API issue. (Zendesk down, etc)
    http_response_code(500);

    $errors = $createTicketResult->getErrors();
    echo json_encode($errors);
}
