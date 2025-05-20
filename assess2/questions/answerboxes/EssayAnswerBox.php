<?php

namespace IMathAS\assess2\questions\answerboxes;

require_once __DIR__ . '/AnswerBox.php';

use Sanitize;

#### Begin OHM-specific changes ############################################################
#### Begin OHM-specific changes ############################################################
#### Begin OHM-specific changes ############################################################
#### Begin OHM-specific changes ############################################################
#### Begin OHM-specific changes ############################################################
require_once __DIR__ . '/AnswerBoxOhmExtensions.php';
require_once __DIR__ . '/../../../ohm/services/AnswerBoxOhmUtilService.php';

use OHM\Services\AnswerBoxOhmUtilService;
#
# The OHM-specific changes:
# - Added "AnswerBoxOhmExtensions" to the list of implemented interfaces.
#
class EssayAnswerBox implements AnswerBox, AnswerBoxOhmExtensions
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
{
    private $answerBoxParams;

    private $answerBox;
    private $jsParams;
    private $entryTip;
    private $correctAnswerForPart;
    private $previewLocation;

    #### Begin OHM-specific changes ############################################################
    #### Begin OHM-specific changes ############################################################
    #### Begin OHM-specific changes ############################################################
    #### Begin OHM-specific changes ############################################################
    #### Begin OHM-specific changes ############################################################
    /* @var Array<String> */
    private array $questionOptionVariables; // A list of option variable names. Most of these will appear in question code.
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    public function __construct(AnswerBoxParams $answerBoxParams)
    {
        $this->answerBoxParams = $answerBoxParams;
    }

    public function generate(): void
    {
        global $RND, $myrights, $useeqnhelper, $showtips, $imasroot;

        $anstype = $this->answerBoxParams->getAnswerType();
        $qn = $this->answerBoxParams->getQuestionNumber();
        $multi = $this->answerBoxParams->getIsMultiPartQuestion();
        $partnum = $this->answerBoxParams->getQuestionPartNumber();
        $la = $this->answerBoxParams->getStudentLastAnswers();
        $options = $this->answerBoxParams->getQuestionWriterVars();
        $colorbox = $this->answerBoxParams->getColorboxKeyword();
        $isConditional = $this->answerBoxParams->getIsConditional();

        $out = '';
        $tip = '';
        $sa = '';
        $preview = '';
        $params = [];

        $optionkeys = ['answerboxsize', 'displayformat', 'scoremethod',
            'answer', 'readerlabel'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum);
        }

        if ($multi) {$qn = ($qn + 1) * 1000 + $partnum;}

        if (empty($answerboxsize)) {
            $rows = 5;
            $cols = 50;
        } else if (strpos($answerboxsize, ',') > 0) {
            list($rows, $cols) = explode(',', $answerboxsize);
            $rows = intval($rows);
            $cols = intval($cols);
        } else {
            $cols = 50;
            $rows = intval($answerboxsize);
        }
        $nopaste = false;
        if ($displayformat == 'editornopaste') {
            $nopaste = true;
            $displayformat = 'editor';
        }
        if ($displayformat == 'editor') {
            $rows += 5;
        }

        if (!isset($GLOBALS['useeditor'])) { // should be defined, but avoid errors if not
            $GLOBALS['useeditor'] = 1;
        }

        if ($GLOBALS['useeditor'] == 'review' || ($GLOBALS['useeditor'] == 'reviewifneeded' && trim($la) == '')) {
            $la = str_replace('&quot;', '"', $la);

            if ($displayformat == 'pre') {
                $la = str_replace(['<','>'],['&lt;','&gt;'], $la);
            } else if ($displayformat != 'editor') {
                $la = preg_replace('/\n/', '<br/>', $la);
            }
            if ($colorbox == '') {
                $out .= '<div class="introtext" id="qnwrap' . $qn . '">';
            } else {
                $out .= '<div class="introtext ' . $colorbox . '" id="qnwrap' . $qn . '">';
            }
            if ($displayformat == 'pre') {
                $out .= '<pre>';
                $out .= $la;
                $out .= '</pre>';
            } else {
                $out .= filter($la);
            }
            
            $out .= "</div>";
        } else {
            $arialabel = $this->answerBoxParams->getQuestionIdentifierString() .
                (!empty($readerlabel) ? ' ' . Sanitize::encodeStringForDisplay($readerlabel) : '');
            if ($displayformat == 'editor' && $GLOBALS['useeditor'] == 1) {
                $la = str_replace('&quot;', '"', $la);
            }
            if ($rows < 2) {
                $out .= "<input type=\"text\" class=\"text $colorbox\" size=\"$cols\" name=\"qn$qn\" id=\"qn$qn\" value=\"" . Sanitize::encodeStringForDisplay($la) . "\" ";
                $out .= 'aria-label="' . $arialabel . '" />';
            } else {
                if ($colorbox != '') {$out .= '<div class="' . $colorbox . '">';}
                $out .= "<textarea rows=\"$rows\" name=\"qn$qn\" id=\"qn$qn\" ";
                if ($displayformat == 'editor' && $GLOBALS['useeditor'] == 1) {
                    $out .= "style=\"width:98%;\" class=\"mceEditor\" ";
                } else {
                    $out .= "cols=\"$cols\" ";
                }
                $out .= 'aria-label="' . $arialabel . '" ';
                $out .= sprintf(">%s</textarea>\n", Sanitize::encodeStringForDisplay($la, true));
                if ($colorbox != '') {$out .= '</div>';}
            }
            if ($displayformat == 'editor' && $GLOBALS['useeditor'] == 1) {
                $params['usetinymce'] = 1;
                if ($nopaste) {
                    $params['nopaste'] = 1;
                }
            }
        }
        $tip .= _('Enter your answer as text.  This question is not automatically graded.');
        if (is_scalar($answer) && !$isConditional) {
            $sa .= $answer;
        }

        if ($scoremethod == 'takeanythingorblank' && trim($la) == '') {
            $params['submitblank'] = 1;
        }

        // Done!
        $this->answerBox = $out;
        $this->jsParams = $params;
        $this->entryTip = $tip;
        $this->correctAnswerForPart = (string) $sa;
        $this->previewLocation = $preview;
        #### Start OHM-specific changes ############################################################
        #### Start OHM-specific changes ############################################################
        #### Start OHM-specific changes ############################################################
        #### Start OHM-specific changes ############################################################
        #### Start OHM-specific changes ############################################################

        /*
         * This gathers question variables, some created by question writers and some not, so we
         * can pass them to AnswerBoxOhmUtilService in order to generate structured data for OHM's
         * question API responses containing question components. The question API will use
         * Question->getExtraData() to retrieve this information.
         */

        // Collect all the values we're interested in so we can pass them to AnswerBoxOhmUtilService.
        $optionVariablesAndValues = [];
        $optionVariablesAndValues['partType'] = 'essay';
        foreach ($optionkeys as $optionVariableName) {
            $optionVariablesAndValues[$optionVariableName] = ${$optionVariableName};
        }

        $answerBoxOhmUtilService = new AnswerBoxOhmUtilService();
        $this->questionOptionVariables = $answerBoxOhmUtilService
            ->formatAndReturnQuestionVariables($optionVariablesAndValues, [], $this->answerBoxParams);

        #### End OHM-specific changes ############################################################
        #### End OHM-specific changes ############################################################
        #### End OHM-specific changes ############################################################
        #### End OHM-specific changes ############################################################
        #### End OHM-specific changes ############################################################
    }

    public function getAnswerBox(): string
    {
        return $this->answerBox;
    }

    public function getJsParams(): array
    {
        return $this->jsParams;
    }

    public function getEntryTip(): string
    {
        return $this->entryTip;
    }

    public function getCorrectAnswerForPart(): string
    {
        return $this->correctAnswerForPart;
    }

    public function getPreviewLocation(): string
    {
        return $this->previewLocation;
    }
    #### Start OHM-specific changes ############################################################
    #### Start OHM-specific changes ############################################################
    #### Start OHM-specific changes ############################################################
    #### Start OHM-specific changes ############################################################
    #### Start OHM-specific changes ############################################################
    /**
     * Get an associative array of question option variable names that may appear in this
     * question's code and their values.
     *
     * Some variables, such as "randkeys", do not appear in question code but will also be
     * returned.
     *
     * The return value of this method is intended to be used by OHM's question API (using
     * the Laravel/Lumen framework) to return all components of a question.
     *
     * @return mixed[] An associative array of question option variable names and their values.
     */
    public function getQuestionOptionVariables(): array
    {
        return $this->questionOptionVariables;
    }
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
    #### End OHM-specific changes ############################################################
}
