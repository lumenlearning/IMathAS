<?php
use yii\helpers\Html;
use app\components\AppUtility;
$this->title = 'New Message';
$this->params['breadcrumbs'][] = ['label' => 'Course', 'url' => ['/instructor/instructor/index?cid='.$_GET['cid']]];
$this->params['breadcrumbs'][] = ['label' => 'Messages', 'url' => ['/message/message/index?cid='.$_GET['cid']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo $this->render('../../instructor/instructor/_toolbarTeacher'); ?>

<div class="">
    <h2><b>New Message</b></h2>
    <input type="hidden" class="send-msg" value="<?php echo $course->id ?>">
    <div class="drop-down">
        <span class="col-md-2"><b>To</b></span>
        <span class="col-md-8">
        <select name="seluid" class="dropdown form-control" id="seluid">
            <option value="0">Select a user..</option>
            <?php foreach ($teachers as $teacher) { ?>
            <option value="<?php echo $teacher->user->id ?>">

                <?php echo ucfirst($teacher->user->FirstName) . " " . ucfirst($teacher->user->LastName); ?>
            </option><?php } ?>
        </select>
        </span>
    </div>
    <br><br><br>

    <div>
        <span class="col-md-2"><b>Subject</b></span>
        <span class="col-md-8"><?php echo '<input class="textbox subject" type="text">'; ?></span>
    </div>
    <br><br><br>

    <div>
        <span class="col-md-1"><b>Message</b></span>
        <?php echo "<span class='left col-md-11'><div class= 'editor'>
        <textarea id='message' name='message' style='width: 100%;' rows='20' cols='200'>";
        echo "</textarea></div></span><br>"; ?>
    </div>

    <div class="col-lg-offset-2 col-md-8">
        <br>
        <a class="btn btn-primary" id="mess">Send Message</a>
    </div>
</div>