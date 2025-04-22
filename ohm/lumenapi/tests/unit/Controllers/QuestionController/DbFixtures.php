<?php

namespace Tests\Unit\Controllers\QuestionController;

class DbFixtures
{
    const imas_QuestionSet_dbRow_number = [
        'id' => '42',
        'uniqueid' => '1491933600157156',
        'adddate' => '1491933600',
        'lastmoddate' => '1491933931',
        'ownerid' => '1',
        'author' => 'Mad Hatter',
        'userights' => '0',
        'license' => '1',
        'description' => '🙃',
        'qtype' => 'number',
        'control' => '$a = rand(1,10);' . "\r\n" . '$answer = $a;',
        'qcontrol' => '',
        'qtext' => 'Why is a raven like a writing desk?',
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
        'varscore' => '1111',
        'external_id' => NULL,
        'isrand' => '1',
    ];

    const imas_QuestionSet_dbRow_choices = [
        'id' => '3607',
        'uniqueid' => '1661894316883503',
        'adddate' => '1661894316',
        'lastmoddate' => '1661990728',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '2',
        'license' => '1',
        'description' => 'Multiple Choice Test 1 with Feedback',
        'qtype' => 'choices',
        'control' => 'loadlibrary("ohm_macros")
 
 $questions[0] = "Sportsball"
 $feedbacktxt[0] = "This is correct. Way to go."
 $questions[1] = "Blernsball"
 $feedbacktxt[1] = "Sorry, Option B is incorrect. Try again."
 $questions[2] = "Calvin Ball"
 $feedbacktxt[2] = "Sorry, Option C is not the right answer. Try again."
 $questions[3] = "Quidditch"
 $feedbacktxt[3] = "Sorry, Option D was the wrong choice. Try again."
 $displayformat = "vert"
 $answer = 0
 
 $feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer)',
        'qcontrol' => '',
        'qtext' => '<p>What is your favorite sport?</p>\r\n<p>$answerbox</p>\r\n',
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
        'isrand' => '0',
    ];

    const imas_QuestionSet_dbRow_multipart_choices = [
        'id' => '3609',
        'uniqueid' => '1665009644605959',
        'adddate' => '1665009644',
        'lastmoddate' => '1665018423',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userrights' => '0',
        'license' => '1',
        'description' => 'Multipart, multiple inputs per part',
        'qtype' => 'multipart',
        'control' => 'loadlibrary("ohm_macros")

$anstypes = "choices,choices,choices,number"

// Part 1 - Choose the best color
$choices[0] = array("Purple", "Red", "Blue", "Green")
$answer[0] = "0"
$colorfeedbacks[0] = "Excellent choice."
$colorfeedbacks[1] = "Nope."
$colorfeedbacks[2] = "Try again."
$colorfeedbacks[3] = "Not even close."

// Part 2 - Cup storage. Are you an upper or a downer?
$choices[1] = array("Up", "Down", "Sideways")
$answer[1] = "2"
$numbersfeedbacks[0] = "Nope!"
$numbersfeedbacks[1] = "No."
$numbersfeedbacks[2] = "Correct."

// Part 3 - Is Hawaiian a valid pizza option?
$choices[2] = array("Yes", "No", "Sometimes", "Only on Tuesdays")
$answer[2] = "0"
$pizzafeedbacks = array(
  "Correct. Hawaiian pizza is always valid pizza.",
  "Incorrect.",
  "Try again.",
  "All days are acceptable pizza days."
)

// Part 4 - What is the answer to life, the universe, and everything?
$answer[3] = "42"

$feedback = mergearrays(
  ohm_getfeedbacktxt($thisq, $colorfeedbacks, $answer[0], 0),
  ohm_getfeedbacktxt($thisq, $numbersfeedbacks, $answer[1], 1),
  ohm_getfeedbacktxt($thisq, $pizzafeedbacks, $answer[2], 2),
  ohm_getfeedbackbasic($thisq, "Correct!", "Not correct.", $answer[3], 3)
)',
        'qcontrol' => '',
        'qtext' => 'Choose the best color:
$answerbox[0]

Cup storage. Are you an upper or a downer?
$answerbox[1]

Is Hawaiian a valid pizza option?
$answerbox[2]

What is the answer to life, the universe, and everything?
$answerbox[3]
',
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
        'isrand' => '0',
    ];

    const imas_QuestionSet_dbRow_multipart_multans = [
        'id' => '3618',
        'uniqueid' => '1668057407498824',
        'adddate' => '1668057407',
        'lastmoddate' => '1668066745',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '0',
        'license' => '1',
        'description' => 'Multipart: multans + number',
        'qtype' => 'multipart',
        'control' => 'loadlibrary("ohm_macros")

$anstypes = "number,multans"

$choices = [
  "Correct",
  "Not correct",
  "Correct",
  "Not correct",
  "Correct"
]

// Both $answer and $answers are declared here, for testing.
$answer[0] = 42
$answers[1] = "0,2,4"

$multansFeedbacks = array(
  "You chose well.",
  "Nope.",
  "You chose correctly.",
  "lol, no.",
  "This is correct."
)

$feedback = mergearrays(
  ohm_getfeedbackbasic($stuanswers[$thisq], "Good answer.", "Wrong answer.", $answer[0], 0),
  ohm_getfeedbacktxtmultans($stuanswers[$thisq], $multansFeedbacks, $answers[1], 1)
)
',
        'qcontrol' => '',
        'qtext' => 'What is the answer to life, the universe, and everything?
$answerbox[0]

Choose the correct answers:
$answerbox[1]
',
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
        'isrand' => '0',
    ];

    const imas_QuestionSet_dbRow_with_ohm1_macro = [
        'id' => '3607',
        'uniqueid' => '1661894316883503',
        'adddate' => '1661894316',
        'lastmoddate' => '1661990728',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '2',
        'license' => '1',
        'description' => 'Multiple Choice Test 10 with Feedback - Staging QID 638',
        'qtype' => 'choices',
        'control' => '$questions[0] = "Something like that."
$feedbacktxt[0] = "This is correct. Way to go."
$questions[1] = "No, sorry. I\'ll add an oxford comma next time."
$feedbacktxt[1] = "Sorry, this is incorrect. Try again."
$questions[2] = "I\'m not the planning committee."
$feedbacktxt[2] = "Sorry, not the right answer. Try again."
$questions[3] = "You were supposed to make the plan!"
$feedbacktxt[3] = "Sorry, that was the wrong choice. Try again."
$displayformat = "vert"
$noshuffle = "all"
$answer = 0

$feedback = getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer)',
        'qcontrol' => '',
        'qtext' => '<p>Was that the plan?</p>
<p>$answerbox</p>
<p>$feedback</p>',
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
        'isrand' => '0',
    ];

    const imas_QuestionSet_dbRow_multans_basicfeedback = [
        'id' => '3623',
        'uniqueid' => '1670955967303564',
        'adddate' => '1670955967',
        'lastmoddate' => '1671133493',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '0',
        'license' => '1',
        'description' => '1.3 L1 - QID 646 in staging',
        'qtype' => 'multans',
        'control' => 'loadlibrary("ohm_macros")

$a = "0 - How tall is the tallest mountain in the United States?"
$b = "1 - Do standing heart rates tend to be higher than sitting heartrates?"
$c = "2 - What is the sum of all the whole numbers between 0 and 10?"
$d = "3 - What is your favorite subject in school?"
$e = "4 - What proportion of college students live on campus?"
$f = "5 - How many members does your household have (including pets)?"

$questions = array($a,$b,$c,$d,$e,$f)
$answers = "1,4"

$hints[1] = "Remember that all statistical investigative questions anticipate variability and could lead to data collection and analysis."

$feedback = ohm_getfeedbackbasic($stuanswers[$thisq], "Excellent! You are able to distinguish the statstical investigative questions from the rest.", "A statistical investigative question would require data collection and analysis. Does the question account for variability?  Questions with a single mathematical answer are not considered statistical investigative questions.", $answers)

// As of AST-275, using ohm_getfeedbackbasic or feedbacktxt in a multans
// type question requires shuffling to be disabled.
$noshuffle = "all"

$hinttext[0] = "Remember that all statistical investigative questions anticipate variability and could lead to data collection and analysis."

$hinttext_a=forminlinebutton("Hint",$hinttext[0])
',
        'qcontrol' => '',
        'qtext' => 'Which of the following are statistical investigative questions? <em>There may be more than one correct answer.</em>
<p>$hinttext_a
  $answerbox
  $feedback
  $hintloc
',
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
        'isrand' => '0',
    ];
}
