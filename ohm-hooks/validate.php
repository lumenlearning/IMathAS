<?php


/**
 * Called during a user's login process.
 *
 * This happens after updating their last login time and before forcing
 * password resets.
 */
function onLogin()
{
    // If post data contains ekey and courseid, then check for user enrollment.
    if (!empty($_POST['enrollandlogin'])) {
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
        'payment_confirmation.php',
        'resync-lms-grades.php',
    );
}

/**
 * Determine if a user is requesting a diagnostic assessment and does
 * NOT require EULA acceptance.
 *
 * @return bool True if diagnostic assessment and does not need EULA acceptance.
 */
function isDiagnostic()
{
    return isset($_SESSION['isdiag']) && !isset($_SESSION['eula_acceptance_required']);
}
