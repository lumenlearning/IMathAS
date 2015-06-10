<?php
use yii\helpers\Html;
use app\components\AppUtility;

$this->title = 'Send Mass Message';
$this->params['breadcrumbs'][] = ['label' => $course->name, 'url' => ['/instructor/instructor/index?cid=' . $course->id]];
$this->params['breadcrumbs'][] = ['label' => 'List Students', 'url' => ['/roster/roster/student-roster?cid='.$course->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<form action="roster-message" method="post" id="roster-form">
<?php echo $this->render('../../instructor/instructor/_toolbarTeacher'); ?>
<div class="student-roster-message">
    <input type="hidden" name="isMessage" value="1"/>
    <input type="hidden" name="courseid" value="<?php echo $course->id ?>"/>
    <input type="hidden" name="studentInformation" value='<?php echo $studentDetails ?>'/>
    <h2><b>Send Mass Message</b></h2>
    <div>
        <span class="col-md-2"><b>Subject</b></span>
        <span class="col-md-8"><?php echo '<input class="textbox subject form-control" type="text" name="subject">'; ?></span>
    </div>
    <br><br>
    <div class="gb">
        <span class="col-md-2"><b>Message</b></span>
        <?php echo "<span class='left col-md-10'><div class= 'editor'>
        <textarea id='message' name='message' style='width: 100%;' rows='20' cols='200'>"; echo "</textarea></div></span><br>"; ?>
    </div>
    <p class="col-md-2"></p>
    <p class="col-md-10"><i><br>Note:</i> <b>FirstName</b> and <b>LastName</b> can be used as form-mail fields that will autofill with each student's first/last name</p>
    <div>
        <span class="col-md-2"><b>Send copy to</b></span>
        <span class="col-md-10">
            <input type="radio" name="messageCopyToSend" id="self" value="onlyStudents"> Only Students<br>
            <input type="radio" name="messageCopyToSend" id="self" value="selfAndStudents" checked="checked"> Students and you<br>
            <input type="radio" name="messageCopyToSend" id="self" value="teachersAndStudents"> Students and all instructors of this course</span>
    </div>
    <div >
        <span class="col-md-2 select-text-margin"><b>Save sent messages?</b></span>
        <span class="col-md-10 select-text-margin"><input type="checkbox" name="isChecked" id="save-sent-message" checked="true"></span>
    </div>
    <div>
    <span class="col-md-2 select-text-margin"><b>Limit send </b></span>
    <span class="roster-assessment ">
	 <p class="col-md-3">To students who haven't completed</p>
	  <select name="roster-assessment-data" id="roster-data" class="col-md-4 select-text-margin">
          <option value='0'>Don't limit - send to all</option>;
          <?php foreach ($assessments as $assessment) { ?>
          <option value="<?php echo $assessment->id ?>">
              <?php echo ucfirst($assessment->name);?>
              </option><?php } ?>
      </select>
    </span>
    </div>
    <div class=" col-lg-offset-2 col-md-10"><br>
        <input type="submit" class="btn btn-primary " id="email-button" value="Send Message" style="margin-left: 0px">
    </div>
    <div>
        <span><p class="col-md-3"><br>Unless limited, message will be sent to:</p></span>
       <span class="col-md-12"><?php foreach (unserialize($studentDetails) as $studentDetail) { ?>
               <?php echo "<li>".ucfirst($studentDetail['LastName']).", ". ucfirst($studentDetail['FirstName'])." (". ($studentDetail['SID']).")</li>" ?>
           <?php } ?>
        </span>
    </div>
</div>
</form>