<?php

namespace Desmos\Lti;

require_once(__DIR__ . '/../../includes/OAuth.php');
require_once(__DIR__ . '/../../includes/ltioauthstore.php');
require_once(__DIR__ . '/../../includes/ltiroles.php');

use IMathASLTIOAuthDataStore;
use LTIRoles;
use OAuthConsumer;
use OAuthRequest;
use OAuthServer;
use OAuthSignatureMethod_HMAC_SHA1;
use PDO;

/**
 * Class BasicLti Implements minimum requirements for BLTI launches to Desmos items.
 *
 * Much code and ideas taken from /bltilaunch.php.
 *
 * @package Desmos\Lti
 * @see /bltilaunch.php
 */
class BasicLti
{
    /* @var $_REQUEST */
    private $request;
    /* @var OAuthConsumer */
    private $oauthRequestInfo;

    /*
     * LTI launch data
     */
    private $userid;
    private $org;
    private $ltikey;
    private $keytype; // Do we care about this? Not sure yet.
    private $ltilookup;
    private $userRole;
    private $gradePassbackUrl;

    /*
     * OHM data
     */
    private $ohmCourseGroupId;
    private $ohmUserId;

    public function __construct(array $request)
    {
        $this->request = $request;
        $this->userid = $request['user_id'];
        $this->ltikey = $request['oauth_consumer_key'];
        $this->org = $this->formatOrgFromRequest(); // This needs to happen after $ltikey is set.
        $this->userRole = $this->assignRoleFromRequest();
        $this->gradePassbackUrl = $request['lis_outcome_service_url'];
    }

    public function debugOutput()
    {
        $classVars = get_object_vars($this);
        // remove sensitive info
        unset($classVars['request']);
        unset($classVars['oauthRequestInfo']);

        echo '<pre>';
        print_r($classVars);
        echo '</pre>';
    }

    /**
     * Authenticate LTI credentials.
     *
     * @return bool True on success.
     * @throws \Exception Thrown on authentication failure.
     */
    public function authenticate(): bool
    {
        $store = new IMathASLTIOAuthDataStore();
        $server = new OAuthServer($store);
        $method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);
        $request = OAuthRequest::from_request(); // Sadface: uses apache_request_headers() :(
        $base = $request->get_signature_base_string();
        $this->oauthRequestInfo = $server->verify_request($request);
        $store->mark_nonce_used($request);

        // Extract OHM-specific values. (ltioauthstore.php grabs these for us)
        $this->ohmCourseGroupId = $this->oauthRequestInfo[0]->groupid;

        return true;
    }

    /**
     * Determine if the request has all the LTI data we need to launch.
     *
     * @return array<string> Error messages. Empty array if no errors.
     */
    public function hasValidLtiData(): array
    {
        $errors = [];

        if (empty($_REQUEST['lti_version'])) {
            $errors[] = "Missing LTI request data: lti_version"
                . " -- This might indicate your browser is set to restrict third-party cookies."
                . " Check your browser settings and try again";
        }
        if (empty($_REQUEST['user_id'])) {
            $errors[] = "Missing LTI request data: user_id -- user information was not provided";
        }
        if (empty($_REQUEST['context_id'])) {
            $errors[] = "Missing LTI request data: context_id -- course information was not provided";
        }
        if (empty($_REQUEST['roles'])) {
            $errors[] = "Missing LTI request data: roles";
        }
        if (empty($_REQUEST['oauth_consumer_key'])) {
            $errors[] = "Missing LTI request data: oauth_consumer_key -- resource key was not provided";
        }

        return $errors;
    }

    /**
     * Look up the OHM user associated with this LTI launch.
     *
     * @return bool True on success.
     * @throws \Exception Thrown if OHM user is not found.
     */
    public function assignOhmUserFromLaunch(): bool
    {
        global $DBH;

        $orgparts = explode(':', $this->org);  //THIS was added to avoid issues when LMS GUID change, while still storing it
        $shortorg = $orgparts[0];       //we'll only use the part from the lti key

        $query = "SELECT lti.userid,iu.FirstName,iu.LastName,iu.email,lti.id
            FROM imas_ltiusers AS lti
                LEFT JOIN imas_users as iu ON lti.userid=iu.id
            WHERE lti.org LIKE :org AND lti.ltiuserid=:ltiuserid ";
        if ($this->userRole != 'learner') {
            //if they're a teacher, make sure their imathas account is too. If not, we'll act like we don't know them
            //and require a new connection
            $query .= "AND iu.rights>19 ";
        }
        //if multiple accounts, use student one first (if not $ltirole of teacher) then higher rights.
        //if there was a mixup and multiple records were created, use the first one
        $query .= "ORDER BY iu.rights, lti.id";
        $stm = $DBH->prepare($query);
        $stm->execute(array(':org' => "$shortorg:%", ':ltiuserid' => $this->userid));

        if ($stm->rowCount() > 0) { //yup, we know them
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $this->ohmUserId = $row['userid'];
        } else {
            throw new \Exception("OHM user not found.");
        }

        return true;
    }

    /**
     * Get the org from LTI launch data and format it in a way MOM expects.
     *
     * @return string An org value as found in the imas_ltiusers table.
     */
    protected function formatOrgFromRequest(): string
    {
        if (empty($this->request['tool_consumer_instance_guid'])) {
            $ltiorg = 'Unknown';
        } else {
            $ltiorg = $this->request['tool_consumer_instance_guid'];
        }

        // prepend ltiorg with courseid or sso+userid to prevent cross-instructor hacking
        $keyparts = explode('_', $this->ltikey);
        if ($keyparts[0] == 'LTIkey') {  //cid:org
            $this->ltilookup = 'c';
            $ltiorg = $keyparts[1] . ':' . $ltiorg;
            $this->keytype = 'gc';
        } else {
            $this->ltilookup = 'u';
            $ltiorg = $this->ltikey . ':' . $ltiorg;
            $this->keytype = 'g';
        }

        return $ltiorg;
    }

    /**
     * Determine the user's role from LTI launch data.
     *
     * @return string 'instructor' or 'learner'.
     */
    protected function assignRoleFromRequest(): string
    {
        $ltiroles = new LTIRoles($this->request['roles']);
        if ($ltiroles->isInstructorForOurPurposes()) {
            return 'instructor';
        } else {
            return 'learner';
        }
    }
}
