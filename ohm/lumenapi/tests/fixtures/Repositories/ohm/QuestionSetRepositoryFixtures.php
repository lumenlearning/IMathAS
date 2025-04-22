<?php

namespace Tests\fixtures\Repositories\ohm;

class QuestionSetRepositoryFixtures
{
    // A single multi-part question with all part types.
    const SINGLE_QUESTION_MULTIPART_ALL_TYPES = [
        'id' => '5485', // id
        'uniqueid' => '1712613994709310',
        'adddate' => '1712613994',
        'lastmoddate' => '1724438304',
        'ownerid' => '1',
        'author' => 'Lastname, Firstname',
        'userights' => '0',
        'license' => '1',
        'description' => 'Multi-part test with all supported question types -- Source OHM 2 stg QID: 2813',
        'qtype' => 'multipart',
        'control' => <<<END_OF_STRING
loadlibrary("ohm_macros")

//=== General question setup ===

// No support for shuffling answers, feedback, or answer choices right now
\$noshuffle = "all"

// List the OHM question types for the multi-part question in this array
\$anstypes = array("calculated","choices","essay","matching","multans","number","numfunc","string")

// Set default correct and Incorrect feedback messages for use in the various feedback macros below
\$fbcorrect = "Correct!"
\$fbincorrect = "Incorrect!"

//=== calculated part ===

\$a = -200
\$b = 100

\$ans0 = 300

\$fb0 = ohm_getfeedbackbasic(\$stuanswers[\$thisq], \$fbcorrect, \$fbincorrect, \$ans0, 0)

//=== multiple choice part ===

\$questions[1] = ["100","Infinity","0","I was told there would be no math"]

\$ans1 = 0

\$fbtxt1[0] = "This is correct. Way to go."
\$fbtxt1[1] = "Incorrect"
\$fbtxt1[2] = "Incorrect."
\$fbtxt1[3] = "Incorrect"

\$fb1 = ohm_getfeedbacktxt(\$stuanswers[\$thisq], \$fbtxt1, \$ans1, 1)

//=== essay part ===

\$fbtxt2 = "You essay, therefore you are"

// no answer set for essays

\$fb2 = ohm_getfeedbacktxtessay(\$stuanswers[\$thisq][2], \$fbtxt2, 2)

//=== matching part ===

// Matching currently cannot be scored via the Question API in a multi-part question.

\$questions[3] = array("Step 1", "Step 2","Step 3")
\$displayformat[3] = "select"

\$ans3 = array("1","2","3")

\$fb3 = ohm_getfeedbackbasic(\$stuanswers[\$thisq], \$fbcorrect, \$fbincorrect, \$ans3, 3)

//=== multi-answer part ===

\$questions[4] = ["60% of product folks are under-caffeinated","Statistics is silly","10% of people do not like candy","I was told there would be no math","95% of campers are happy","4 out of 5 sloths say disco is their favorite music genre"]

\$ans4 = "0,2,4,5"

\$scoremethod[4] = "answers"

\$fb4 = ohm_getfeedbackbasic(\$stuanswers[\$thisq], \$fbcorrect, \$fbincorrect, \$ans4, 4)

//=== number part ===

\$ans5 = 1

\$fb5 = ohm_getfeedbackbasic(\$stuanswers[\$thisq], \$fbcorrect, \$fbincorrect, \$ans5, 5)

//=== number function part ===

\$ans6 = 9

\$partial = "9,1,8,.5"

\$fbtxt6 = array("Correct answer feedback!","Partial score feedback!")

\$fb6 = ohm_getfeedbacktxtnumfunc(\$stuanswers[\$thisq], \$partial, \$fbtxt6, \$fbincorrect, 'x', '', '.001', '-10,10', 6)

//=== string part ===

\$ans7 = "A,B,C,D,E,F"

\$fb7 = ohm_getfeedbackbasic(\$stuanswers[\$thisq], \$fbcorrect, \$fbincorrect, \$ans7, 7)

// Build final answer and feedback arrays

\$answer = array(\$ans0, \$ans1, \$ans2, \$ans3, \$ans4, \$ans5, \$ans6, \$ans7)

\$feedback = mergearrays(\$fb0, \$fb1, \$fb2, \$fb3, \$fb4, \$fb5, \$fb6, \$fb7)
END_OF_STRING,
        'qcontrol' => '',
        'qtext' => <<<END_OF_STRING
<p>What is the distance between the numbers -200 and 100?</p>
\$answerbox[0]


<p>What is 10 * 10?</p>
\$answerbox[1]

<p>How are you feeling today?</p>
\$answerbox[2]

<p>What is the correct order of these steps?</p>
\$answerbox[3]

<p>Select the questions with valid statistics.</p>
\$answerbox[4]

<p>What is 10 / 10?</p>
\$answerbox[5]

<p>If `10x = 90`, solve for `x`.</p>
\$answerbox[6]

<p>List all the letters from A to F as a comma-separated list.</p>
\$answerbox[7]
END_OF_STRING,
        'answer' => '',
        'solution' => '',
        'extref' => '',
        'hasimg' => '0',
        'deleted' => '0',
        'avgtime' => '0',
        'ancestors' => '',
        'ancestorauthors' => '',
        'otherattribution' => '',
        'importuid' => '',
        'replaceby' => '0',
        'broken' => '0',
        'solutionopts' => '6',
        'sourceinstall' => '',
        'meantimen' => '0',
        'meantime' => '0',
        'vartime' => '0',
        'meanscoren' => '0',
        'meanscore' => '0',
        'varscore' => '0',
        'external_id' => NULL,
        'isrand' => '0'
    ];

    // A result set with two questions: a single part and a multi-part.
    const RESULTSET_WITH_TWO_QUESTIONS = [
        self::SINGLE_QUESTION_MULTIPART_ALL_TYPES,
        [
            'id' => '3261', // id
            'uniqueid' => '1712618134706342',
            'adddate' => '1712613994',
            'lastmoddate' => '1724438304',
            'ownerid' => '1',
            'author' => 'Lastname, Firstname',
            'userights' => '0',
            'license' => '1',
            'description' => 'A simple number question.',
            'qtype' => 'number',
            'control' => '$answer = 11',
            'qcontrol' => '',
            'qtext' => '<p>What is 1 + 1? $answerbox</p>',
            'answer' => '',
            'solution' => '',
            'extref' => '',
            'hasimg' => '0',
            'deleted' => '0',
            'avgtime' => '0',
            'ancestors' => '',
            'ancestorauthors' => '',
            'otherattribution' => '',
            'importuid' => '',
            'replaceby' => '0',
            'broken' => '0',
            'solutionopts' => '6',
            'sourceinstall' => '',
            'meantimen' => '0',
            'meantime' => '0',
            'vartime' => '0',
            'meanscoren' => '0',
            'meanscore' => '0',
            'varscore' => '0',
            'external_id' => NULL,
            'isrand' => '0'
        ]
    ];

}