<?php
namespace OHM\Controlers;
require(__DIR__ . '/../../includes/ltioutcomes.php');
use \PDO;
class LTI
{
    public static function requestLMSGrade(string $sourcedid)
    {
        global $DBH;
        list($lti_sourcedid, $ltiurl, $ltikey, $keytype) = explode(':|:', $sourcedid);
        $secret = '';
        if (strlen($lti_sourcedid) > 1 && strlen($ltiurl) > 1 && strlen($ltikey) > 1) {
            if ($keytype == 'c') {
                $keyparts = explode('_', $ltikey);
                $stm = $DBH->prepare("SELECT ltisecret FROM imas_courses WHERE id=:id");
                $stm->execute(array(':id' => $keyparts[1]));
                if ($stm->rowCount() > 0) {
                    $secret = $stm->fetchColumn(0);
                }
            } else {
                $stm = $DBH->prepare(
                    "SELECT password FROM imas_users "
                    . "WHERE SID=:SID AND (rights=11 OR rights=76 OR rights=77)"
                );
                $stm->execute(array(':SID' => $ltikey));
                if ($stm->rowCount() > 0) {
                    $secret = $stm->fetchColumn(0);
                }
            }
        }
        if ($secret != '') {
            $value = self::sendLTIOutcome(
                'read', $ltikey, $secret, $ltiurl, $lti_sourcedid, 0, true
            );
            if (isset($value[1])) {
                $grade = preg_replace('/.*textString.*([\d\.]*).*textString.*/', '$1', $value[1]);
                if (!empty($grade)) {
                    return $grade;
                } else {
                    return $value[1];
                }
            } else {
                return "unable to read LTI grade";
            }
        } else {
            return "Unable to lookup secret";
        }
    }
    public static function reCalcandupdateLTIgrade(int $aid, $scores, $ver)
    {
        global $DBH;
        $stm = $DBH->prepare("SELECT ptsposs,itemorder,defpoints FROM imas_assessments WHERE id=:id");
        $stm->execute(array(':id' => $aid));
        $line = $stm->fetch(PDO::FETCH_ASSOC);
        if ($line['ptsposs'] == -1) {
            $line['ptsposs'] = Assessments::updatePointsPossible($aid, $line['itemorder'], $line['defpoints']);
        }
        $aidposs = $line['ptsposs'];
        $allans = true;
        if (1 == $ver) {
            // old assesses
            // Calculation reference: /course/isolateassessgrade.php, line ~283 (as of 2020 Feb 05)
            $sp = explode(';', $scores);
            $scores = explode(',', $sp[0]);
            $total = 0;
            for ($i=0;$i<count($scores);$i++) {
                $total += Assessments::getpts($scores[$i]);
            }
        } else {
            // new assesses
            $total = Assessments::getpts($scores);
        }
        $grade = min(1, max(0, $total / $aidposs));
        $grade = number_format($grade, 8);
        return $grade;
    }
    public static function sendLTIOutcome(
        $action,$key,$secret,$url,$sourcedid,$grade=0,$checkResponse=false
    ) {

        $method="POST";
        $content_type = "application/xml";

        $body = '<?xml version = "1.0" encoding = "UTF-8"?>
	<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
		<imsx_POXHeader>
			<imsx_POXRequestHeaderInfo>
				<imsx_version>V1.0</imsx_version>
				<imsx_messageIdentifier>MESSAGE</imsx_messageIdentifier>
			</imsx_POXRequestHeaderInfo>
		</imsx_POXHeader>
		<imsx_POXBody>
			<OPERATION>
				<resultRecord>
					<sourcedGUID>
						<sourcedId>SOURCEDID</sourcedId>
					</sourcedGUID>
					<result>
						<resultScore>
							<language>en-us</language>
							<textString>GRADE</textString>
						</resultScore>
					</result>
				</resultRecord>
			</OPERATION>
		</imsx_POXBody>
	</imsx_POXEnvelopeRequest>';

        $shortBody = '<?xml version = "1.0" encoding = "UTF-8"?>
	<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
		<imsx_POXHeader>
			<imsx_POXRequestHeaderInfo>
				<imsx_version>V1.0</imsx_version>
				<imsx_messageIdentifier>MESSAGE</imsx_messageIdentifier>
			</imsx_POXRequestHeaderInfo>
		</imsx_POXHeader>
		<imsx_POXBody>
			<OPERATION>
				<resultRecord>
					<sourcedGUID>
						<sourcedId>SOURCEDID</sourcedId>
					</sourcedGUID>
				</resultRecord>
			</OPERATION>
		</imsx_POXBody>
	</imsx_POXEnvelopeRequest>';

        if ($action=='update') {
            $operation = 'replaceResultRequest';
            $postBody = str_replace(
                array('SOURCEDID', 'GRADE', 'OPERATION','MESSAGE'),
                array($sourcedid, $grade, $operation, uniqid()),
                $body
            );
        } else if ($action=='read') {
            $operation = 'readResultRequest';
            $postBody = str_replace(
                array('SOURCEDID', 'OPERATION','MESSAGE'),
                array($sourcedid, $operation, uniqid()),
                $shortBody
            );
        } else if ($action=='delete') {
            $operation = 'deleteResultRequest';
            $postBody = str_replace(
                array('SOURCEDID', 'OPERATION','MESSAGE'),
                array($sourcedid, $operation, uniqid()),
                $shortBody
            );
        } else {
            return false;
        }

        $response = sendOAuthBodyPOST($method, $url, $key, $secret, $content_type, $postBody, $checkResponse);
        return $response;
    }
    public static function addToLTIQueue($sourcedid, $grade, $sendnow=false, $userid, $assessmentid)
    {
        global $DBH, $CFG;

        $LTIdelay = 60*(isset($CFG['LTI']['queuedelay'])?$CFG['LTI']['queuedelay']:5);
        $keyseturl = $GLOBALS['basesiteurl'] . '/lti/jwks.php';

        $sourcedidHash = md5($sourcedid);
        $sendon = (time() + ($sendnow?0:$LTIdelay));

        $debugData = [
            'hash' => $sourcedidHash,
            'userid' => $userid,
            'assessmentid' => $assessmentid,
            'grade' => $grade,
            'sendon' => $sendon,
            'sourcedid' => $sourcedid,
        ];
        error_log('Adding to imas_ltiqueue: ' . json_encode($debugData));

        $query = 'INSERT INTO imas_ltiqueue (hash, sourcedid, grade, failures, sendon, userid, assessmentid, keyseturl) ';
        $query .= 'VALUES (:hash, :sourcedid, :grade, 0, :sendon, :userid, :assessmentid, :keyseturl) ON DUPLICATE KEY UPDATE ';
        $query .= 'grade=VALUES(grade),sendon=VALUES(sendon),failures=0,keyseturl=VALUES(keyseturl) ';

        $stm = $DBH->prepare($query);
        $stm->execute(
            array(
                ':hash' => $sourcedidHash,
                ':sourcedid' => $sourcedid,
                ':grade' => $grade,
                ':sendon' => $sendon,
                ':userid' => $userid,
                ':assessmentid' => $assessmentid,
                ':keyseturl' => $keyseturl,
            )
        );

        return ($stm->rowCount()>0);
    }
}
