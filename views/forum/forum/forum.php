<?php
use app\components\AppUtility;
use app\components\AssessmentUtility;
use app\components\CourseItemsUtility;
use app\components\AppConstant;
use yii\widgets\ActiveForm;

$this->title = AppUtility::t('Forums',false );
$this->params['breadcrumbs'][] = $this->title;
$currentTime = AppUtility::parsedatetime(date('m/d/Y'), date('h:i a'));
$now = $currentTime;
?>
<div class="item-detail-header">
    <?php if($users->rights == 100 || $users->rights == 20) {
        echo $this->render("../../itemHeader/_indexWithLeftContent", ['link_title' => [AppUtility::t('Home', false), $course->name], 'link_url' => [AppUtility::getHomeURL() . 'site/index', AppUtility::getHomeURL() . 'instructor/instructor/index?cid=' . $course->id]]);
    } elseif($users->rights == 10){
        echo $this->render("../../itemHeader/_indexWithLeftContent", ['link_title' => [AppUtility::t('Home', false), $course->name], 'link_url' => [AppUtility::getHomeURL() . 'site/index', AppUtility::getHomeURL() . 'course/course/index?cid=' . $course->id]]);
    }?>
</div>
<div class = "title-container">
    <div class="row">
        <div class="pull-left page-heading">
            <div class="vertical-align title-page"><?php echo $this->title ?></div>
        </div>
    </div>
</div>
<div class="item-detail-content">
    <?php if($users->rights == 100 || $users->rights == 20) {
        echo $this->render("../../instructor/instructor/_toolbarTeacher", ['course' => $course, 'section' => 'Forums']);
    } elseif($users->rights == 10){
        echo $this->render("../../course/course/_toolbarStudent", ['course' => $course, 'section' => 'Forums']);
    }?>
</div>
<input type="hidden" id="courseId" class="courseId" value="<?php echo $cid ?>">
<div class="tab-content shadowBox ">
        <div class="forum-background">
        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-md-3\">{input}</div>\n<div class=\"col-md-5\">{error}</div>",
                'labelOptions' => ['class' => 'col-md-1 control-label'],
            ],
        ]); ?>
          <div class="pull right second-level-div">
              <span class="">
                 <input type="text" id="search_text" maxlength="30" placeholder="<?php echo AppUtility::t('Enter Search Terms')?>">
               </span>
              <span class="search-dropdown">
                    <select name="seluid" class="form-control-forum select_option " id="">
                            <option value="0"><?php echo AppUtility::t('All Thread Subject')?></option>
                            <option value="1"><?php echo AppUtility::t('All Post')?></option>
                    </select>
              </span>
              <span class="Search-btn-forum margin-left-ten">
                  <button type="button" class="btn btn-primary search-button" id="forum_search"><i class="fa fa-search"></i>&nbsp;<b><?php echo AppUtility::t('Search')?></b></button>
              </span>
         </div>
 </div>
        <div class="main-div">
        <div id="display">
<!--            <table id="forum-table display-forum" class="forum-table table table-bordered table-striped table-hover data-table">-->
<!--                <thead>-->
<!--                <tr>-->
<!--                    <th>--><?php //echo AppUtility::t('Forum Name')?><!--</th>-->
<!--                    --><?php //if($users->rights > AppConstant::STUDENT_RIGHT){?>
<!--                    <th>--><?php //echo AppUtility::t('Modify')?><!--</th>-->
<!--                    --><?php //}?>
<!--                    <th>--><?php //echo AppUtility::t('Threads')?><!--</th>-->
<!--                    <th>--><?php //echo AppUtility::t('Posts')?><!--</th>-->
<!--                    <th>--><?php //echo AppUtility::t('Last Post Date')?><!--</th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody class="forum-table-body">-->
<!--                </tbody>-->
<!--            </table>-->
            <table class=forum>
                <thead>
                <tr><th>Forum Name</th><th>Threads</th><th>Posts</th><th>Last Post Date</th></tr>
                </thead>
                <tbody>
                <?php
                foreach ($itemsimporder as $item) {
                    if (!isset($itemsassoc[$item])) { continue; }
                    $line = $forumdata[$itemsassoc[$item]];

                    if (!$isteacher && !($line['avail']==2 || ($line['avail']==1 && $line['startdate']<$now && $line['enddate']>$now))) {
                        continue;
                    }
                    echo "<tr><td>";
                    if ($isteacher) {
                        echo '<span class="right">';
                        echo "<a href=\"../course/addforum.php?cid=$cid&id={$line['id']}\">Modify</a> ";
                        echo '</span>';
                    }
                    echo "<b><a href=\"thread.php?cid=$cid&forum={$line['id']}\">{$line['name']}</a></b> ";
                    if ($newcnt[$line['id']]>0) {
                        echo "<a href=\"thread.php?cid=$cid&forum={$line['id']}&page=-1\" style=\"color:red\">New Posts ({$newcnt[$line['id']]})</a>";
                    }
                    echo "</td>\n";
                    if (isset($threadcount[$line['id']])) {
                        $threads = $threadcount[$line['id']];
                        $posts = $postcount[$line['id']];
                        $lastpost = AppUtility::tzdate("F j, Y, g:i a",$maxdate[$line['id']]);
                    } else {
                        $threads = 0;
                        $posts = 0;
                        $lastpost = '';
                    }
                    echo "<td class=c>$threads</td><td class=c>$posts</td><td class=c>$lastpost</td></tr>\n";
                }
                ?>
                </tbody>
            </table>
        </div>
        <div id="search-thread">
            <table id="forum-search-table display-forum" class="forum-search-table table table-bordered table-striped table-hover data-table" bPaginate="false">
                <thead>
                <th><?php echo AppUtility::t('Topic')?></th>
                <th><?php echo AppUtility::t('Replies')?></th>
                <th><?php echo AppUtility::t('Views')?></th>
                <th><?php echo AppUtility::t('Last Post Date')?></th>
                </thead>
                <tbody class="forum-search-table-body">
                </tbody>
            </table>

        </div>
        <div id="search-post"></div>
        <div id="result">
            <h5><Strong><?php echo AppUtility::t('No result found for your search.')?></Strong></h5>
        </div>
        <?php ActiveForm::end(); ?>


</div>