<?php

namespace Tests\fixtures\Services;

class QuestionServiceFixtures
{

    // This is an example associative array returned by QuestionService->getQuestionsWithAnswers.
    // Generated from actual method return data and copy/pasted using var_export().
    const QUESTIONS_WITH_ANSWERS = array(
        0 =>
            array(
                'questionSetId' => 5485,
                'ohmUniqueId' => '1gljk2clg9u',
                'questionType' => 'multipart',
                'seed' => 1234,
                'html' => '<div class="question" role=region aria-label="Question">
<p>What is the distance between the numbers -200 and 100?</p>
<input type="text" size="20" name="qn1000" id="qn1000" value="" autocomplete="off" aria-label="Question 1 Part 1 of 8" class="text" /><button type=button class=btn id="pbtn1000">Preview <span class="sr-only">Question 1 Part 1 of 8</span></button> &nbsp;<span id=p1000></span> <br/><br/>

<p>What is 10 * 10?</p>
<div  class="clearfix"  id="qnwrap1001" style="display:block" role=radiogroup aria-label="Question 1 Part 2 of 8"><ul class=nomark><li><input class="unind" type=radio name=qn1001 value=0 id="qn1001-0"  /><label for="qn1001-0">100</label></li> 
<li><input class="unind" type=radio name=qn1001 value=1 id="qn1001-1"  /><label for="qn1001-1">Infinity</label></li> 
<li><input class="unind" type=radio name=qn1001 value=2 id="qn1001-2"  /><label for="qn1001-2">0</label></li> 
<li><input class="unind" type=radio name=qn1001 value=3 id="qn1001-3"  /><label for="qn1001-3">I was told there would be no math</label></li> 
</ul>
</div><br/><br/>
<p>How are you feeling today?</p>
<textarea rows="5" name="qn1002" id="qn1002" cols="50" aria-label="Question 1 Part 3 of 8" ></textarea>
<br/><br/>
<p>What is the correct order of these steps?</p>
<div id="qnwrap1003" role="group" aria-label="Question 1 Part 4 of 8"><div class="match" >
<ul class="nomark">
<li class="nowrap"><select name="qn1003-0" id="qn1003-0"><option value="-" selected="1">-</option><option value="0" >1</option>
<option value="1" >2</option>
<option value="2" >3</option>
</select>&nbsp;<label for="qn1003-0">Step 1</label></li>
<li class="nowrap"><select name="qn1003-1" id="qn1003-1"><option value="-" selected="1">-</option><option value="0" >1</option>
<option value="1" >2</option>
<option value="2" >3</option>
</select>&nbsp;<label for="qn1003-1">Step 2</label></li>
<li class="nowrap"><select name="qn1003-2" id="qn1003-2"><option value="-" selected="1">-</option><option value="0" >1</option>
<option value="1" >2</option>
<option value="2" >3</option>
</select>&nbsp;<label for="qn1003-2">Step 3</label></li>
</ul>
</div><div class=spacer>&nbsp;</div></div><br/><br/>
<p>Select the questions with valid statistics.</p>
<div  class="clearfix"   id="qnwrap1004" style="display:block" role=group aria-label="Question 1 Part 5 of 8 Select one or more answers"><ul class=nomark><li><input class="unind" type=checkbox name="qn1004[0]" value=0 id="qn1004-0"  /><label for="qn1004-0">60% of product folks are under-caffeinated</label></li> 
<li><input class="unind" type=checkbox name="qn1004[1]" value=1 id="qn1004-1"  /><label for="qn1004-1">Statistics is silly</label></li> 
<li><input class="unind" type=checkbox name="qn1004[2]" value=2 id="qn1004-2"  /><label for="qn1004-2">10% of people do not like candy</label></li> 
<li><input class="unind" type=checkbox name="qn1004[3]" value=3 id="qn1004-3"  /><label for="qn1004-3">I was told there would be no math</label></li> 
<li><input class="unind" type=checkbox name="qn1004[4]" value=4 id="qn1004-4"  /><label for="qn1004-4">95% of campers are happy</label></li> 
<li><input class="unind" type=checkbox name="qn1004[5]" value=5 id="qn1004-5"  /><label for="qn1004-5">4 out of 5 sloths say disco is their favorite music genre</label></li> 
</ul>
</div><br/><br/>
<p>What is 10 / 10?</p>
<input type="text" size="20" name="qn1005" id="qn1005" value="" autocomplete="off" aria-label="Question 1 Part 6 of 8" class="text" /><span id=p1005></span><br/><br/>
<p>If `10x = 90`, solve for `x`.</p>
<input type="text" size="20" name="qn1006" id="qn1006" value="" autocomplete="off" aria-label="Question 1 Part 7 of 8" class="text" /><button type=button class=btn id="pbtn1006">Preview <span class="sr-only">Question 1 Part 7 of 8</span></button> &nbsp;<span id=p1006></span>
<br/><br/>
<p>List all the letters from A to F as a comma-separated list.</p>
<input type="text" size="20" name="qn1007" id="qn1007" value="" autocomplete="off" aria-label="Question 1 Part 8 of 8" class="text" />
</div>
',
                'jsParams' =>
                    array(
                        1000 =>
                            array(
                                'tip' => 'Enter a mathematical expression',
                                'longtip' => 'Enter your answer as a number (like 5, -3, 2.2172) or as a calculation (like 5/3, 2^3, 5+4)<br/>Enter DNE for Does Not Exist, oo for Infinity',
                                'preview' => 2,
                                'calcformat' => '',
                                'qtype' => 'calculated',
                            ),
                        1001 =>
                            array(
                                'qtype' => 'choices',
                            ),
                        1002 =>
                            array(
                                'qtype' => 'essay',
                            ),
                        1003 =>
                            array(
                                'qtype' => 'matching',
                            ),
                        1004 =>
                            array(
                                'qtype' => 'multans',
                            ),
                        1005 =>
                            array(
                                'calcformat' => '',
                                'tip' => 'Enter an integer or decimal number',
                                'longtip' => 'Enter your answer as an integer or decimal number.  Examples: 3, -4, 5.5172<br/>Enter DNE for Does Not Exist, oo for Infinity',
                                'qtype' => 'number',
                            ),
                        1006 =>
                            array(
                                'tip' => 'Enter an algebraic expression',
                                'longtip' => 'Enter your answer as an expression.  Example: 3x^2+1, x/5, (a+b)/c
<br/>Be sure your variables match those in the question',
                                'preview' => 2,
                                'calcformat' => '',
                                'vars' =>
                                    array(
                                        0 => 'x',
                                    ),
                                'fvars' =>
                                    array(),
                                'domain' =>
                                    array(
                                        0 =>
                                            array(
                                                0 => -10.0,
                                                1 => 10.0,
                                                2 => false,
                                            ),
                                    ),
                                'qtype' => 'numfunc',
                            ),
                        1007 =>
                            array(
                                'tip' => 'Enter text',
                                'longtip' => 'Enter your answer as letters.  Examples: A B C, linear, a cat',
                                'calcformat' => '',
                                'qtype' => 'string',
                            ),
                    ),
                'correctAnswers' =>
                    array(
                        0 => 300,
                        1 => '0',
                        2 => NULL,
                        3 =>
                            array(
                                0 => '1',
                                1 => '2',
                                2 => '3',
                            ),
                        4 => '0,2,4,5',
                        5 => 1,
                        6 => 9,
                        7 => 'A,B,C,D,E,F',
                    ),
                'showAnswerText' =>
                    array(
                        0 => '300',
                        1 => '100',
                        2 => '',
                        3 => '<br/>1<br/>2<br/>3',
                        4 => '<br/>60% of product folks are under-caffeinated<br/>10% of people do not like candy<br/>95% of campers are happy<br/>4 out of 5 sloths say disco is their favorite music genre',
                        5 => '1',
                        6 => '`9`',
                        7 => 'A,B,C,D,E,F',
                    ),
                'uniqueid' => '1712613994709310',
                'feedback' =>
                    array(
                        'qn1001-0' =>
                            array(
                                'correctness' => 'correct',
                                'feedback' => 'This is correct. Way to go.',
                            ),
                        'qn1001-1' =>
                            array(
                                'correctness' => 'incorrect',
                                'feedback' => 'Incorrect',
                            ),
                        'qn1001-2' =>
                            array(
                                'correctness' => 'incorrect',
                                'feedback' => 'Incorrect.',
                            ),
                        'qn1001-3' =>
                            array(
                                'correctness' => 'incorrect',
                                'feedback' => 'Incorrect',
                            ),
                    ),
                'errors' =>
                    array(),
                'partTypes' =>
                    array(
                        0 => 'calculated',
                        1 => 'choices',
                        2 => 'essay',
                        3 => 'matching',
                        4 => 'multans',
                        5 => 'number',
                        6 => 'numfunc',
                        7 => 'string',
                    ),
                'answerDataByQnIdentifier' =>
                    array(
                        'qn1000' =>
                            array(
                                'questionType' => 'calculated',
                                'correctAnswer' => 300,
                                'showAnswerText' => '300',
                            ),
                        'qn1001' =>
                            array(
                                'questionType' => 'choices',
                                'correctAnswer' => '0',
                                'showAnswerText' => '100',
                            ),
                        'qn1002' =>
                            array(
                                'questionType' => 'essay',
                                'correctAnswer' => NULL,
                                'showAnswerText' => '',
                            ),
                        'qn1003' =>
                            array(
                                'questionType' => 'matching',
                                'correctAnswer' =>
                                    array(
                                        0 => '1',
                                        1 => '2',
                                        2 => '3',
                                    ),
                                'showAnswerText' => '<br/>1<br/>2<br/>3',
                            ),
                        'qn1004' =>
                            array(
                                'questionType' => 'multans',
                                'correctAnswer' => '0,2,4,5',
                                'showAnswerText' => '<br/>60% of product folks are under-caffeinated<br/>10% of people do not like candy<br/>95% of campers are happy<br/>4 out of 5 sloths say disco is their favorite music genre',
                            ),
                        'qn1005' =>
                            array(
                                'questionType' => 'number',
                                'correctAnswer' => 1,
                                'showAnswerText' => '1',
                            ),
                        'qn1006' =>
                            array(
                                'questionType' => 'numfunc',
                                'correctAnswer' => 9,
                                'showAnswerText' => '`9`',
                            ),
                        'qn1007' =>
                            array(
                                'questionType' => 'string',
                                'correctAnswer' => 'A,B,C,D,E,F',
                                'showAnswerText' => 'A,B,C,D,E,F',
                            ),
                    ),
            ),
        1 =>
            array(
                'questionSetId' => 3261,
                'ohmUniqueId' => '1gljntos656',
                'questionType' => 'number',
                'seed' => 4321,
                'html' => '<div class="question" role=region aria-label="Question">
<p>What is 1 + 1? <input type="text" size="20" name="qn0" id="qn0" value="" autocomplete="off" aria-label="Question 1" class="text" /><span id=p0></span></p>
</div>
',
                'jsParams' =>
                    array(
                        0 =>
                            array(
                                'calcformat' => '',
                                'tip' => 'Enter an integer or decimal number',
                                'longtip' => 'Enter your answer as an integer or decimal number.  Examples: 3, -4, 5.5172<br/>Enter DNE for Does Not Exist, oo for Infinity',
                                'qtype' => 'number',
                            ),
                    ),
                'correctAnswers' =>
                    array(
                        0 => 11,
                    ),
                'showAnswerText' =>
                    array(
                        0 => '11',
                    ),
                'uniqueid' => '1712618134706342',
                'feedback' => NULL,
                'errors' =>
                    array(),
                'answerDataByQnIdentifier' =>
                    array(
                        'qn0' =>
                            array(
                                'questionType' => 'number',
                                'correctAnswer' =>
                                    array(
                                        0 => 11,
                                    ),
                                'showAnswerText' =>
                                    array(
                                        0 => '11',
                                    ),
                            ),
                    ),
            ),
    );

}