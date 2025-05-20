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
class CalculatedAnswerBox implements AnswerBox, AnswerBoxOhmExtensions
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
        $correctAnswerWrongFormat = $this->answerBoxParams->getCorrectAnswerWrongFormat();
        $isConditional = $this->answerBoxParams->getIsConditional();

        $out = '';
        $tip = '';
        $sa = '';
        $preview = '';
        $params = [];

        $optionkeys = ['ansprompt', 'answerboxsize', 'hidepreview', 'answerformat',
            'answer', 'reqdecimals', 'reqsigfigs', 'displayformat', 'readerlabel'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum);
        }

        if (empty($answerboxsize)) {$answerboxsize = 20;}
        if ($multi) {$qn = ($qn + 1) * 1000 + $partnum;}

        if (!empty($correctAnswerWrongFormat)) {
            $rightanswrongformat = true;
            if ($colorbox == 'ansred') {
                $colorbox = 'ansorg';
            }
        }

        $ansformats = array_map('trim', explode(',', $answerformat));
        $isListAnswer =  (in_array('list', $ansformats) || in_array('exactlist', $ansformats) || in_array('orderedlist', $ansformats));

        if (in_array('allowplusminus', $ansformats)) {
            if (!$isListAnswer) {
                $ansformats[] = 'list';
                $answerformat = ($answerformat == '') ? 'list' : $answerformat . ',list';
                $isListAnswer = true;
            }
        } else if (isset($GLOBALS['myrights']) && $GLOBALS['myrights'] > 10 && is_string($answer) && strpos($answer,'+-')!==false) {
            echo _('Warning: For +- in an $answer to score correctly, use $answerformat="allowplusminus"');
        }

        if (!empty($ansprompt) && !in_array('nosoln', $ansformats) && !in_array('nosolninf', $ansformats)) {
            $out .= $ansprompt;
        }
        if ($displayformat == "point") {
            $leftb = "(";
            $rightb = ")";
        } else if ($displayformat == "vector") {
            $leftb = "&lt;";
            $rightb = "&gt;";
        } else {
            $leftb = '';
            $rightb = '';
        }

        if ($isListAnswer) {
            $tip = _('Enter your answer as a list of values separated by commas: Example: -4, 3, 2') . "<br/>";
            $eword = _('each value');
        } else if (in_array('set', $ansformats) || in_array('exactset', $ansformats)) {
            $tip = _('Enter your answer as a set of values separated with commas: Example: {-4, 3, 2}') . "<br/>";
            $eword = _('each value');
        } else {
            $tip = '';
            $eword = _('your answer');
        }
        if ($reqdecimals !== '') {
            list($reqdecimals, $exactreqdec, $reqdecoffset, $reqdecscoretype) = parsereqsigfigs($reqdecimals);
        }
        list($longtip, $shorttip) = formathint($eword, $ansformats, ($reqdecimals !== '') ? $reqdecimals : null, 'calculated', ($isListAnswer || in_array('set', $ansformats) || in_array('exactset', $ansformats)), 1);
        $tip .= $longtip;
        if ($reqsigfigs !== '' && !in_array("scinot", $ansformats) && !in_array("scinotordec", $ansformats) && !in_array("decimal", $ansformats)) {
            $reqsigfigs = '';
        }
        if ($reqsigfigs !== '') {
            list($reqsigfigs, $exactsigfig, $reqsigfigoffset, $sigfigscoretype) = parsereqsigfigs($reqsigfigs);

            if ($exactsigfig) {
                if ($isListAnswer) {
                    $answer = implode(',', prettysigfig(explode(',', $answer), $reqsigfigs, '', false, in_array("scinot", $ansformats) || in_array("scinotordec", $ansformats)));
                } else {
                    $answer = prettysigfig($answer, $reqsigfigs, '', false, in_array("scinot", $ansformats) || in_array("scinotordec", $ansformats));
                }
                $tip .= "<br/>" . sprintf(_('Your answer should have exactly %d significant figures.'), $reqsigfigs);
                $shorttip .= sprintf(_(', with exactly %d significant figures'), $reqsigfigs);
            } else if ($reqsigfigoffset > 0) {
                $tip .= "<br/>" . sprintf(_('Your answer should have between %d and %d significant figures.'), $reqsigfigs, $reqsigfigs + $reqsigfigoffset);
                $shorttip .= sprintf(_(', with %d - %d significant figures'), $reqsigfigs, $reqsigfigs + $reqsigfigoffset);
            } else {
                if (is_numeric($answer) && $answer != 0) {
                    $v = -1 * floor(-log10(abs($answer)) - 1e-12) - $reqsigfigs;
                }
                if (is_numeric($answer) && $answer != 0 && $v < 0 && strlen($answer) - strpos($answer, '.') - 1 + $v < 0) {
                    if ($isListAnswer) {
                        $answer = implode(',', prettysigfig(explode(',', $answer), $reqsigfigs, '', false, in_array("scinot", $ansformats) || in_array("scinotordec", $ansformats)));
                    } else {
                        $answer = prettysigfig($answer, $reqsigfigs, '', false, in_array("scinot", $ansformats) || in_array("scinotordec", $ansformats));
                    }
                }
                $tip .= "<br/>" . sprintf(_('Your answer should have at least %d significant figures.'), $reqsigfigs);
                $shorttip .= sprintf(_(', with at least %d significant figures'), $reqsigfigs);
            }
        }

        $classes = ['text'];
        if ($colorbox != '') {
            $classes[] = $colorbox;
        }
        $attributes = [
            'type' => 'text',
            'size' => $answerboxsize,
            'name' => "qn$qn",
            'id' => "qn$qn",
            'value' => $la,
            'autocomplete' => 'off',
            'aria-label' => $this->answerBoxParams->getQuestionIdentifierString() .
            (!empty($readerlabel) ? ' ' . Sanitize::encodeStringForDisplay($readerlabel) : ''),
        ];

        $params['tip'] = $shorttip;
        $params['longtip'] = $tip;
        if ($useeqnhelper) {
            $params['helper'] = 1;
        }
        if (empty($hidepreview)) {
            $params['preview'] = !empty($_SESSION['userprefs']['livepreview']) ? 1 : 2;
        }
        $params['calcformat'] = $answerformat;

        $out .= $leftb .
        '<input ' .
        Sanitize::generateAttributeString($attributes) .
        'class="' . implode(' ', $classes) .
            '" />' .
            $rightb;

        $plabel = $this->answerBoxParams->getQuestionIdentifierString() . ' ' . _('Preview');
        if (empty($hidepreview)) {
            $preview .= '<button type=button class=btn id="pbtn' . $qn . '">';
            $preview .= _('Preview') . ' <span class="sr-only">' . $this->answerBoxParams->getQuestionIdentifierString() . '</span>';
            $preview .= '</button> &nbsp;';
        }
        $preview .= "$leftb<span id=p$qn></span>$rightb ";

        $nosolntype = 0;
        if (in_array('nosoln', $ansformats) || in_array('nosolninf', $ansformats)) {
            list($out, $answer, $nosolntype) = setupnosolninf($qn, $out, $answer, $ansformats, $la, $ansprompt, $colorbox);
        }

        if ($answer !== '' && !is_array($answer) && !$isConditional) {
            if ($nosolntype > 0) {
                $sa = $answer;
            } else if (!is_numeric($answer)) {
                //$sa = '`' . $answer . '`';
                if (in_array('allowplusminus', $ansformats)) {
                    $answer = str_replace('+-','pm',$answer);
                }
                $sa = makeprettydisp($answer);
            } else if (in_array('mixednumber', $ansformats) || in_array("sloppymixednumber", $ansformats) || in_array("mixednumberorimproper", $ansformats)) {
                $sa = '`' . decimaltofraction($answer, "mixednumber") . '`';
            } else if (in_array("fraction", $ansformats) || in_array("reducedfraction", $ansformats)) {
                $sa = '`' . decimaltofraction($answer) . '`';
            } else if (in_array("scinot", $ansformats) || (in_array("scinotordec", $ansformats) && (abs($answer) > 1000 || abs($answer) < .001))) {
                $sa = '`' . makescinot($answer, -1, '*') . '`';
            } else if (is_numeric($answer) && $answer != 0 && abs($answer) < .001 && abs($answer) > 1e-9) {
                $sa = prettysmallnumber($answer);
            } else {
                $sa = $answer;
            }
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
        $optionVariablesAndValues['partType'] = 'calculated';
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
