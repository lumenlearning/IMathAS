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
class MatchingAnswerBox implements AnswerBox, AnswerBoxOhmExtensions
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
#### End OHM-specific changes ############################################################
{
    private $answerBoxParams;

    private $answerBox;
    private $jsParams = [];
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
        $isConditional = $this->answerBoxParams->getIsConditional();
        $partnum = $this->answerBoxParams->getQuestionPartNumber();
        $la = $this->answerBoxParams->getStudentLastAnswers();
        $options = $this->answerBoxParams->getQuestionWriterVars();
        $colorbox = $this->answerBoxParams->getColorboxKeyword();
        $assessmentId = $this->answerBoxParams->getAssessmentId();

        // FIXME: The following code needs to be updated
        //        - $qn is always the question number (never $qn+1)
        //        - $multi is now a boolean
        //        - $partnum is now available

        $out = '';
        $tip = '';
        $sa = '';
        $preview = '';

        $optionkeys = ['questiontitle', 'answertitle', 'matchlist', 'noshuffle',
            'displayformat', 'readerlabel', 'ansprompt'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum);
        }
        $questions = getOptionVal($options, 'questions', $multi, $partnum, 2);
        $answers = getOptionVal($options, 'answers', $multi, $partnum, 2);

        if ($multi) {$qn = ($qn + 1) * 1000 + $partnum;}

        if (!is_array($questions)) {
            echo _('Eeek!  $questions is not defined or needs to be an array');
            $questions = array();
        }
        if (!is_array($answers)) {
            echo _('Eeek!  $answers is not defined or needs to be an array');
            $answers = array();
        }
        if (!empty($matchlist)) {
            $matchlist = array_map('trim', explode(',', $matchlist));
            if (count($matchlist) != count($questions)) {
                echo _('$questions and $matchlist should have the same number of entries');
            }
        }
        if ($noshuffle == "questions" || $noshuffle == 'all') {
            $randqkeys = array_keys($questions);
        } else {
            $randqkeys = (array) $RND->array_rand($questions, count($questions));
            $RND->shuffle($randqkeys);
        }
        if ($noshuffle == "answers" || $noshuffle == 'all') {
            $randakeys = array_keys($answers);
        } else {
            $randakeys = (array) $RND->array_rand($answers, count($answers));
            $RND->shuffle($randakeys);
        }
        $_SESSION['choicemap'][$assessmentId][$qn] = array($randqkeys, $randakeys);
        if (!empty($GLOBALS['inline_choicemap'])) {
            $params['choicemap'] = encryptval(array($randqkeys, $randakeys), $GLOBALS['inline_choicemap']);
        }
        if (isset($GLOBALS['capturechoices'])) {
            $GLOBALS['choicesdata'][$qn] = array($randqkeys, $answers);
        }
        if (isset($GLOBALS['capturechoiceslivepoll'])) {
            /* TODO
        $params['livepoll_choices'] = $questions;
        $params['livepoll_ans'] = $answer;
        $params['livepoll_randkeys'] = $randakeys;
         */
        }

        $ncol = 1;

        if (substr($displayformat, 1) == 'columnselect') {
            $ncol = $displayformat[0];
            $itempercol = ceil(count($randqkeys) / $ncol);
            $displayformat = 'select';
        } else if (substr($displayformat, 1) == 'columnstacked') {
            $ncol = $displayformat[0];
            $itempercol = ceil(count($randqkeys) / $ncol);
            $itemperanscol = ceil(count($randakeys) / $ncol);
        }
        if (substr($displayformat, 0, 8) == "limwidth") {
            $divstyle = 'style="max-width:' . substr($displayformat, 8) . 'px;"';
        } else {
            $divstyle = '';
        }
        $out = '<div id="qnwrap' . $qn . '" role="group" ';
        if ($colorbox != '') {
            $out .= 'class="' . $colorbox . '" ';
        }
        $out .= 'aria-label="' . $this->answerBoxParams->getQuestionIdentifierString() .
            (!empty($readerlabel) ? ' ' . Sanitize::encodeStringForDisplay($readerlabel) : '') . '">';
        $out .= "<div class=\"match\" $divstyle>\n";
        if (!empty($questiontitle)) {
            $out .= "<p class=\"centered\">$questiontitle</p>\n";
        }
        $out .= "<ul class=\"nomark\">\n";
        if ($la == '' || is_array($la)) { // no reason for $la to be array, but catch case
            $las = array();
        } else {
            $las = explode("|", $la);
        }

        $letters = array_slice(explode(',', 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,aa,ab,ac,ad,ae,af,ag,ah,ai,aj,ak,al,am,an,ao,ap,aq,ar,as,at,au,av,aw,ax,ay,az'), 0, count($answers));

        for ($i = 0; $i < count($randqkeys); $i++) {
            if (isset($las[$randqkeys[$i]])) {
                $laval = $las[$randqkeys[$i]];
            } else {
                $laval = '-';
            }
            if ($ncol > 1) {
                if ($i > 0 && $i % $itempercol == 0) {
                    $out .= '</ul></div><div class="match"><ul class=nomark>';
                }
            }
            if (strpos($questions[$randqkeys[$i]], ' ') === false || strlen($questions[$randqkeys[$i]]) < 12) {
                $out .= '<li class="nowrap">';
            } else {
                $out .= '<li>';
            }
            $out .= "<select name=\"qn$qn-$i\" id=\"qn$qn-$i\">";
            $out .= '<option value="-" ';
            if ($laval == '-' || strcmp($laval, '') == 0) {
                $out .= 'selected="1"';
            }
            $out .= '>' . ($ansprompt !== '' ? $ansprompt : '-') . '</option>';
            if ($displayformat == "select") {
                for ($j = 0; $j < count($randakeys); $j++) {
                    $out .= "<option value=\"" . $j . "\" ";
                    if (strcmp($laval, $randakeys[$j]) == 0) {
                        $out .= 'selected="1"';
                    }
                    $out .= ">" . str_replace('`', '', $answers[$randakeys[$j]]) . "</option>\n";
                }
            } else {
                foreach ($letters as $j => $v) {
                    //$out .= "<option value=\"$v\" ";
                    $out .= "<option value=\"$j\" ";
                    if (strcmp($laval, $randakeys[$j]) == 0) {
                        $out .= 'selected="1"';
                    }
                    $out .= ">$v</option>";
                }
            }
            $out .= "</select>&nbsp;<label for=\"qn$qn-$i\">{$questions[$randqkeys[$i]]}</label></li>\n";
        }
        $out .= "</ul>\n";
        $out .= "</div>";

        if (empty($displayformat) || $displayformat != "select") {
            if (!empty($itemperanscol)) {
                $out .= "<div class=spacer>&nbsp;</div>";
            }
            $out .= "<div class=\"match\" $divstyle>\n";
            if (!empty($answertitle)) {
                $out .= "<p class=centered>$answertitle</p>\n";
            }

            $out .= "<ol class=lalpha>\n";
            for ($i = 0; $i < count($randakeys); $i++) {
                if ($ncol > 1 && $i > 0 && $i % $itemperanscol == 0) {
                    $out .= '</ol></div><div class="match"><ol class=lalpha start=' . ($i + 1) . '>';
                }
                $out .= "<li>{$answers[$randakeys[$i]]}</li>\n";
            }
            $out .= "</ol>";
            $out .= "</div>";
        }
        $out .= "<div class=spacer>&nbsp;</div>";
        $out .= '</div>';
        //$tip = "In each box provided, type the letter (a, b, c, etc.) of the matching answer in the right-hand column";
        if ($displayformat == "select") {
            $tip = _('In each pull-down, select the item that matches with the displayed item');
        } else {
            $tip = _('In each pull-down on the left, select the letter (a, b, c, etc.) of the matching answer in the right-hand column');
        }
        if (!$isConditional) {
            for ($i = 0; $i < count($randqkeys); $i++) {
                if (!empty($matchlist)) {
                    $anss = array_map('trim', explode(' or ', $matchlist[$randqkeys[$i]]));
                    $ansopts = [];
                    foreach ($anss as $v) {
                        $akey = array_search($v, $randakeys);
                        if ($displayformat == "select") {
                            $ansopts[] = $answers[$randakeys[$akey]];
                        } else {
                            $ansopts[] = chr($akey + 97);
                        }
                    }
                    if ($displayformat == "select") {
                        $sa .= '<br/>' . implode(' or ', $ansopts);
                    } else if (count($ansopts) > 1) {
                        $sa .= '(' . implode(' or ', $ansopts) . ') ';
                    } else {
                        $sa .= $ansopts[0] . ' ';
                    }
                } else {
                    $akey = array_search($randqkeys[$i], $randakeys);
                    if ($displayformat == "select") {
                        $sa .= '<br/>' . $answers[$randakeys[$akey]];
                    } else {
                        $sa .= chr($akey + 97) . " ";
                    }
                }
            }
        }

        // Done!
        $this->answerBox = $out;
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

        // We're interested in a few more option variables, so let's merge their names with $optionkeys.
        $optionVariableNamesAndMore = array_merge(['questions', 'randakeys', 'randqkeys'], $optionkeys);

        // Collect all the values we're interested in so we can pass them to AnswerBoxOhmUtilService.
        $optionVariablesAndValues = [];
        $optionVariablesAndValues['partType'] = 'matching';
        foreach ($optionVariableNamesAndMore as $optionVariableName) {
            $optionVariablesAndValues[$optionVariableName] = ${$optionVariableName};
        }

        // Remap some variable names for the question API response.
        $variableNameRemap = [
            // Multiple answers are sometimes stored in $questions, but "choices"
            // makes more sense as a hash key here.
            'questions' => 'choices',
            'randakeys' => 'shuffledAnswerChoicesIndex',
            'randqkeys' => 'shuffledQuestionChoicesIndex',
        ];

        $answerBoxOhmUtilService = new AnswerBoxOhmUtilService();
        $this->questionOptionVariables = $answerBoxOhmUtilService
            ->formatAndReturnQuestionVariables($optionVariablesAndValues, $variableNameRemap, $this->answerBoxParams);

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
