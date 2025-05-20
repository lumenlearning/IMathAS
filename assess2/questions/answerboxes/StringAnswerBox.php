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
class StringAnswerBox implements AnswerBox, AnswerBoxOhmExtensions
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

       


        $optionkeys = ['ansprompt', 'answerboxsize', 'answer', 'strflags', 
            'displayformat', 'answerformat', 'scoremethod', 'readerlabel', 'variables'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum);
        }
        $questions = getOptionVal($options, 'questions', $multi, $partnum, 2);

        if ($multi) {$qn = ($qn + 1) * 1000 + $partnum;}

        if (empty($answerboxsize)) {$answerboxsize = 20;}
        if (!empty($ansprompt)) {$out .= $ansprompt;}

        $arialabel = $this->answerBoxParams->getQuestionIdentifierString() .
            (!empty($readerlabel) ? ' ' . Sanitize::encodeStringForDisplay($readerlabel) : '');

        if ($answerformat == 'list') {
            $tip = _('Enter your answer as a list of text separated by commas.  Example:  dog, cat, rabbit.') . "<br/>";
            $shorttip = _('Enter a list of text');
        } else if ($answerformat == 'matrix') {
            $shorttip = _('Enter your answer as a matrix');
            $tip = $shorttip . _(', like [(2,3,4),(1,4,5)]');
        } else if ($answerformat == 'logic') {
            $shorttip = _('Enter a logic statement');
            $tip = _('Enter a logic statement using the editor buttons, or use "and", "or", "xor", "neg", "implies", and "iff"');
        } else if ($answerformat == 'setexp') {
            $shorttip = _('Enter a set expression');
            $tip = _('Enter a set expression using the editor buttons, or use "and", "or", "ominus", and "-"');
        } else {
            $tip .= _('Enter your answer as letters.  Examples: A B C, linear, a cat');
            $shorttip = _('Enter text');
        }

        // If answerformat == setexp, then we need to distinguish the variable 'U' from the union symbol 'U' in $la so it displays correctly
        if ($answerformat == 'setexp'){
            $keywords = ['nn',	'and',	'cap',	'ominus',   'xor',	'oplus', 'uu',	'cup',	'or',	'-'];
	        $replace = 	['#a',	'#a',	'#a',	'#x',	    '#x',	'#x',    '#o',	'#o',	'#o',	'#m'];
            $la = str_replace($keywords,$replace,$la);
        	while(preg_match('/((^|[^#])[A-Za-z](\s|\(|\)|\[|\]|\^c|\')*)U/',$la)){
        		$la = preg_replace('/((^|[^#])[A-Za-z](\s|\(|\)|\[|\]|\^c|\')*)U/','$1 #o ',$la,1);
        	}
            $la = str_replace($replace,$keywords,$la);
        }

        if ($displayformat == 'select' && is_array($questions)) {
            $out .= "<select name=\"qn$qn\" id=\"qn$qn\" style=\"margin-right:20px\" class=\"$colorbox\" ";
            $out .= 'aria-label="' . $arialabel . '">';
            $out .= '<option value=""> </option>';
            foreach ($questions as $i => $v) {
                $out .= '<option value="' . htmlentities($v) . '"';
                if ($v == $la) {
                    $out .= ' selected="selected"';
                }
                $out .= '>' . htmlentities($v) . '</option>';
            }
            $out .= '</select>';
        } else {
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
                'aria-label' => $arialabel,
            ];

            if ($displayformat == 'alignright') {
                $classes[] = 'textright';
            } else if ($displayformat == 'hidden') {
                $classes[] = 'pseudohidden';
            } else if ($displayformat == 'debit') {
                $params['format'] = 'debit';
                $classes[] = 'textright';
            } else if ($displayformat == 'credit') {
                $params['format'] = 'credit';
                $classes[] = 'textright';
                $classes[] = 'creditbox';
            }

            $params['tip'] = $shorttip;
            $params['longtip'] = $tip;
            if ($useeqnhelper && ($displayformat == 'usepreview' || $answerformat == 'logic' || $answerformat == 'setexp')) {
                $params['helper'] = 1;
            }
            if (empty($hidepreview) && ($displayformat == 'usepreview' || $displayformat == 'usepreviewnomq')) {
                $params['preview'] = !empty($_SESSION['userprefs']['livepreview']) ? 1 : 2;
            }
            if ($answerformat == 'logic' || $answerformat == 'setexp') {
                $params['vars'] = $variables;
            }

            $params['calcformat'] = $answerformat;

            if ($displayformat == 'typeahead') {
                if (!is_array($questions)) {
                    echo _('Eeek!  $questions is not defined or needs to be an array');
                } else {
                    foreach ($questions as $i => $v) {
                        $questions[$i] = htmlentities(trim($v));
                    }

                    $autosugglist = '["' . implode('","', $questions) . '"]';
                    if (!isset($GLOBALS['autosuggestlists'])) {
                        $GLOBALS['autosuggestlists'] = array();
                    }
                    if (($k = array_search($autosugglist, $GLOBALS['autosuggestlists'])) !== false) {
                        $asvar = 'autosuggestlist' . $k;
                    } else {
                        $GLOBALS['autosuggestlists'][] = $autosugglist;
                        $ascnt = count($GLOBALS['autosuggestlists']) - 1;
                        $asvar = 'autosuggestlist' . $ascnt;

                        $params[$asvar] = $questions;
                    }
                    $params['autosuggest'] = $asvar;
                }
            }

            $out .= '<input ' .
            Sanitize::generateAttributeString($attributes) .
            'class="' . implode(' ', $classes) .
                '" />';

            if ($displayformat == 'usepreview' || $displayformat == 'usepreviewnomq') {
                $preview .= '<button type=button class=btn id="pbtn' . $qn . '">';
                $preview .= _('Preview') . ' <span class="sr-only">' . $this->answerBoxParams->getQuestionIdentifierString() . '</span>';
                $preview .= '</button> &nbsp;';
                $preview .= "<span id=p$qn></span> ";
            }
        }
        if (!$isConditional && !is_array($answer)) {
            if (strpos($strflags, 'regex') !== false) {
                $sa .= _('The answer must match a specified pattern');
            } else if ($answerformat == "logic") {
                $sa = '`' . str_replace(['and', 'xor', 'or', 'implies', 'iff'], ['^^', 'oplus', 'vv', '=>', '<=>'], $answer) . '`';
            } else if ($answerformat == "setexp") {
                $sa = '`' . str_replace(['and', 'cap', 'xor', 'oplus', 'ominus', 'or', 'cup'], ['nn', 'nn', '⊖', '⊖', '⊖', 'uu', 'uu'], $answer) . '`';
            } else if (strpos($strflags, 'all_words') !== false) {
                $sa .= _('The answer must contain the words:') . ' ' . $answer;
            } else {
                $sa .= $answer;
            }
        }

        if (($scoremethod == 'takeanythingorblank' && trim($la) == '') ||
            $scoremethod == 'submitblank' ||
            (is_string($answer) && trim($answer) == '')
        ) {
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
        $optionVariablesAndValues['partType'] = 'string';
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
