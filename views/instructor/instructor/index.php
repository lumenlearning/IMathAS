<?php
use app\components\AppUtility;

$this->title = ucfirst($course->name);
$this->params['breadcrumbs'][] = $this->title;
?>
<link href='<?php echo AppUtility::getHomeURL(); ?>css/course/course.css?<?php echo time(); ?>' rel='stylesheet' type='text/css'>
<link href='<?php echo AppUtility::getHomeURL() ?>css/fullcalendar.print.css' rel='stylesheet' media='print'/>

<!--<div class="mainbody">-->
<input type="hidden" class="courseId" value="<?php echo $course->id?>">
<div class="item-detail-header">
    <?php echo $this->render("../../itemHeader/_indexWithButton",['item_name'=>'Course Setting', 'link_title'=>'Home', 'link_url' => AppUtility::getHomeURL().'site/index', 'page_title' => $this->title]); ?>
</div>

<div class="item-detail-content">
    <?php echo $this->render("_toolbarTeacher", ['course' => $course, 'section' => 'course']);?>
</div>

<div class="tab-content shadowBox">
    <div class="row course-copy-export">
        <div class="col-md-1 course-top-menu">
            <a href="#">Copy All</a>
        </div>
        <div class="col-md-2 course-top-menu">
            <a href="#">Export Course</a>
        </div>
    </div>
    <div class="clear-both"></div>
    <div class=" row add-item">
        <div class="col-md-1 plus-icon">
        <i class="fa fa-plus fa-2x"></i>
    </div>
        <div class=" col-md-2 add-item-text">
            <p>Add An Item...</p>
        </div>
    </div>
   <?php if(count($courseDetail)){
    foreach($courseDetail as $key => $item){
        switch(key($item)):
   case 'Assessment': ?>
       <?php $assessment = $item[key($item)];
      ?>
        <input type="hidden" class="assessment-link" value="<?php echo $assessment->id?>">

       <?php break?>
   <?php endswitch;?>
    <?php }?>

   <?php }?>

<br><br><br><br><br>
    <h1>Comming Soon....</h1>
    <br><br><br><br><br>
</div>