<?php
require __DIR__ . '/../init.php';

if ('development' == $GLOBALS['configEnvironment']
    || 11 == $GLOBALS['groupid'] && in_array(['production', 'staging'], $GLOBALS['configEnvironment'])
) {
    // Access is granted.
} else {
    echo '<p>Insufficient permissions.</p>';
    return;
}

$GLOBALS['placeinhead'] .= '<link rel="stylesheet" type="text/css" href="/ohm/macro_help.css" />';
require __DIR__ . '/../header.php';
?>
    <h1>Table of contents</h1>

    <ul>
        <li><a href="#using_macros">Using Lumen One Macros</a> (read first)</li>
        <li><a href="#question_type_support">Macros and the question types they support</a></li>
        <li><a href="#macro_syntax">Macro syntax</a></li>
        <ul>
            <li><a href="#ohm_getfeedbackbasic">ohm_getfeedbackbasic</a></li>
            <li><a href="#ohm_getfeedbacktxt">ohm_getfeedbacktxt</a></li>
            <li><a href="#ohm_getfeedbacktxtmultans">ohm_getfeedbacktxtmultans</a></li>
            <li><a href="#ohm_getfeedbacktxtnumber">ohm_getfeedbacktxtnumber</a></li>
            <li><a href="#ohm_getfeedbacktxtcalculated">ohm_getfeedbacktxtcalculated</a></li>
            <li><a href="#ohm_getfeedbacktxtnumfunc">ohm_getfeedbacktxtnumfunc</a></li>
            <li><a href="#ohm_getfeedbacktxtessay">ohm_getfeedbacktxtessay</a></li>
        </ul>
        <li><a href="#argument_specific">Argument-specific docs</a></li>
        <ul>
            <li><a href="#arg_answerformat">$answerformat</a></li>
            <li><a href="#arg_requiretimes">$requiretimes</a></li>
            <li><a href="#arg_tolerance">$tolerance</a></li>
        </ul>
    </ul>

    <h1 id="using_macros">Using Lumen One Macros (read first)</h1>

    <p>
        In Lumen One, OHM question code containing feedback will need to use new
        OHM-specific macros. This allows us to return feedback in a format more
        easily parsable by Catra and without embedded HTML.
    </p>

    <p>
        <u>General notes</u>
    </p>

    <ul>
        <li>These macros will only work in Lumen One.</li>
        <li>Macro names are prefixed with <code>ohm_</code>.</li>
        <li><code>loadlibrary("ohm_macros")</code> must be present before
            usages of the new macros. As a best practice, make it the
            first line of code.
        </li>
        <li><span class="danger">Feedback macros must be used for the question
                or question part types they are designed for.</span>
        </li>
        <ul>
            <li>Macros do not have knowledge of question or part types.</li>
            <li>It is not currently possible for macros to warn if they are
                being used for incorrect questions or part types.
            </li>
        </ul>
    </ul>

    <p>
        <u>Multi-part question notes</u>
    </p>

    <ul>
        <li>The question part index must be provided as the <em>last</em>
            argument to all feedback macros.
        </li>
        <li><span class="danger">All feedback arrays must be merged into a
                single <code>$feedback</code> array.</span></li>
    </ul>

    <p>
        <u>One example of merging feedback arrays</u>
    </p>

    <pre>
    loadlibrary("ohm_macros")
    $anstype = "choices,number"

    // Part 1 - Meow
    $choices[0] = ["Cats", "Dogs", "Birds", "Reptiles"]
    $answers[0] = 1
    $feedbacktxt = ["Correct.", "No.", "Nope!", "Why?"]
    <span class="secondaryCodeExample">$feedback0 = ohm_getfeedbacktxt($stuanswers[$thisq][0], $feedbacktxt, $answers[0], 0)</span>

    // Part 2 - What is the answer to life, everything, and the universe?
    $answers[1] = 42
    <span class="secondaryCodeExample">$feedback1 = ohm_getfeedbackbasic($stuanswers[$thisq][1], "Correct!", "Not correct.", $answers[1], 1)</span>

    // Feedback must be defined as a flat array containing feedback for all parts.
    <span class="primaryCodeExample bold">$feedback = mergearrays(<span class="secondaryCodeExample">$feedback0</span>, <span
                class="secondaryCodeExample">$feedback1</span>)</span>
</pre>

    <h1 id="question_type_support">Macros and the question types they support</h1>

    <table border="1" cellspacing="0">
        <thead>
        <tr>
            <th></th>
            <th>multiple choice</th>
            <th>multiple answer</th>
            <th>number</th>
            <th>essay</th>
            <th>calculated</th>
            <th>function</th>
            <th>string</th>
            <th>matching</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>ohm_getfeedbackbasic</td>
            <td>✅</td>
            <td>✅</td>
            <td>✅</td>
            <td></td>
            <td>✅</td>
            <td>✅</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>ohm_getfeedbacktxt</td>
            <td>✅</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>ohm_getfeedbacktxtessay</td>
            <td></td>
            <td></td>
            <td></td>
            <td>✅</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>ohm_getfeedbacktxtessay</td>
            <td></td>
            <td></td>
            <td>✅</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>ohm_getfeedbacktxtcalculated</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>✅</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>ohm_getfeedbacktxtnumfunc</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>✅</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>ohm_getfeedbacktxtmultans</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>

    <h1 id="macro_syntax">Macro syntax</h1>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbackbasic                                          -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbackbasic">ohm_getfeedbackbasic</h2>

    <p>
        This provides a single correct or incorrect feedback for the entire
        single-part question or multi-part question part.
    </p>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbackbasic(
        $stuanswers,
        $correctFeedback,
        $incorrectFeedback,
        $correctAnswer,
        $partNumber  (required for multi-part)
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$stuanswers</code>: The student answer, obtained from
            <code>$stuanswers[$thisq]</code> for single part questions, or using
            the <code>getstuans</code> macro for multipart.
        </li>
        <li><code>$correctFeedback</code>: The correct feedback response</li>
        <li><code>$incorrectFeedback</code>: The incorrect feedback response</li>
        <li><code>$correctAnswer</code>: The correct answer.
            Example: <code>"42"</code></li>
        <li><code>$partNumber</code>: The part number (multi-part only)</li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    // Cup storage. Are you an upper or a downer?
    $choices = ["Up", "Down", "Sideways"]
    $answer = 2

    <span class="primaryCodeExample">$feedback = ohm_getfeedbackbasic($stuanswers[$thisq], "Correct!", "Not correct.", $answer)</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part example
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>fifth</em> argument.
    </p>

    <pre>
    loadlibrary("ohm_macros")

    $anstype = "choices,number"

    // Part 1 - Cup storage. Are you an upper or a downer?
    $choices[0] = ["Up", "Down", "Sideways"]
    $answers[0] = 2

    // Part 2 - What is the answer to life, everything, and the universe?
    $answers[1] = 42

    // Feedback must be defined as an array containing feedback for all parts.
    // The part index must also be provided.
    <span class="secondaryCodeExample">$feedback = mergearrays(
        <span class="primaryCodeExample">ohm_getfeedbackbasic($stuanswers[$thisq], "Correct!", "Not correct.", $answers[0], 0)</span>,
        <span class="primaryCodeExample">ohm_getfeedbackbasic($stuanswers[$thisq], "Correct!", "Not correct.", $answers[1], 1)</span>
    )</span>
</pre>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbacktxt                                            -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbacktxt">ohm_getfeedbacktxt</h2>

    <p>
        Provide answer-specific feedback for multiple choice (<code>choices</code>)
        questions only.
    </p>

    <p>
        Notes:
    </p>

    <ul>
        <li>For answer-specific feedback for <code>multans</code> type questions,
            see <a href="#ohm_getfeedbacktxtmultans">ohm_getfeedbacktxtmultans</a>.
        </li>
    </ul>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbacktxt(
        $stuanswers,
        $feedbackArray,
        $correctAnswer,
        $partNumber  (required for multi-part)
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$stuanswers</code>: The student answer, obtained from
            <code>$stuanswers[$thisq]</code> for single part questions, or using
            the <code>getstuans</code> macro for multipart.
        </li>
        <li><code>$feedbacksPossible</code>: The correct and incorrect feedback
            responses
        </li>
        <li><code>$correctAnswer</code>: The correct answer.
            Example: <code>"1"</code></li>
        <li><code>$partNumber</code>: The part number (multi-part only)</li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    $questions = ["Walter", "Godzilla", "Fluffy", "Scooby Doo"]
    $feedbacktxt = [
        "This is correct. Way to go.",
        "You monster! Try again.",
        "Sorry, Option C is not the right answer. Try again.",
        "Ruh Roh! Try again."
    ]
    $answer = 0

    <span class="primaryCodeExample">$feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer)</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part question
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>fourth</em> argument.
    </p>


    <pre>
    loadlibrary("ohm_macros")

    $anstypes = "choices,choices"

    // Part 1 - Is Hawaiian a valid pizza option?
    $choices[0] = ["Yes", "No", "Sometimes", "Only on Tuesdays"]
    $answers[0] = "0"
    $pizzaFeedbacks = [
        "Correct. Hawaiian pizza is always valid pizza.",
        "Incorrect.",
        "Try again.",
        "Wrong. All days are acceptable pizza days."
    ]

    // Part 2 - Select a companion.
    $questions[1] = ["Cats", "Dogs", "Birds", "Reptiles"]
    $kittyFeedback = ["Correct.", "No.", "Nope!", "Why?"]
    $answers[1] = 0

    // Feedback must be defined as an array containing feedback for all parts.
    // The part index must also be provided.
    <span class="secondaryCodeExample">$feedback = mergearrays(
        <span class="primaryCodeExample">ohm_getfeedbacktxt($stuanswers[$thisq], $pizzaFeedbacks, $answers[0], 0)</span>,
        <span class="primaryCodeExample">ohm_getfeedbacktxt($stuanswers[$thisq], $kittyFeedback, $answers[1], 1)</span>
    )</span>
</pre>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbacktxtmultans                                     -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbacktxtmultans">ohm_getfeedbacktxtmultans</h2>

    <p>
        ⛔ Currently out of scope - do not use! ⛔
    </p>

    <p>
        Provide answer-specific feedback for multiple answer (<code>multans</code>)
        questions only.
    </p>

    <p>
        Notes:
    </p>

    <ul>
        <li>For answer-specific feedback for choices type questions, see ohm_getfeedbacktxt.</li>
        <li>For multipart questions, the part index must be provided as the <em>fourth</em> argument.</li>
        <ul>
            <li>(0 for part 1, 1 for part 2, etc)</li>
        </ul>
    </ul>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbacktxtmultans(
        $stuanswers,
        $feedbacksPossible,
        $correctAnswers,
        $partNumber  (required for multi-part)
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$stuanswers</code>: The student answer, obtained from
            <code>$stuanswers[$thisq]</code> for single part questions, or using
            the <code>getstuans</code> macro for multipart.
        </li>
        <li><code>$feedbacksPossible</code>: The correct and incorrect feedback
            responses
        </li>
        <li><code>$correctAnswers</code>: The correct answers.
            Example: <code>"0,2,5,6"</code></li>
        <li><code>$partNumber</code>: The part number (only for multi-part)</li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    // Soylent green is?
    $choices = ["High energy plankton", "Delicious", "People", "Only on Tuesdays"]
    $feedback = ["Lies.", "Correct.", "Yum.", "Sadly, correct."]
    $answer = "1,2,3"

    <span class="primaryCodeExample">$feedback = ohm_getfeedbacktxtmultans($stuanswers[$thisq], $feedback, $answer)</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part example
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>fourth</em> argument.
    </p>

    <pre>
    loadlibrary("ohm_macros")

    $anstypes = "multans,multans"

    // Part 1 - Soylent green is?
    $choices[0] = ["High energy plankton", "Delicious", "People", "Only on Tuesdays"]
    $soylentFeedback = ["Lies.", "Correct.", "Yum.", "Sadly, correct."]
    $answers[0] = "1,2,3"

    // Part 2 - 1.21 gigawatts may be generated by which means?
    $choices[1] = [
        "A totally sane scientist",
        "Plutonium",
        "Hoverboard",
        "A bolt of lightning",
        "Mr. Fusion"
    ]
    $wattsFeedback = [
        "No, we do not vaporize sane scientists for profit.",
        "Yes, available at any corner drugstore in 1985.",
        "Incorrect. Hoverboards do not generate power.",
        "Correct. Unfortunately, you never know when or where one will strike.",
        "Yes, this is a fine alternative to plutonium."
    ]
    $answers[1] = "1,3,4"

    // Feedback must be defined as an array containing feedback for all parts.
    // The part index must also be provided.
    <span class="secondaryCodeExample">$feedback = mergearrays(
        <span class="primaryCodeExample">ohm_getfeedbacktxtmultans($stuanswers[$thisq], $soylentFeedback, $answers[0], 0)</span>,
        <span class="primaryCodeExample">ohm_getfeedbacktxtmultans($stuanswers[$thisq], $wattsFeedback, $answers[1], 1)</span>
    )</span>
</pre>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbacktxtnumber                                      -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbacktxtnumber">ohm_getfeedbacktxtnumber</h2>

    <p>
        Provide feedback for <em>number</em> questions only.
    </p>

    <p>
        Notes:
    </p>

    <ul>
        <li>For multi-part questions, all optional arguments are required.</li>
    </ul>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbacktxtnumber(
        $studentAnswer,
        $partialCredit,
        $feedbacksPossible,
        $defaultFeedback, (optional, default = "Incorrect"),
        $tolerance,       (optional, default = '0.001'),
        $partNumber       (required for multi-part)
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$studentAnswer</code>: The answer provided by the student</li>
        <li><code>$partialCredit</code>: An array or list of form
            <code>array(number, score, number, score, ... )</code> where the
            scores are in the range <code>[0,1]</code>
        </li>
        <li><code>$feedbacksPossible</code>: An array of feedback messages,
            corresponding in array order to the order of the numbers in the
            <code>$partialCredit</code> list
        </li>
        <li><code>$defaultFeedback</code>: The default incorrect response feedback.</li>
        <li><code>$tolerance</code>: The relative tolerance, or prefix with
            <code>|</code> for an absolute tolerance. In other words, the answer
            must be within this percent/amount to be scored as correct.
            See <a href="#arg_tolerance">docs below</a>.
            -- Required for multipart questions.
        </li>
        <li><code>$partNumber</code>: A part number for multipart questions
            (only for multipart questions)
        </li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    // What is Pi?
    $answer = 3.14

    // This is a combination of the correct answer, and the score earned if
    // within tolerance.
    $partial = "$answer,1"

    $feedbacktxt = ["Correct!", "n/a"]
    $defaultFeedback = "Pi is 3.14"

    <span class="primaryCodeExample">$feedback = ohm_getfeedbacktxtnumber($stuanswers[$thisq], $partial, $feedbacktxt, $defaultFeedback)</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part example
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>sixth</em> argument.
    </p>

    <pre>
    loadlibrary("ohm_macros")

    $anstypes = "number,number"

    // Part 1 - What is Pi?
    $answer[0] = 3.14

    $partial0 = "$answer[0],1"

    $feedbacktxt0 = ["Correct!", "n/a"]
    $defaultFeedback0 = "Incorrect. :("

    // Part 2 - What is the answer to life, the universe, and everything?
    $answer[1] = 42

    $partial1 = "$answer[1],1"

    $feedbacktxt1 = ["Correct!", "n/a"]
    $defaultFeedback1 = "But what was the question?"

    <span class="secondaryCodeExample">$feedback = mergearrays(
        <span class="primaryCodeExample">ohm_getfeedbacktxtnumber($stuanswers[$thisq], $partial0, $feedbacktxt0, $defaultFeedback0, ".001", 0)</span>,
        <span class="primaryCodeExample">ohm_getfeedbacktxtnumber($stuanswers[$thisq], $partial1, $feedbacktxt1, $defaultFeedback1, ".001", 1)</span>
    )</span>
</pre>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbacktxtcalculated                                  -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbacktxtcalculated">ohm_getfeedbacktxtcalculated</h2>

    <p>
        Provide feedback for <em>calculated</em> number type questions.
    </p>

    <p>
        Notes:
    </p>

    <ul>
        <li>For multi-part questions, all optional arguments are required.</li>
    </ul>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbacktxtcalculated($studentAnswer,
        $studentAnswerValue,
        $partialCredit,
        $feedbacksPossible,
        $defaultFeedback,  (optional, default = "Incorrect"),
        $answerformat,     (optional, default = ''),
        $requiretimes,     (optional, default = ''),
        $tolerance,        (optional, default = '0.001'),
        $partNumber        (required for multi-part)
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$studentAnswer</code>: The answer provided by the student</li>
        <li><code>$studentAnswerValue</code>: The numerical value of the student
            answer, obtained from <code>$stuanswersval[$thisq]</code></li>
        <li><code>$partialCredit</code>: An array or list of form
            <code>array(number, score, number, score, ... )</code> where the scores
            are in the range <code>[0,1]</code></li>
        <li><code>$feedbacksPossible</code>: An array of feedback messages,
            corresponding in array order to the order of the numbers in the
            <code>$partialCredit</code> list
        </li>
        <li><code>$defaultFeedback</code>: The default incorrect response feedback</li>
        <li><code>$answerFormat</code>: A single answerformat to apply to all
            expressions, or an array with each element applied to the corresponding
            expression.
            See <a href="#arg_answerformat">docs below</a>.
        <li><code>$requiretimes</code>: A single requiretimes to apply to all
            expressions, or an array with each element applied to the corresponding
            expression.
            See <a href="#arg_requiretimes">docs below</a>.
        <li><code>$tolerance</code>: The relative tolerance, or prefix with
            <code>|</code> for an absolute tolerance. In other words, the answer
            must be within this percent/amount to be scored as correct.
            See <a href="#arg_tolerance">docs below</a>.
        <li><code>$partNumber</code>: A part number for multipart questions
            (only for multipart questions)
        </li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    // Give me half, Eddie!
    $answer = '1/2'

    <span class="primaryCodeExample">$ohm_feedback = ohm_getfeedbacktxtcalculated(
        $stuanswers[$thisq],
        $stuanswersval[$thisq],
        ['1/2', 1, '1/2', 0.5],
        ['Correct.', 'Correct answer, but wrong format.'],
        'Incorrect.',
        ['fraction', '']
    )</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part example
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>eighth</em> argument.
    </p>

    <pre>
    loadlibrary("ohm_macros")

    $anstypes = "calculated,calculated"

    // Part 1 - Give me half, Eddie!
    $answer[0] = '1/2'

    <span class="primaryCodeExample">$feedback0 = ohm_getfeedbacktxtcalculated(
        $stuanswers[$thisq][0],
        $stuanswersval[$thisq][0],
        ['1/2', 1, '1/2', 0.5],
        ['Correct.', 'Correct answer, but wrong format.'],
        'Incorrect.',
        ['fraction', ''],
        '',
        '.001',
        0
    )</span>

    // Part 2 - What is a quarter?
    $answer[1] = '1/4'

    <span class="primaryCodeExample">$feedback1 = ohm_getfeedbacktxtcalculated(
        $stuanswers[$thisq][1],
        $stuanswersval[$thisq][1],
        ['1/4', 1, '1/4', 0.5],
        ['Correct.', 'Correct answer, but wrong format.'],
        'Incorrect.',
        ['fraction', ''],
        '',
        '.001',
        0
    )</span>

    <span class="secondaryCodeExample">$feedback = mergearrays(<span
                    class="primaryCodeExample">$feedback0</span>, <span
                    class="primaryCodeExample">$feedback1</span>)</span>
</pre>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbacktxtnumfunc                                  -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbacktxtnumfunc">ohm_getfeedbacktxtnumfunc</h2>

    <p>
        Provide feedback for <em>numfunc (algebraic expression/equation)</em> type questions.
    </p>

    <p>
        Notes:
    </p>

    <ul>
        <li>For multi-part questions, all optional arguments are required.</li>
    </ul>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbacktxtnumfunc(
        $studentAnswer,
        $partialCredit,
        $feedbacksPossible,
        $defaultFeedback,  (optional, default = "Incorrect"),
        $vars,             (default = 'x'),
        $requiretimes,     (optional, default = ''),
        $tolerance,        (optional, default = '0.001'),
        $domain,           (optional, default = '-10,10',
        $partNumber        (required for multi-part
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$studentAnswer</code>: The answer provided by the student</li>
        <li><code>$partialCredit</code>: An array or list of form
            <code>array(number, score, number, score, ... )</code> where the scores
            are in the range <code>[0,1]</code></li>
        <li><code>$feedbacksPossible</code>: An array of feedback messages,
            corresponding in array order to the order of the numbers in the
            <code>$partialCredit</code> list
        </li>
        <li><code>$defaultFeedback</code>: The default incorrect response feedback.</li>
        <li><code>$vars</code>: A list of variables used in the expression</li>
        <li><code>$requiretimes</code>: A single requiretimes to apply to all
            expressions, or an array with each element applied to the corresponding
            expression.
            See <a href="#arg_requiretimes">docs below</a>.
        </li>
        <li><code>$tolerance</code>: The relative tolerance, or prefix with
            <code>|</code> for an absolute tolerance. In other words, the answer
            must be within this percent/amount to be scored as correct.
            See <a href="#arg_tolerance">docs below</a>.
        </li>
        <li><code>$domain</code>: To limit the test domain</li>
        <li><code>$partNumber</code>: A part number for multipart questions
            (only for multipart questions)
        </li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    // What is the answer to life, the universe, and everything?
    $answer = 42

    $partial = "42,1,21,0.5"
    $fbtext = ["Correct.", "Partially correct."]
    $fbincorrect = "Not correct."

    <span class="primaryCodeExample">$feedback = ohm_getfeedbacktxtnumfunc($stuanswers[$thisq], $partial, $fbtext, $fbincorrect)</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part example
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>eighth</em> argument.
    </p>

    <pre>
    loadlibrary("ohm_macros")

    $anstypes = "numfunc,numfunc"

    // Part 1 - What is the answer to life, the universe, and everything?
    $answer[0] = 42

    $partial0 = "42,1,21,0.5"
    $fbtext0 = ["Correct.", "Partially correct."]
    $fbincorrect0 = "Not correct."

    // Part 2 - What is pi?
    $answer[1] = 3.14

    $partial1 = "3.14,1,1.57,0.5"
    $fbtext1 = ["Correct.", "Partially correct."]
    $fbincorrect1 = "Not correct."

    // Merge all feedback.
    <span class="secondaryCodeExample">$feedback = mergearrays(
        <span class="primaryCodeExample">ohm_getfeedbacktxtnumfunc($stuanswers[$thisq], $partial0, $fbtext0, $fbincorrect0, 'x', '', '.001', '-10,10', 0)</span>,
        <span class="primaryCodeExample">ohm_getfeedbacktxtnumfunc($stuanswers[$thisq], $partial1, $fbtext1, $fbincorrect1, 'x', '', '.001', '-10,10', 1)</span>
    )</span>
</pre>

    <!-- ############################################################# -->
    <!-- ohm_getfeedbacktxtessay                                       -->
    <!-- ############################################################# -->

    <hr/>

    <h2 id="ohm_getfeedbacktxtessay">ohm_getfeedbacktxtessay</h2>

    <p>
        Gives feedback on <em>essay</em> questions once the student has entered any
        response.
    </p>

    <p>
        Syntax:
    </p>

    <pre>
    ohm_getfeedbacktxtessay(
        $studentAnswer,
        $feedbackText,
        $partNumber (required for multi-part)
    )
</pre>

    <p>
        Arguments:
    </p>

    <ol>
        <li><code>$studentAnswer</code>: The answer provided by the student</li>
        <li><code>$feedbackText</code>: The feedback response. Does not have a
            default, but could be simply “Correct.”
        </li>
        <li><code>$partNumber</code>: A part number for multipart questions
            (only for multipart questions)
        </li>
    </ol>

    <p class="singlePartExampleHeader">
        Single part example
    </p>

    <pre>
    loadlibrary("ohm_macros")

    <span class="primaryCodeExample">$feedback = ohm_getfeedbacktxtessay($stuanswers[$thisq], "Essay submitted.")</span>
</pre>

    <p class="multiPartExampleHeader">
        Multi-part example
    </p>

    <p>
        ⚠️ Note: The question part index <span class="danger">must</span> be
        provided as the <em>third</em> argument.
    </p>

    <pre>
    loadlibrary("ohm_macros")

    // Merge all feedback.
    <span class="secondaryCodeExample">$feedback = mergearrays(
        <span class="primaryCodeExample">ohm_getfeedbacktxtessay($stuanswers[$thisq], "Essay submitted.", 0)</span>,
        <span class="primaryCodeExample">ohm_getfeedbacktxtessay($stuanswers[$thisq], "Essay submitted.", 1)</span>,
    )</span>
</pre>

    <!-- ############################################################# -->
    <!-- Argument specific docs                                        -->
    <!-- ############################################################# -->

    <hr/>

    <h1 id="argument_specific">Argument-specific docs</h1>

    <p>
        ℹ️ This documentation is sourced directly from OHM1 help documentation,
        transcribed here for ease of access with just a few adjustments made to
        fit into the above macros.
    </p>

    <!-- ############################################################# -->
    <!-- $answerformat                                                 -->
    <!-- ############################################################# -->

    <h2 id="arg_answerformat">$answerformat</h2>

    <p>
        By default, the student answer is expected to be an expression and be
        equivalent (at points) to the specified answer. This option changes this
        behavior.
    </p>

    <p>
        Options:
    </p>

    <ul>
        <li><code>equation</code>: Specifies that the answer expected is an
            equation rather than an expression. The given answer should also be an
            equation. Be sure to specify all variables in the equation in
            <code>$variables</code>. This may fail on equations that are near zero
            for most values of the input; this can often be overcome by changing
            the <code>$domain</code></li>
    </ul>
    <ul>
        <li><code>inequality</code>: Specifies that the answer is expected to be an
            inequality rather than an expression. The given answers should also be
            an inequality. Be sure to specify all variables in the equation in
            <code>$variables</code>. This may fail on equations that are near zero
            for most values of the input; this can often be overcome by changing
            the <code>$domain</code></li>
    </ul>
    <ul>
        <li><code>generalcomplex</code>: Specifies the answer is a complex function,
            involving <code>i</code></li>
    </ul>
    <ul>
        <li><code>toconst</code>: Specifies that the answer provided by the student
            is allowed to differ from the specified answer by a constant for all
            inputs. Appropriate for comparing antiderivatives. This may fail on
            expressions that evaluate to very large values or raise numbers to very
            large powers
        </li>
    </ul>
    <ul>
        <li><code>scalarmult</code>: Specifies that the answer provided by the
            student is allowed to differ from the specified answer by a scalar
            multiple
        </li>
    </ul>
    <ul>
        <li><code>nosoln/nosolninf</code>: adds a list radio buttons for
            "no solutions" and optionally "infinite solutions".
        </li>
    </ul>
    <ul>
        <li><code>sameform</code>: requires the student's answer be in the
            "same form" as the correct answer. This means exactly the same, except
            for commutation of addition and multiplication, multiplication by 1,
            implicit multiplication, and extra parentheses. (2x-3)(5-x) would be
            "sameform" as (-1x+5)(2*x-3), but 2/4x and 1/2x and 0.5x would not be
            "sameform". Use with caution.
        </li>
    </ul>
    <ul>
        <li><code>list</code>: Allow a list of expressions or equations to be
            entered. Does not ignore duplicates. You cannot use this in combination
            with <code>$partialcredit</code>. <code>$requiretimes</code> will apply
            to each element of the list.
        </li>
    </ul>

    <!-- ############################################################# -->
    <!-- $requiretimes                                                 -->
    <!-- ############################################################# -->

    <h2 id="arg_requiretimes">$requiretimes</h2>

    <p>
        Adds format checking to the student's answer. The list can include multiple
        checks, defined in pairs.
    </p>

    <ul>
        <li>The first is the symbol to look for.</li>
        <li>The second describes what is acceptable.</li>
    </ul>

    <p>
        Example: "^,=3,cos,<2".
    </p>

    <p>
        In the string shown above, the symbol "^" would be required to show up
        exactly 3 times, and "cos" would be required to show up less than 2 times.
        You can use "#" in the symbol location to match any number (including
        decimal values); 3.2x+42y would match twice.
    </p>

    <p>
        Commas are ignored by default with Function type, and a basic exponent like
        x^(-3) is converted to x^-3 before the check.
    </p>

    <ul>
        <li>You can use a regular expression by putting in the symbol location "regex:expression"</li>
        <li>You can match either of two symbols by putting || between them, like "x^-3||/x^3,>0"</li>
        <li>Include "ignore_case,false" to make the search case sensitive.</li>
    </ul>

    <p>
        Additional notes:
    </p>

    <ul>
        <li>You can put "ignore_spaces,true" at the beginning of the $requiretimes
            to ignore spaces in the answer.
        </li>
        <li>Include "ignore_symbol,$" (or some symbol other than $) at the beginning
            of the <code>$requiretimes</code> to ignore that symbol in the answer.
        </li>
    </ul>

    <!-- ############################################################# -->
    <!-- $tolerance                                                    -->
    <!-- ############################################################# -->

    <h2 id="arg_tolerance">$tolerance</h2>

    <p>
        You may specify a relative or absolute tolerance.
    </p>

    <p>
        <u>Relative tolerance</u>
    </p>

    <ul>
        <li>Defines the largest relative error that will be accepted. If this is not
            set, a relative error of .001 (.1%) is used by default.
        </li>
    </ul>

    <p>
        <u>Absolute tolerance</u>
    </p>

    <ul>
        <li>Defines the largest absolute error that will be accepted.</li>
        <li>This will override the use of relative tolerance.</li>
        <li>$tolerance should be prefixed with <code>|</code> to distinguish it as
            absolute
        </li>
    </ul>

<?php
require __DIR__ . '/../footer.php';
