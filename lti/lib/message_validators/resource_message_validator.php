<?php
namespace IMSGlobal\LTI;

class Resource_Message_Validator implements Message_Validator {
    public function can_validate($jwt_body) {
        return $jwt_body['https://purl.imsglobal.org/spec/lti/claim/message_type'] === 'LtiResourceLinkRequest';
    }

    public function validate($jwt_body) {
        #### Begin OHM-specific code #####################################################################
        #### Begin OHM-specific code #####################################################################
        #### Begin OHM-specific code #####################################################################
        #### Begin OHM-specific code #####################################################################
        #### Begin OHM-specific code #####################################################################
        if ($GLOBALS['ENABLE_LTI_DEBUG']) {
            $logData = [
                'message' => 'Validating LTI resource message.',
                'classMethod' => __METHOD__,
                'jwtBody' => $jwt_body,
            ];
            error_log(json_encode($logData));
        }
        #### End OHM-specific code #####################################################################
        #### End OHM-specific code #####################################################################
        #### End OHM-specific code #####################################################################
        #### End OHM-specific code #####################################################################
        #### End OHM-specific code #####################################################################
        if (empty($jwt_body['sub'])) {
            throw new LTI_Exception('Must have a user (sub)');
        }
        if (empty($jwt_body['https://purl.imsglobal.org/spec/lti/claim/context'])) {
            throw new LTI_Exception('Must have a context');
        }
        if ($jwt_body['https://purl.imsglobal.org/spec/lti/claim/version'] !== '1.3.0') {
            throw new LTI_Exception('Incorrect version, expected 1.3.0');
        }
        if (!isset($jwt_body['https://purl.imsglobal.org/spec/lti/claim/roles'])) {
            throw new LTI_Exception('Missing Roles Claim');
        }
        if (empty($jwt_body['https://purl.imsglobal.org/spec/lti/claim/resource_link']['id'])) {
            throw new LTI_Exception('Missing Resource Link Id');
        }

        return true;
    }
}
?>
