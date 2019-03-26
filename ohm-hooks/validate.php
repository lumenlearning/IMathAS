<?php


function onLogin()
{
    // If post data contains ekey and courseid, then check for user enrollment.
    if ($_POST['enrollandlogin']) {
        $queryString = Sanitize::fullQueryString(
            sprintf('action=enroll&cid=%d&ekey=%s&enrollandlogin=1',
                $_POST['cid'], $_POST['ekey'])
        );

        header(sprintf('Location: %s/actions.php?%s', $GLOBALS['basesiteurl'],
            $queryString));
        exit;
    }
}


/**
 * Return a list of files allowed for request via LTI.
 *
 * @return array An array of filenames.
 */
function allowedInAssessment()
{
    return array(
        'process_activation.php',
        'activation_confirmation.php',
        'activation_ajax.php',
        'payment_confirmation.php'
    );
}
