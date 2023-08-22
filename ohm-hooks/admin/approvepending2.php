<?php

$reqFields = array(
    'school' => 'School',
    'phone' => 'Phone',
    'url' => 'Verification URL',
    'search' => 'Search'
);

/**
 * Get the email message to be used for new account denials.
 *
 * @param string $firstName The user's first name.
 * @param string $lastName The user's last name.
 * @param string $username The user's username.
 * @param int $userGroupId The user's group ID.
 * @return string The raw HTML to be used for the email content.
 */
function getDenyMessage($firstName, $lastName, $username, $userGroupId)
{
    $sanitizedName = Sanitize::encodeStringForDisplay($firstName);
    $sanitizedUsername = Sanitize::encodeStringForDisplay($username);

    $message = "
<p>
Dear ${sanitizedName},
</p>

<p>
Thank you for your interest in Lumen OHM. We are unable to verify your
instructor status, either because you used a non-institutional email, or
because the URL provided did not include information we could use to verify
your status as an instructor.
</p>

<p>
If you still want an OHM instructor account, please respond to this message
<u>from your institutional email address</u> and include:
</p>

<ul>
    <li>
        Your requested username: ${sanitizedUsername}
    </li>
    <li>
        A web page address or other documentation that clearly shows your
        instructor status. If this is unavailable, please send the name and
        email address for a supervisor or departmental colleague who can
        confirm your instructor status.
    </li>
</ul>

<p>
If you did not request an account, no action is required.
</p>

<p>
Thank you,<br/>
The Lumen Team
</p>
";

    return $message;
}


/**
 * Get the email message to be used for new account approvals.
 *
 * @param string $firstName The user's first name.
 * @param string $lastName The user's last name.
 * @param string $username The user's username.
 * @param int $userGroupId The user's group ID.
 * @return string The raw HTML to be used for the email content.
 * @see getApprovalEmailForNonLumenCustomer
 * @see getApprovalEmailForLumenCustomer
 */
function getApproveMessage($firstName, $lastName, $username, $userGroupId)
{
    return isLumenCustomer($userGroupId)
        ? getApprovalEmailForLumenCustomer($firstName, $username)
        : getApprovalEmailForNonLumenCustomer($firstName, $username);
}


/**
 * Get the BCC list to use for new account approval emails.
 *
 * @return array An array of email addresses.
 */
function getApproveBcc()
{
    global $CFG, $group;

    if (isLumenCustomer($group)) {
        return array();
    } else {
        return $CFG['email']['new_acct_bcclist_ohm_hook'];
    }
}


/*
 * The following are what would normally be private methods.
 */


/**
 * Return the approval email content for a non-Lumen customer new account.
 *
 * @param string $firstName The user's first name.
 * @param string $username The user's username.
 * @return string The full, raw email content. Includes HTML.
 * @see getApprovalEmailForLumenCustomer For Lumen customers.
 */
function getApprovalEmailForNonLumenCustomer(string $firstName,
                                             string $username): string
{
    $sanitizedName = Sanitize::encodeStringForDisplay($firstName);
    $sanitizedUsername = Sanitize::encodeStringForDisplay($username);

    return "
<p>
    Hi ${sanitizedName},
</p>

<p>
	Welcome to Lumen OHM! Your instructor access has been approved. Login with
	your username ${sanitizedUsername} and password at
	<a href='${GLOBALS['basesiteurl']}'>ohm.lumenlearning.com</a>
	to get started today.
</p>

<p>
    These resources can help orient you to using OHM:
</p>

<p>
	<ul>
		<li>
		    <strong>Startup Guide:</strong> step-by-step instructions on the
		    basics of course creation, adding homework, quizzes, activities and
		    tests.
		</li>
		<ul>
			<li><a target='_blank' href='https://lumenlearning.zendesk.com/hc/en-us/articles/115010623688-Faculty-Quick-Start-Guide-Lumen-OHM'>Download the PDF</a></li>
			<li><a target='_blank' href='https://lumenlearning.zendesk.com/hc/en-us/categories/115000706447-OHM-Faculty-User-Guide'>Watch the Video Training Series</a></li>
		</ul>
	</ul>
</p>

<p>
    <ul>
        <li>
            <strong>Complete User Guide:</strong> more detailed information
            about every feature of OHM including course creation, course
            management, LMS integrations and more.
        </li>
		<ul>
			<li><a target='_blank' href='https://lumenlearning.zendesk.com/hc/en-us/categories/115000706447-OHM-Faculty-User-Guide'>View the entire OHM User Guide.</a></li>
		</ul>
    </ul>
</p>

<p>
    As you explore OHM and begin building courses, we’ll check in on your
    progress. Our standard pricing is just $25 per student for full use of
    courseware, etext, online homework, and other learning content. Typically
    this is handled through the bookstore, via a course materials fee, or by
    students paying Lumen directly. Your institution may have an agreement in
    place to cover this cost for your students. When you’re ready to enroll
    students, we’ll confirm how to handle payment. Learn more about payment
    options
    <a target='_blank' href='https://lumenlearning.com/how/payment-options/'>here</a>.
</p>

<p>
    We appreciate your interest in Open Educational Resources (OER) and look
    forward to partnering with you to create affordable and effective math
    courses. We welcome you to the Lumen OHM community!
</p>
";
}


/**
 * Return the approval email content for a Lumen customer new account.
 *
 * @param string $firstName The user's first name.
 * @param string $username The user's username.
 * @return string The full, raw email content. Includes HTML.
 * @see getApprovalEmailForNonLumenCustomer For non-Lumen customers.
 */
function getApprovalEmailForLumenCustomer(string $firstName,
                                          string $username): string
{
    $sanitizedName = Sanitize::encodeStringForDisplay($firstName);
    $sanitizedUsername = Sanitize::encodeStringForDisplay($username);

    return "
    <p>
      Hi ${sanitizedName},
    </p>

    <p>
        Welcome to Lumen Learning's Online Homework Manager (aka OHM). Your instructor access has been approved. Login with your  username ${sanitizedUsername} and password at 
        <a href='${GLOBALS['basesiteurl'] }'>ohm.lumenlearning.com</a> to get started.
    </p>

    <p>
        The following resources can help guide and support you as you start to explore OHM:
    </p>

    <p>
      <ul>
        <li>
          <p>
            <strong>
              <a 
                href='https://support.lumenlearning.com/hc/en-us/articles/360049502553-OHM-Faculty-StartUp-Guide' 
                target='_blank'> Start Up Guide</a>:
            </strong> includes step-by-step instructions on the basics of course creation, assigning homework, and adding quizzes, activities and tests.       
          </p>
        </li>
        <li>
          <p>
            <strong>
              <a 
                href='https://www.youtube.com/playlist?list=PL7Xv4ZtgRLGRFw9yP-YPJaFjUCG43jT2i' 
                target='_blank'>OHM Video Guides</a>:
            </strong> a compilation of short videos offering tutorials on effectively navigating and utilizing OHM's features.
          </p>
        </li>
        <li>
          <p>
            <strong>
              <a 
                href='https://support.lumenlearning.com/hc/en-us/categories/115000706447-OHM-Faculty-User-Guide' 
                target='_blank'>Complete User Guide</a>:
            </strong> includes more detailed information about features and functionality found in OHM, including guidance for course creation, course management, LMS integration, and more.
          </p>
        </li>
      </ul>
    </p>

    <p>
        We appreciate your interest in Lumen Learning OHM and look forward to partnering with you to provide your students with affordable and effective course materials. Welcome to the Lumen community!
    </p>

    <p>
      Thank you,<br/>
      The Lumen Team
    </p>
";
}


/**
 * Determine Lumen customer status by group ID.
 *
 * @param int $groupId The group ID.
 * @return bool True if the group is a Lumen customer. False if not.
 */
function isLumenCustomer($groupId): bool
{
    global $DBH;

    $groupId = Sanitize::onlyInt($groupId);

    $stm = $DBH->prepare("SELECT grouptype FROM imas_groups WHERE id = :id");
    $stm->execute(array(':id' => $groupId));
    $groupType = $stm->fetchColumn(0);

    return (1 == $groupType) ? true : false;
}
