<?php
use app\components\AppUtility;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use app\components\AppConstant;

$this->title = 'Manage Exception';
$this->params['breadcrumbs'][] = ['label' => ucfirst($course->name), 'url' => ['/instructor/instructor/index?cid=' . $course->id]];
if($gradebook == AppConstant::NUMERIC_ONE){
    $this->params['breadcrumbs'][] = ['label' => 'Gradebook', 'url' => ['/gradebook/gradebook/gradebook?cid='.$course->id]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'Roster', 'url' => ['/roster/roster/student-roster?cid='.$course->id]];
}
$this->params['breadcrumbs'][] = $this->title;
echo $this->render('../../instructor/instructor/_toolbarTeacher', ['course' => $course]);
?>
<div id="headermassexception" class="pagetitle"><h2>Manage Exceptions</h2></div>
<?php if($gradebook == AppConstant::NUMERIC_ONE){?>
    <form action="make-exception?cid=<?php echo $course->id ?>&gradebook=1" method="post" id="roster-form">
<?php }else {?>
<form action="make-exception?cid=<?php echo $course->id ?>" method="post" id="roster-form">
<?php } ?>


    <div class="student-roster-exception">
        <input type="hidden" name="isException" value="1"/>
        <input type="hidden" name="gradebook" value="<?php echo $gradebook ?>"/>
        <input type="hidden" name="courseid" value="<?php echo $course->id ?>"/>
        <input type="hidden" name="studentInformation" value='<?php echo $studentDetails ?>'/>
        <input type="hidden" name="section" value='<?php echo $section ?>'/>
        <div><div>
            <?php  if(sizeof((unserialize($studentDetails))) == 1){
                foreach (unserialize($studentDetails) as $studentDetail) {
                    echo "<h3 class='name pull-left'>".ucfirst($studentDetail['LastName']).", ". ucfirst($studentDetail['FirstName']);
                    if($section != "" && $section != 'null')
                        echo "</h3><h4 class='pull-left margin-zero'>&nbsp;(section: ".$section.")</h4>";
                    else{
                        echo " </h3>";
                    }
                }
            }
            ?>
            </div>
            <br class="form">
            <?php
                if($existingExceptions){
                echo " <div><h4>Existing Exceptions</h4><p>Select exceptions to clear</p>"
            ?><div class='exception-list'><pre>
                <?php
                    foreach($existingExceptions as $entry){
                        echo "<ul><li><b>".$entry['Name']."</b><ul>";
                            foreach($entry['assessments'] as $singleAssessment){
                                echo "<li><input type='checkbox' name='clears[]' value='{$singleAssessment['exceptionId']}'>".' '."{$singleAssessment['assessmentName']}".' ('."{$singleAssessment['exceptionDate']}".')';
                                    if ($singleAssessment['waiveReqScore']==1) {
                                        echo ' <i>('._('waives prereq').')</i>';
                                    }
                                echo "</li>";
                            }
                        echo "</ul></li>";
                ?>
            <?php echo "</ul>"; } echo "</pre></div></div><input type='submit'  class='btn btn-primary record-submit clear-exception' id='change-record' value='Record Changes'>";}
            else{
                echo"<div class='alert alert-danger'><p>No exceptions currently exist for the selected students.</p></div>";
            }?>
        </div>
        <div>
            <h4>Make New Exception</h4>
            <span class="form select-text-margin">Available After:</span>

              <div class="col-lg-3 pull-left" id="datePicker-id1" >
                  <?php
                     echo DatePicker::widget([
                          'name' => 'startDate',
                          'options' => ['placeholder' => 'Select start date ...'],
                          'type' => DatePicker::TYPE_COMPONENT_APPEND,
                          'value' => date("m/d/Y"),
                          'pluginOptions' => [
                          'autoclose' => true,
                          'format' => 'mm/dd/yyyy' ]
                     ]);
                  ?>
              </div><div class="end pull-left select-text-margin right">at</div>
            <div class="col-lg-4" id="timepicker-id" >
                <?php
                    echo TimePicker::widget([
                        'name' => 'startTime',
                        'options' => ['placeholder' => 'Select start time ...'],
                        'convertFormat' => true,
                        'value' => date('g:i A'),
                        'pluginOptions' => [
                            'format' => "m/d/Y g:i A",
                            'todayHighlight' => true,
                        ]
                    ]);
                ?>
            </div>
            <br class="form">
            <span class="form select-text-margin">Available Until:</span>
            <div class="col-lg-3 pull-left" id="datePicker-id2" >
                <?php
                    echo DatePicker::widget([
                         'name' => 'endDate',
                         'options' => ['placeholder' => 'Select end date ...'],
                         'type' => DatePicker::TYPE_COMPONENT_APPEND,
                         'value' => date("m/d/Y",strtotime("+1 week")),
                         'pluginOptions' => [
                              'autoclose' => true,
                              'format' => 'mm/dd/yyyy' ]
                         ]);
                ?>
                </div><div class="end pull-left select-text-margin right">at</div>
                <div class="col-lg-4" id="timepicker-id1" >
                <?php
                    echo TimePicker::widget([
                        'name' => 'endTime',
                        'options' => ['placeholder' => 'Select time ...'],
                        'convertFormat' => true,
                        'value' =>"10:00 AM",
                        'pluginOptions' => [
                            'format' => "m/d/Y g:i A",
                            'todayHighlight' => true,
                        ]
                    ]);
                ?>
            </div>
            </span>
            <br class="form">
            <p>Set Exception for assessments:</p>
            <ul  class='assessment-list'>
                <?php foreach ($assessments as $assessment) { ?>
                <?php echo "<li><input type='checkbox' name='addExc[]' value='{$assessment->id}'>".' '. ucfirst($assessment->name)."</li>";?>
                <?php } ?>
            </ul>
            <input type="submit" class="btn btn-primary record-submit create-exception" id="change-record" value="Record Changes">
            <?php if($gradebook == AppConstant::NUMERIC_ONE){?>
                <a class="btn btn-primary back-btn" href="<?php echo AppUtility::getURLFromHome('gradebook/gradebook', 'gradebook?cid='.$course->id)  ?>">Back</a>
            <?php }else {?>
                <a class="btn btn-primary back-btn" href="<?php echo AppUtility::getURLFromHome('roster/roster', 'student-roster?cid='.$course->id)  ?>">Back</a>
            <?php } ?>
        </div>
        <br>
        <div>
            <p><input type="checkbox" name="forceReGen"> Force student to work on new versions of all questions? Students will keep any scores earned, but must work new versions of questions to improve score.</p>
            <p><input type="checkbox" name="forceClear"> Clear student's attempts?  Students will <b>not</b>  keep any scores earned, and must rework all problems.</p>
            <p><input type="checkbox" name="eatLatePass"> Deduct <input type="input" name="latePassN" size="1" value="1">  LatePass(es) from each student.<?php echo $latePassMsg?></p>
            <p><input type="checkbox" name="waiveReqScore"> Waive "show based on an another assessment" requirements, if applicable.</p>
            <p><input type="checkbox" name="sendMsg"> Send message to these students?</p>
        </div>
        <div>
            <span><p><h4>Students Selected:</h4></span><ul>
            <span class="col-md-12"><?php foreach (unserialize($studentDetails) as $studentDetail) { ?>
               <?php echo "<li>".ucfirst($studentDetail['LastName']).",". ucfirst($studentDetail['FirstName'])." (". ($studentDetail['SID']).")</li>" ?>
           <?php } ?></ul>
        </span>
        </div>
    </div>
</form>