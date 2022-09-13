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
$GLOBALS['RND'] = new Rand();

array_push($GLOBALS['allowedmacros'],
    "ohm_getfeedbackbasic",
    "ohm_getfeedbacktxt",
    "ohm_getfeedbacktxtessay",
    "ohm_getfeedbacktxtnumber",
    "ohm_getfeedbacktxtnumfunc",
    "ohm_getfeedbacktxtcalculated"
);

/**
 * Generates feedback based solely on whether the question was scored correct or not.
 *
 * - Feedback is returned without any added HTML.
 * - If student answer is null, all available feedback is returned.
 * - If a student answer is provided, feedback will be returned based on correctness.
 *
 * @param string $correctFeedback The feedback for correct answers.
 * @param string $wrongFeedback The feedback for incorrect answers.
 * @param mixed $thisq
 * @param mixed $partn A part number for multipart questions to get part-based feedback.
 * @return array|null An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbackbasic(string $correctFeedback, string $wrongFeedback, $thisq, $partn = null): ?array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    $val = $GLOBALS['assess2-curq-iscorrect'] ?? -1;
    if ($partn !== null && is_array($val)) {
        if (isset($val[$partn])) {
            $res = $val[$partn];
        } else {
            $res = -1;
        }
    } else {
        $res = $val;
    }
    if ($res > 0 && $res < 1) {
        $res = 0;
    }

    if ($res == -1) {
        return [
            0 => [
                'correctness' => 'correct',
                'feedback' => $correctFeedback,
            ],
            1 => [
                'correctness' => 'incorrect',
                'feedback' => $wrongFeedback,
            ]
        ];
    } else if ($res == 1) {
        return [
            'correctness' => 'correct',
            'feedback' => $correctFeedback,
        ];
    } else if ($res == 0) {
        return [
            'correctness' => 'incorrect',
            'feedback' => $wrongFeedback,
        ];
    }
}

/**
 * Gives feedback on multiple choice questions based on the student's answer.
 *
 * - Feedback is returned without any added HTML.
 * - If student answer is null, all available feedback is returned.
 * - If a student answer is provided, feedback will be returned based on correctness.
 *
 * @param string|integer $studentAnswer The answer provided by the student.
 * @param array $feedbacksPossible The correct and incorrect feedback responses.
 * @param string|integer $correctAnswer The correct answer.
 * @return array An associative array of feedback(s).
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxt($studentAnswer, array $feedbacksPossible, $correctAnswer): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    if ($studentAnswer === null) {
        // If no student answers are provided, then we are only displaying
        // the question. (no scoring)
        $feedbacks = [];
        foreach ($feedbacksPossible as $index => $feedbackText) {
            $correctness = ($index == $correctAnswer) ? 'correct' : 'incorrect';
            $feedbacks[$index] = [
                'correctness' => $correctness,
                'feedback' => $feedbackText
            ];
        }
        return $feedbacks;
    } else if ($studentAnswer === 'NA') {
        return [
            'correctness' => 'incorrect',
            'feedback' => _("No answer selected. Try again.")
        ];
    } else {
        $anss = explode(' or ', $correctAnswer);
        foreach ($anss as $ans) {
            // Student provided the correct answer.
            if ($studentAnswer == $ans) {
                $feedback = [
                    'correctness' => 'correct',
                    'feedback' => null,
                ];
                if (isset($feedbacksPossible[$studentAnswer])) {
                    $feedback['feedback'] = $feedbacksPossible[$studentAnswer];
                }
                return $feedback;
            }
        }
        // Student provided an incorrect answer.
        $feedback = [
            'correctness' => 'incorrect',
            'feedback' => null,
        ];
        if (isset($feedbacksPossible[$studentAnswer])) {
            $feedback['feedback'] = $feedbacksPossible[$studentAnswer];
        }
        return $feedback;
    }
}

/**
 * Gives feedback on essay questions once the student has entered any response.
 *
 * @param string $studentAnswer The answer provided by the student.
 * @param string $feedbackText The feedback response.
 * @return array An associative array of feedback(s).
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtessay(string $studentAnswer, string $feedbackText): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }
    if ($studentAnswer == null || trim($studentAnswer) == '') {
        return [];
    } else {
        return [
            'correctness' => 'correct',
            'feedback' => $feedbackText,
        ];
    }
}

/**
 * Gives feedback on number questions.
 *
 * @param mixed $studentAnswer The answer provided by the student.
 * @param array $partialCredit An array or list of form array(number, score, number, score, ... )
 *                             where the scores are in the range [0,1].
 * @param array $feedbacksPossible An array of feedback messages, corresponding in array
 *                                 order to the order of the numbers in the partialcredit list.
 * @param string $defaultFeedback The default incorrect response feedback.
 * @param float|string $tolerance The relative tolerance (defaults to .001),
 *                                or prefix with | for an absolute tolerance.
 * @return array An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtnumber($studentAnswer,
                                  array $partialCredit,
                                  array $feedbacksPossible,
                                  string $defaultFeedback = 'Incorrect',
                                  $tolerance = .001
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }
    if ($studentAnswer !== null) {
        $studentAnswer = preg_replace('/[^\-\d\.eE]/', '', $studentAnswer);
    }
    if ($studentAnswer === null) {
        return [];
    } else if (!is_numeric($studentAnswer)) {
        return [
            'correctness' => 'incorrect',
            'feedback' => _("This answer does not appear to be a valid number."),
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
                    'correctness' => 'incorrect',
                    'feedback' => $feedbacksPossible[$match / 2],
                ];
            } else {
                return [
                    'correctness' => 'correct',
                    'feedback' => $feedbacksPossible[$match / 2],
                ];
            }
        } else {
            return [
                'correctness' => 'incorrect',
                'feedback' => $defaultFeedback,
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
 * @param array $partialCredit An array or list of form array(number, score, number, score, ... )
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
 * @return array An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtcalculated($studentAnswer,
                                      $studentAnswerValue,
                                      array $partialCredit,
                                      array $feedbacksPossible,
                                      string $defaultFeedback = 'Incorrect',
                                      $answerformat = '',
                                      $requiretimes = '',
                                      $tolerance = .001
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }
    if ($studentAnswer === null) {
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
                    'correctness' => 'incorrect',
                    'feedback' => $feedbacksPossible[$match / 2],
                ];
            } else {
                return [
                    'correctness' => 'correct',
                    'feedback' => $feedbacksPossible[$match / 2],
                ];
            }
        } else {
            return [
                'correctness' => 'incorrect',
                'feedback' => $defaultFeedback,
            ];
        }
    }
}

/**
 * Gives feedback on numfunc (algebraic expression/equation) questions.
 *
 * @param mixed $studentAnswer The student answer, obtained from $stuanswers[$thisq] for single
 *                             part questions, or using the getstuans macro for multipart.
 * @param array $partialCredit An array or list of form array(expression, score, expression, score, ... )
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
 * @return array An associative array of feedback.
 * @see https://localhost/help.php#feedbackmacros
 */
function ohm_getfeedbacktxtnumfunc($studentAnswer,
                                   array $partialCredit,
                                   array $feedbacksPossible,
                                   string $defaultFeedback = 'Incorrect',
                                   $vars = 'x',
                                   $requiretimes = '',
                                   $tolerance = '.001',
                                   $domain = '-10,10'
): array
{
    if (isset($GLOBALS['testsettings']['testtype']) && ($GLOBALS['testsettings']['testtype'] == 'NoScores' || $GLOBALS['testsettings']['testtype'] == 'EndScore')) {
        return [];
    }

    if ($studentAnswer === null || trim($studentAnswer) === '') {
        return [];
    } else {
        if (strval($tolerance)[0] == '|') {
            $abstol = true;
            $tolerance = substr($tolerance, 1);
        } else {
            $abstol = false;
        }
        $type = "expression";
        if (strpos($studentAnswer, '=') !== false && strpos($studentAnswer, '=') !== false) {
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
                'correctness' => 'incorrect',
                'feedback' => $defaultFeedback,
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
                'correctness' => 'incorrect',
                'feedback' => $defaultFeedback,
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
                    'correctness' => 'incorrect',
                    'feedback' => $feedbacksPossible[$match / 2],
                ];
            } else {
                return [
                    'correctness' => 'correct',
                    'feedback' => $feedbacksPossible[$match / 2],
                ];
            }
        } else {
            return [
                'correctness' => 'incorrect',
                'feedback' => $defaultFeedback,
            ];
        }
    }
}

