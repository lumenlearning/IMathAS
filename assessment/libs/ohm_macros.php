<?php
/*
 * This file is based on /assessment/macros.php.
 *
 * Feedback macros have been copied and modified to always return all feedback
 * available (correct and incorrect) when no student answer is provided.
 *
 * When a student answer is provided, only the appropriate feedback (correct or
 * incorrect) is returned.
 *
 * See /help.php#feedbackmacros for more information on macro usage.
 */

require_once(__DIR__ . '/../../includes/Rand.php');

array_push($GLOBALS['allowedmacros'],
    "ohm_getfeedbackbasic",
    "ohm_getfeedbacktxt",
    "ohm_getfeedbacktxtessay",
    "ohm_getfeedbacktxtnumber",
    "ohm_getfeedbacktxtnumfunc",
    "ohm_getfeedbacktxtcalculated",
    "ohm_getfeedbacktxtmultans",
);

/*
 * Copied from macros.php. Required for convertTri(), which is required
 * for numtowords().
 */
$GLOBALS['ones'] = array( "", " one", " two", " three", " four", " five", " six", " seven", " eight", " nine", " ten", " eleven", " twelve", " thirteen", " fourteen", " fifteen", " sixteen", " seventeen", " eighteen", " nineteen");
$GLOBALS['onesth'] = array(""," first"," second", " third", " fourth", " fifth", " sixth", " seventh", " eighth", " ninth", "tenth"," eleventh", " twelfth", " thirteenth", " fourteenth"," fifteenth", " sixteenth", " seventeenth", " eighteenth"," nineteenth");
$GLOBALS['tens'] = array( "", "", " twenty", " thirty", " forty", " fifty", " sixty", " seventy", " eighty", " ninety");
$GLOBALS['tensth'] = array("",""," twentieth", " thirtieth", " fortieth", " fiftieth", " sixtieth", " seventieth", " eightieth", " ninetieth");
$GLOBALS['triplets'] = array( "", " thousand", " million", " billion", " trillion", " quadrillion", " quintillion", " sextillion", " septillion", " octillion", " nonillion");
$GLOBALS['placevals'] = array( "", "tenth", "hundredth", "thousandth", "ten-thousandth", "hundred-thousandth", "millionth", "ten-millionth", "hundred-millionth", "billionth");

/**
 * Generate the question index for a single or multipart question for feedback.
 *
 * - Index strings will match question input IDs in question display HTML.
 * - Single part questions will always be indexed as "qn0".
 *
 * @param integer|null $partNumber The question part number.
 * @return string The question index for feedback.
 */
function _getFeedbackIndex(?int $partNumber): string
{
    if (is_null($partNumber)) {
        $questionIndex = 'qn0';
    } else {
        $questionNumber = 1000 + $partNumber;
        $questionIndex = 'qn' . $questionNumber;
    }

    return $questionIndex;
}

/**
 * Gives feedback based on a simple comparison between the correct
 * answer and the student's answer.
 *
 * Differences from getfeedbackbasic in IMathAS' macros.php:
 * - The function signature matches the pattern of all other ohm_* macros.
 * - The answer is checked directly, instead of looking at $GLOBALs.
 * - Unlike getfeedbackbasic, which depends on a $GLOBAL to determine
 *   correctness, this macro is unable to determine correctness for
 *   shuffled answers. Correct answers are only made available to this
 *   macro before they've been shuffled.
 *
 * The most important note:
 * - Shuffling MUST be disabled to use this macro.
 *
 * @param string|int|array|null $stuanswers The student answer, obtained from $stuanswers[$thisq] for single
 *                                          part questions, or using the getstuans macro for multipart.
 * @param string $correctFeedback The feedback for correct answers.
 * @param string $incorrectFeedback The feedback for incorrect answers.
 * @param string|integer|null $correctAnswer The correct answer from question code.
 * @param integer|null $partNumber The part number for multipart questions.
 *                             Null for single part questions.
 * @return array An array of feedback.
 */
function ohm_getfeedbackbasic($stuanswers,
                              string $correctFeedback,
                              string $incorrectFeedback,
                              $correctAnswer,
                              ?int $partNumber = null
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);

    if (!is_null($partNumber) && is_array($stuanswers)) {
        $studentAnswer = $stuanswers[$partNumber];
    } else {
        $studentAnswer = $stuanswers;
    }

    if (is_null($studentAnswer) || '' === $studentAnswer) {
        return [];
    }

    // For "multans" type questions, the student answer will be an array of answer keys.
    if (is_array($studentAnswer)) {
        sort($studentAnswer);
        $studentAnswer = implode(',', $studentAnswer);
        // The correct answer is written by humans.
        // Remove spaces and ensure the answer keys are sorted.
        if (is_array($correctAnswer)) {
            $correctAnswer = array_map('trim', $correctAnswer);
        } else {
            $correctAnswer = preg_replace('/\s*/', '', $correctAnswer);
            $correctAnswer = explode(',', $correctAnswer);
        }
        sort($correctAnswer);
        $correctAnswer = implode(',', $correctAnswer);
    } else {
        $studentAnswer = normalizemathunicode($studentAnswer);
    };

    if ($studentAnswer == $correctAnswer) {
        return [
            $questionIndex => [
                'correctness' => 'correct',
                'feedback' => $correctFeedback,
                'failsafe_data' => [
                    'incorrect_feedback' => $incorrectFeedback
                ],
            ],
        ];
    } else {
        return [
            $questionIndex => [
                'correctness' => 'incorrect',
                'feedback' => $incorrectFeedback,
                'failsafe_data' => [
                    'correct_feedback' => $correctFeedback,
                ],
            ],
        ];
    }
}

/**
 * Get ALL feedback for "choices" type questions.
 *
 * From within this macro, it is not possible to obtain the correct answer
 * indexes for a question after they've been shuffled with a question seed.
 *
 * Feedback is returned without any added HTML.
 *
 * Notes:
 * - The random answer key mapping is available in:
 *   - ohm-hooks/assess2/questions/scorepart/choices_score_part.php
 * - The feedback returned by this function will need to be shuffled and filtered in:
 *   - ohm-hooks/assess2/assess_standalone.php
 *
 * @param string|integer $studentAnswer The answer provided by the student.
 * @param array $feedbacksPossible The correct and incorrect feedback responses.
 * @param string|integer $correctAnswer The correct answer.
 *                                          Examples:
 *                                              - 2
 *                                              - "2 or 3"
 * @param integer|null $partNumber A part number for multipart questions.
 * @return array An associative array of feedback(s).
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxt($studentAnswer,
                            array $feedbacksPossible,
                            $correctAnswer,
                            ?int $partNumber = null
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);

    // This should probably be: ( is_null(answer) || '' === answer ),
    // but this macro is currently in use by PROD question code, so
    // leaving the !is_null check as-is for now.
    if (!is_null($studentAnswer) && '' === $studentAnswer) {
        return [];
    } else if ($studentAnswer === 'NA') {
        return [
            $questionIndex => [
                'correctness' => 'incorrect',
                'feedback' => _("No answer selected. Try again.")
            ],
        ];
    }

    $correctAnswersAsArray = explode(' or ', $correctAnswer);

    return _getAllFeedbacksWithCorrectness($feedbacksPossible, $correctAnswersAsArray, $partNumber);
}

/**
 * Gives feedback on essay questions once the student has entered any response.
 *
 * @param ?string $studentAnswer The answer provided by the student.
 * @param string $feedbackText The feedback response.
 * @param integer|null $partNumber A part number for multipart questions.
 * @return array An associative array of feedback(s).
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtessay(?string $studentAnswer, string $feedbackText, ?int $partNumber = null): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);

    if ($studentAnswer == null || trim($studentAnswer) == '') {
        return [];
    } else {
        return [
            $questionIndex => [
                'correctness' => 'correct',
                'feedback' => $feedbackText,
            ],
        ];
    }
}

/**
 * Gives feedback on number questions.
 *
 * @param mixed $studentAnswer The answer provided by the student.
 * @param mixed $partialCredit An array or list of form array(number, score, number, score, ... )
 *                             where the scores are in the range [0,1].
 * @param array $feedbacksPossible An array of feedback messages, corresponding in array
 *                                 order to the order of the numbers in the partialcredit list.
 * @param string $defaultFeedback The default incorrect response feedback.
 * @param float|string $tolerance The relative tolerance (defaults to .001),
 *                                or prefix with | for an absolute tolerance.
 * @param integer|null $partNumber A part number for multipart questions.
 * @return array An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtnumber($studentAnswer,
                                  $partialCredit,
                                  array $feedbacksPossible,
                                  string $defaultFeedback = 'Incorrect',
                                  $tolerance = .001,
                                  ?int $partNumber = null
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);
    $studentAnswer = is_null($partNumber) ? $studentAnswer : $studentAnswer[$partNumber];

    if ($studentAnswer !== null) {
        $studentAnswer = preg_replace('/[^\-\d\.eE]/', '', $studentAnswer);
    }
    if ($studentAnswer === null) {
        return [];
    } else if (!is_numeric($studentAnswer)) {
        return [
            $questionIndex => [
                'correctness' => 'incorrect',
                'feedback' => _("This answer does not appear to be a valid number."),
            ],
        ];
    } else {
        if (strval($tolerance)[0] == '|') {
            $abstol = true;
            $tolerance = substr($tolerance, 1);
        } else {
            $abstol = false;
        }
        $match = -1;
        if (!is_array($partialCredit)) {
            $partialCredit = listtoarray($partialCredit);
        }
        for ($i = 0; $i < count($partialCredit); $i += 2) {
            if (!is_numeric($partialCredit[$i])) {
                $partialCredit[$i] = evalMathParser($partialCredit[$i]);
            }
            if ($abstol) {
                if (abs($studentAnswer - $partialCredit[$i]) < $tolerance + 1E-12) {
                    $match = $i;
                    break;
                }
            } else {
                if (abs($studentAnswer - $partialCredit[$i]) / (abs($partialCredit[$i]) + .0001) < $tolerance + 1E-12) {
                    $match = $i;
                    break;
                }
            }
        }
        if ($match > -1) {
            if ($partialCredit[$i + 1] < 1) {
                return [
                    $questionIndex => [
                        'correctness' => 'incorrect',
                        'feedback' => $feedbacksPossible[$match / 2],
                    ],
                ];
            } else {
                return [
                    $questionIndex => [
                        'correctness' => 'correct',
                        'feedback' => $feedbacksPossible[$match / 2],
                    ],
                ];
            }
        } else {
            return [
                $questionIndex => [
                    'correctness' => 'incorrect',
                    'feedback' => $defaultFeedback,
                ],
            ];
        }
    }
}

/**
 * Gives feedback on calculated number questions.
 *
 * Example:
 *   ohm_getfeedbacktxtcalculated($stuanswers[$thisq],
 *                                $stuanswersval[$thisq],
 *                                array("1/2",1,"1/2",.5),
 *                                array(
 *                                  "Correct",
 *                                  "Right value, but give your answer as a fraction"),
 *                                  "Incorrect",
 *                                  array("fraction","")
 *                                )
 *     This will check the students answer against the answer "1/2" twice,
 *     the first time applying the "fraction" answerformat, and the second time not.
 *
 * @param mixed $studentAnswer The student answer, obtained from $stuanswers[$thisq] for single
 *                             part questions, or using the getstuans macro for multipart.
 * @param mixed $studentAnswerValue The numerical value of the student answer, obtained from $stuanswersval[$thisq]
 * @param mixed $partialCredit An array or list of form array(number, score, number, score, ... )
 *                             where the scores are in the range [0,1].
 * @param array $feedbacksPossible An array of feedback messages, corresponding in array
 *                                 order to the order of the numbers in the partialcredit list.
 * @param string $defaultFeedback The default incorrect response feedback.
 * @param string|array $answerformat A single answerformat to apply to all expressions, or
 *                                   an array with each element applied to the corresponding expression.
 * @param string|array $requiretimes A single requiretimes to apply to all expressions, or an array
 *                             with each element applied to the corresponding expression.
 * @param float|string $tolerance The relative tolerance (defaults to .001),
 *                                or prefix with | for an absolute tolerance.
 * @param integer|null $partNumber A part number for multipart questions.
 * @return array An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtcalculated($studentAnswer,
                                      $studentAnswerValue,
                                      $partialCredit,
                                      array $feedbacksPossible,
                                      string $defaultFeedback = 'Incorrect',
                                      $answerformat = '',
                                      $requiretimes = '',
                                      $tolerance = .001,
                                      ?int $partNumber = null
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);

    if ($studentAnswer === null || !is_numeric($studentAnswerValue)) {
        return [];
    } else {
        if (strval($tolerance)[0] == '|') {
            $abstol = true;
            $tolerance = substr($tolerance, 1);
        } else {
            $abstol = false;
        }
        $match = -1;
        if (!is_array($partialCredit)) {
            $partialCredit = listtoarray($partialCredit);
        }
        for ($i = 0; $i < count($partialCredit); $i += 2) {
            $idx = $i / 2;
            if (is_array($requiretimes)) {
                if ($requiretimes[$idx] != '') {
                    if (checkreqtimes(str_replace(',', '', $studentAnswer), $requiretimes[$idx]) == 0) {
                        $rightanswrongformat = $i;
                        continue;
                    }
                }
            } else if ($requiretimes != '') {
                if (checkreqtimes(str_replace(',', '', $studentAnswer), $requiretimes) == 0) {
                    $rightanswrongformat = $i;
                    continue;
                }
            }
            if (is_array($answerformat)) {
                if ($answerformat[$idx] != '') {
                    if (checkanswerformat($studentAnswer, $answerformat[$idx]) == 0) {
                        $rightanswrongformat = $i;
                        continue;
                    }
                }
            } else if ($answerformat != '') {
                if (checkanswerformat($studentAnswer, $answerformat) == 0) {
                    $rightanswrongformat = $i;
                    continue;
                }
            }
            if (!is_numeric($partialCredit[$i])) {
                $partialCredit[$i] = evalMathParser($partialCredit[$i]);
            }
            if ($abstol) {
                if (abs($studentAnswerValue - $partialCredit[$i]) < $tolerance + 1E-12) {
                    $match = $i;
                    break;
                }
            } else {
                if (abs($studentAnswerValue - $partialCredit[$i]) / (abs($partialCredit[$i]) + .0001) < $tolerance + 1E-12) {
                    $match = $i;
                    break;
                }
            }
        }
        if ($match > -1) {
            if ($partialCredit[$i + 1] < 1) {
                return [
                    $questionIndex => [
                        'correctness' => 'incorrect',
                        'feedback' => $feedbacksPossible[$match / 2],
                    ],
                ];
            } else {
                return [
                    $questionIndex => [
                        'correctness' => 'correct',
                        'feedback' => $feedbacksPossible[$match / 2],
                    ],
                ];
            }
        } else {
            return [
                $questionIndex => [
                    'correctness' => 'incorrect',
                    'feedback' => $defaultFeedback,
                ],
            ];
        }
    }
}

/**
 * Gives feedback on numfunc (algebraic expression/equation) questions.
 *
 * @param mixed $studentAnswer The student answer, obtained from $stuanswers[$thisq] for single
 *                             part questions, or using the getstuans macro for multipart.
 * @param mixed $partialCredit An array or list of form array(expression, score, expression, score, ... )
 *                             where the scores are in the range [0,1].
 * @param array $feedbacksPossible An array of feedback messages, corresponding in array
 *                                 order to the order of the numbers in the partialcredit list.
 * @param string $defaultFeedback The default incorrect response feedback.
 * @param string $vars A list of variables used in the expression. Defaults to "x".
 * @param string|array $requiretimes A single requiretimes to apply to all expressions,
 *                                   or an array with each element applied to the corresponding expression
 * @param float|string $tolerance The relative tolerance (defaults to .001),
 *                                or prefix with | for an absolute tolerance.
 * @param string $domain To limit the test domain. Defaults to "-10,10".
 * @param integer|null $partNumber A part number for multipart questions.
 * @return array An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtnumfunc($studentAnswer,
                                   $partialCredit,
                                   array $feedbacksPossible,
                                   string $defaultFeedback = 'Incorrect',
                                   $vars = 'x',
                                   $requiretimes = '',
                                   $tolerance = '.001',
                                   $domain = '-10,10',
                                   ?int $partNumber = null
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);
    $studentAnswer = is_null($partNumber) ? $studentAnswer : $studentAnswer[$partNumber];

    if ($studentAnswer === null || trim($studentAnswer) === '') {
        return [];
    } else {
        $studentAnswer = normalizemathunicode($studentAnswer);
        if (strval($tolerance)[0] == '|') {
            $abstol = true;
            $tolerance = substr($tolerance, 1);
        } else {
            $abstol = false;
        }
        $type = "expression";
        if (strpos($studentAnswer, '=') !== false) {
            $type = "equation";
        }
        $stuorig = $studentAnswer;
        $studentAnswer = str_replace(array('[', ']'), array('(', ')'), $studentAnswer);
        if ($type == 'equation') {
            $studentAnswer = preg_replace('/(.*)=(.*)/', '$1-($2)', $studentAnswer);
        }

        $fromto = listtoarray($domain);
        $variables = listtoarray($vars);
        $vlist = implode(",", $variables);
        $origstu = $studentAnswer;
        $stufunc = makeMathFunction(makepretty($studentAnswer), $vlist);
        if ($stufunc === false) {
            return [
                $questionIndex => [
                    'correctness' => 'incorrect',
                    'feedback' => $defaultFeedback,
                ],
            ];
        }

        $numpts = 20;
        for ($i = 0; $i < $numpts; $i++) {
            for ($j = 0; $j < count($variables); $j++) {
                if (isset($fromto[2]) && $fromto[2] == "integers") {
                    $tps[$i][$j] = $GLOBALS['RND']->rand($fromto[0], $fromto[1]);
                } else if (isset($fromto[2 * $j + 1])) {
                    $tps[$i][$j] = $fromto[2 * $j] + ($fromto[2 * $j + 1] - $fromto[2 * $j]) * $GLOBALS['RND']->rand(0, 499) / 500.0 + 0.001;
                } else {
                    $tps[$i][$j] = $fromto[0] + ($fromto[1] - $fromto[0]) * $GLOBALS['RND']->rand(0, 499) / 500.0 + 0.001;
                }
            }
        }

        $stupts = array();
        $cntnana = 0;
        $correct = true;
        for ($i = 0; $i < $numpts; $i++) {
            $varvals = array();
            for ($j = 0; $j < count($variables); $j++) {
                $varvals[$variables[$j]] = $tps[$i][$j];
            }
            $stupts[$i] = $stufunc($varvals);
            if (isNaN($stupts[$i])) {
                $cntnana++;
            }
            if ($stupts[$i] === false) {
                $correct = false;
                break;
            }
        }
        if ($cntnana == $numpts || !$correct) { //evald to NAN at all points
            return [
                $questionIndex => [
                    'correctness' => 'incorrect',
                    'feedback' => $defaultFeedback,
                ],
            ];
        }

        $match = -1;
        if (!is_array($partialCredit)) {
            $partialCredit = listtoarray($partialCredit);
        }
        for ($k = 0; $k < count($partialCredit); $k += 2) {
            $correct = true;
            $b = $partialCredit[$k];
            if ($type == 'equation') {
                if (substr_count($b, '=') != 1) {
                    continue;
                }
                $b = preg_replace('/(.*)=(.*)/', '$1-($2)', $b);
            }
            $origb = $b;
            $bfunc = makeMathFunction(makepretty($b), $vlist);
            if ($bfunc === false) {
                //parse error - skip it
                continue;
            }
            $cntnanb = 0;
            $ratios = array();
            for ($i = 0; $i < $numpts; $i++) {
                $varvals = array();
                for ($j = 0; $j < count($variables); $j++) {
                    $varvals[$variables[$j]] = $tps[$i][$j];
                }
                $ansb = $bfunc($varvals);

                //echo "real: $ansa, my: $ansb <br/>";
                if (isNaN($ansb)) {
                    $cntnanb++;
                    continue;
                }
                if (isNaN($stupts[$i])) {
                    continue;
                } //avoid NaN problems

                if ($type == 'equation') {
                    if (abs($stupts[$i]) > .000001 && is_numeric($ansb)) {
                        $ratios[] = $ansb / $stupts[$i];
                        if (abs($ansb) <= .00000001 && $stupts[$i] != 0) {
                            $cntzero++;
                        }
                    } else if (abs($stupts[$i]) <= .000001 && is_numeric($ansb) && abs($ansb) <= .00000001) {
                        $cntbothzero++;
                    }
                } else {
                    if ($abstol) {
                        if (abs($stupts[$i] - $ansb) > $tolerance - 1E-12) {
                            $correct = false;
                            break;
                        }
                    } else {
                        if ((abs($stupts[$i] - $ansb) / (abs($stupts[$i]) + .0001) > $tolerance - 1E-12)) {
                            $correct = false;
                            break;
                        }
                    }
                }
            }
            //echo "$i, $ansa, $ansb, $cntnana, $cntnanb";
            if ($cntnanb == 20) {
                continue;
            } else if ($i < 20) {
                continue;
            }
            if (abs($cntnana - $cntnanb) > 1) {
                continue;
            }
            if ($type == "equation") {
                if ($cntbothzero > $numpts - 2) {
                    $match = $k;
                    break;
                } else if (count($ratios) > 0) {
                    if (count($ratios) == $cntzero) {
                        continue;
                    } else {
                        $meanratio = array_sum($ratios) / count($ratios);
                        for ($i = 0; $i < count($ratios); $i++) {
                            if ($abstol) {
                                if (abs($ratios[$i] - $meanratio) > $tolerance - 1E-12) {
                                    continue 2;
                                }
                            } else {
                                if ((abs($ratios[$i] - $meanratio) / (abs($meanratio) + .0001) > $tolerance - 1E-12)) {
                                    continue 2;
                                }
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
            if ($correct) {
                if (is_array($requiretimes)) {
                    if ($requiretimes[$k / 2] != '') {
                        if (checkreqtimes(str_replace(',', '', $stuorig), $requiretimes[$k / 2]) == 0) {
                            $rightanswrongformat = $k;
                            continue;
                        }
                    }
                } else if ($requiretimes != '') {
                    if (checkreqtimes(str_replace(',', '', $stuorig), $requiretimes) == 0) {
                        $rightanswrongformat = $k;
                        continue;
                    }
                }
                $match = $k;
                break;
            } else {
                continue;
            }

        }
        //WHAT to do with right answer, wrong format??
        if ($match > -1) {
            if ($partialCredit[$match + 1] < 1) {
                return [
                    $questionIndex => [
                        'correctness' => 'incorrect',
                        'feedback' => $feedbacksPossible[$match / 2],
                    ],
                ];
            } else {
                return [
                    $questionIndex => [
                        'correctness' => 'correct',
                        'feedback' => $feedbacksPossible[$match / 2],
                    ],
                ];
            }
        } else {
            return [
                $questionIndex => [
                    'correctness' => 'incorrect',
                    'feedback' => $defaultFeedback,
                ],
            ];
        }
    }
}

/**
 * Get ALL feedback for multiple answer questions.
 *
 * From within this macro, it is not possible to obtain the correct answer
 * indexes for a question after they've been shuffled with a question seed.
 *
 * Feedback is returned without any added HTML.
 *
 * Notes:
 * - The random answer key mapping is available in:
 *   - ohm-hooks/assess2/questions/scorepart/multiple_answer_score_part.php
 * - The feedback returned by this function will need to be shuffled and filtered in:
 *   - ohm-hooks/assess2/assess_standalone.php
 *
 * @param string|array|null $stuanswers The student answer, obtained from $stuanswers[$thisq] for single
 *                                      part questions, or using the getstuans macro for multipart.
 * @param array $feedbacksPossible The correct and incorrect feedback responses.
 * @param string $correctAnswers The correct answers. Example: "0,2,5,6"
 * @param integer|null $partNumber The part number for multipart questions.
 * @return array An associative array of feedback(s).
 */
function ohm_getfeedbacktxtmultans($stuanswers, // can't specify a type here :(
                                   array $feedbacksPossible,
                                   string $correctAnswers,
                                   ?int $partNumber = null
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $questionIndex = _getFeedbackIndex($partNumber);

    if (!is_null($partNumber) && is_array($stuanswers)) {
        $studentAnswer = $stuanswers[$partNumber];
    } else {
        $studentAnswer = $stuanswers;
    }

    if (is_null($studentAnswer) || '' === $studentAnswer) {
        return [];
    } else if ($studentAnswer === 'NA') {
        return [
            $questionIndex => [
                'correctness' => 'incorrect',
                'feedback' => _("No answer selected. Try again.")
            ],
        ];
    }

    # This value is created by a human, so remove spaces if found.
    $correctAnswers = preg_replace('/s*/', '', $correctAnswers);
    $correctAnswersAsArray = explode(',', $correctAnswers);

    return _getAllFeedbacksWithCorrectness($feedbacksPossible, $correctAnswersAsArray, $partNumber);
}

/**
 * Get all feedback for a question with correctness.
 *
 * This is used by feedback macros for "choices" and "multans" type questions.
 * - ohm_getfeedbacktxt  (choices)
 * - ohm_getfeedbacktxtmultans  (multans)
 *
 * @param array $feedbacksPossible The correct and incorrect feedback responses.
 * @param array $correctAnswers An array of correct answers.
 * @param integer|null $partNumber The part number for multipart questions.
 * @return array An associative array of feedback(s) with correctness.
 */
function _getAllFeedbacksWithCorrectness(array $feedbacksPossible,
                                         array $correctAnswers,
                                         ?int $partNumber = null
): array
{
    $questionIndex = _getFeedbackIndex($partNumber);

    $allFeedback = [];
    foreach ($feedbacksPossible as $idx => $feedback) {
        $answerIndex = $questionIndex . '-' . $idx;
        $isCorrect = in_array($idx, $correctAnswers);

        $allFeedback += [
            $answerIndex => [
                'correctness' => $isCorrect ? 'correct' : 'incorrect',
                'feedback' => $feedback,
            ]
        ];
    }
    return $allFeedback;
}
