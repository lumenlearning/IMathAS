<?php
use yii\helpers\Html;
use app\components\AppUtility;
use app\components\AppConstant;
use kartik\time\TimePicker;
use kartik\date\DatePicker;
use app\components\AssessmentUtility;

$this->title = $pageTitle;
$this->params['breadcrumbs'][] = ['label' => $course->name, 'url' => ['/instructor/instructor/index?cid='.$course->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if ($modifyForumId){ ?>
    <form enctype="multipart/form-data" method=post action="add-forum?cid=<?php echo $course->id ?>&modifyFid=<?php echo $modifyForumId; ?>">
<?php }else{ ?>
    <form enctype="multipart/form-data" method=post action="add-forum?cid=<?php echo $course->id ?>">
<?php } ?>
    <div class="item-detail-header">
        <?php echo $this->render("../../itemHeader/_indexWithLeftContent",['link_title'=>['Home',$course->name], 'link_url' => [AppUtility::getHomeURL().'site/index',AppUtility::getHomeURL().'instructor/instructor/index?cid='.$course->id], 'page_title' => $this->title]); ?>
    </div>
    <div class = "title-container">
        <div class="row">
            <div class="pull-left page-heading">
                <div class="vertical-align title-page"><?php echo $this->title ?><i class="fa fa-question help-icon"></i></div>
            </div>
            <div class="pull-left header-btn">
                <button class="btn btn-primary pull-right page-settings" type="submit" value="Submit"><i class="fa fa-share header-right-btn"></i><?php echo $saveTitle ?></button>
            </div>
        </div>
    </div>

    <div class="tab-content shadowBox non-nav-tab-item">
        <div class="name-of-item">
            <div class="col-lg-2"><?php AppUtility::t('Name of Forum')?></div>
            <div class="col-lg-10">
                <?php $title = AppUtility::t('Enter forum name here', false);
                if ($forumData) {
                $title = $forumData['name'];
                } ?>
                <input type=text size=0 style="width: 100%;height: 40px; border: #6d6d6d 1px solid;" name=name value="<?php echo $title;?>">
            </div>
        </div>
            <BR class=form>

        <div class="editor-summary">
            <div class="col-lg-2"><?php AppUtility::t('Description')?></div>
            <div class="col-lg-10">
                <div class=editor>
                    <textarea cols=5 rows=12 id=description name=description style="width: 100%;">
                    </textarea>
                </div>
            </div>
        </div>
            <!--Show-->
        <div>
        <div class="col-lg-2">Visibility</div>
        <div class="col-lg-10">
            <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility label-visible'><td><input type=radio name="avail" value="0" <?php AssessmentUtility::writeHtmlChecked($forumData['avail'],AppConstant::NUMERIC_ZERO);?> onclick="document.getElementById('datediv').style.display='none'; "/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td><td><?php AppUtility::t('Hide')?></td></div>
            <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility label-visible'><td><input type=radio name="avail" value="1" <?php AssessmentUtility::writeHtmlChecked($forumData['avail'],AppConstant::NUMERIC_ONE);?> onclick="document.getElementById('datediv').style.display='block';"/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td><td><?php AppUtility::t('Show by Dates')?></td></div>
            <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility label-visible'><td><input type=radio name="avail" value="2" <?php AssessmentUtility::writeHtmlChecked($forumData['avail'], AppConstant::NUMERIC_TWO); ?> onclick="document.getElementById('datediv').style.display='none'; "/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td><td><?php AppUtility::t('Show Always')?></td></div>
        </div>
        </div>
                <!--Show by dates-->
                <div id="datediv" style="display:<?php echo ($forum['avail'] == 1) ? "block" : "none"; ?>">
                    <?php $startTime = $eTime; ?>
                    <div class=col-lg-2>Available After:</div>
                    <div class=col-lg-10>
                        <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility label-visible'><td><input type=radio name="available-after" value="0" <?php AssessmentUtility::writeHtmlChecked($defaultValue['startDate'], '0', AppConstant::NUMERIC_ZERO) ?>/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td><td><?php AppUtility::t('Always until end date')?></td></div>
                        <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility pull-left label-visible'><td><input type=radio name="available-after" class="pull-left" value="1" <?php AssessmentUtility::writeHtmlChecked($defaultValue['startDate'] , '1', AppConstant::NUMERIC_ONE) ?>/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td>
                        <?php
                        echo '<div class = "col-lg-4 time-input" style="padding-left: 0">';
                        echo DatePicker::widget([
                            'name' => 'sdate',
                            'type' => DatePicker::TYPE_COMPONENT_APPEND,
                            'value' => date("m-d-y"),
                            'removeButton' => false,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'mm/dd/yyyy']
                        ]);
                        echo '</div>'; ?>
                        <?php
                        echo '<label class="end col-lg-1"> at </label>';
                        echo '<div class="pull-left col-lg-4">';
                        echo TimePicker::widget([
                            'name' => 'stime',
                            'value' => $eTime,
                            'pluginOptions' => [
                                'showSeconds' => false,
                                'class' => 'time'
                            ]
                        ]);
                        echo '</div>'; ?>
                    </div></div><BR class=form>

                    <div class=col-lg-2>Available Until:</div>
                    <div class=col-lg-10>
                        <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility label-visible'><td><input type=radio name="available-until" value="2000000000" <?php AssessmentUtility::writeHtmlChecked($defaultValue['endDate'], '2000000000', 0) ?>/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td><td><?php AppUtility::t('Always after start date')?></td></div>
                        <div class='radio student-enroll visibility override-hidden'><label class='checkbox-size label-visibility pull-left label-visible'><td><input type=radio name="available-until" class="pull-left value="1" <?php AssessmentUtility::writeHtmlChecked($defaultValue['endDate'], '2000000000', 1) ?>/><span class='cr'><i class='cr-icon fa fa-check'></i></span></label></td>
                        <?php
                        echo '<div class = "col-lg-4 time-input" style="padding-left: 0">';
                        echo DatePicker::widget([
                            'name' => 'edate',
                            'type' => DatePicker::TYPE_COMPONENT_APPEND,
                            'value' => date("m-d-y"),
                            'removeButton' => false,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'mm/dd/yyyy']
                        ]);
                        echo '</div>'; ?>
                        <?php
                        echo '<label class="end col-lg-1"> at </label>';
                        echo '<div class="pull-left col-lg-4">';

                        echo TimePicker::widget([
                            'name' => 'etime',
                            'value' => $eTime,
                            'pluginOptions' => [
                                'showSeconds' => false,
                                'class' => 'time'
                            ]
                        ]);
                        echo '</div>'; ?>
                            </div>
                </div>
                <BR class=form>
                </div>

                <div style="margin-top: 20px">
                    <div class=col-lg-2>Group forum?</div>
                        <div class=col-lg-10>
                             <?php
                            AssessmentUtility::writeHtmlSelect("groupsetid",$groupNameId,$groupNameLabel,$forumData['groupsetid'],"Not group forum",0);
                            if ($forumData['groupsetid'] > 0 && $defaultValue['hasGroupThreads']) {
                                echo '<br/>WARNING: <span style="font-size: 80%">Group threads exist.  Changing the group set will set all existing threads to be non-group-specific threads</span>';
                            }        ?>

                </div></div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Allow anonymous posts:</div>
                            <div class=col-lg-10>
                                <div class="checkbox override-hidden"><label class="inline-checkbox label-visible"><input type="checkbox" name="allow-anonymous-posts" value="1"<?php if ($defaultValue['allowanon']) { echo "checked=1";}?> ><span class="cr"><i class="cr-icon fa fa-check"></i></span></label></div>
                            </div>
                    </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Allow students to modify posts:</div>
                            <div class=col-lg-10>
                                <div class="checkbox override-hidden"><label class="inline-checkbox label-visible"><input type="checkbox" name="allow-students-to-modify-posts" value="2"<?php if ($defaultValue['allowmod']) { echo "checked=1";}?>><span class="cr"><i class="cr-icon fa fa-check"></i></span></label></div>
                        </div>
                    </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Allow students to delete own posts (if no replies):</div>
                            <div class=col-lg-10>
                                <div class="checkbox override-hidden"><label class="inline-checkbox label-visible"><input type="checkbox" name="allow-students-to-delete-own-posts" value="4"<?php if ($defaultValue['allowdel']) { echo "checked=1";}?>><span class="cr"><i class="cr-icon fa fa-check"></i></span></label></div>
                        </div>
                    </div><br class=form>

                    <div class=col-lg-2>Turn on "liking" posts:</div>
                        <div class=col-lg-10>
                            <div class="checkbox override-hidden"><label class="inline-checkbox label-visible"><input type="checkbox" name="like-post" value="8"<?php if ($defaultValue['allowlikes']) { echo "checked=1";}?>><span class="cr"><i class="cr-icon fa fa-check"></i></span></label></div>
                        </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Viewing before posting:</div>
                        <div class=col-lg-10>
                            <input type="checkbox" name="viewing-before-posting" value="16"<?php if ($defaultValue['viewAfterPost']) { echo "checked=1";}?>>
                            Prevent students from viewing posts until they have created a thread.
                            You will likely also want to disable modifying posts
                        </div>
                    </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Get email notify of new posts:</div>
                        <div class=col-lg-10>
                            <input type="checkbox" name="Get-email-notify-of-new-posts" value="1"<?php if ($defaultValue['hasSubScrip']) { echo "checked=1";}?>>
                        </div>
                    </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Default display:</div>
                            <div class=col-lg-10>
                            <select name="default-display" class="form-control">
                                <option value="0" <?php if ($defaultValue['defDisplay']==0 || $defaultValue['defDisplay']==1) {echo "selected=1";}?>>Expanded</option>
                                <option value="2" <?php if ($defaultValue['defDisplay']==2) {echo "selected=1";}?>>Condensed</option>
                            </select>
                        </div>
                    </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Sort threads by:</div>
                            <div class=col-lg-10>
                                <input type=radio name="sort-thread" value="0" <?php AssessmentUtility::writeHtmlChecked($defaultValue['sortBy'],0);?>>Thread start date<br/>
                                <input type=radio name="sort-thread" value="1"<?php AssessmentUtility::writeHtmlChecked($defaultValue['sortBy'],1);?>/> Most recent reply date<br/>
                            </div><br class="form"/>
                    </div>

                    <div class="item-alignment">
                        <div class=col-lg-2>Students can create new threads:</div>
                        <div class="col-lg-10">
                            <input type=radio name="new-thread"
                                   value="0" <?php if ($defaultValue['postBy']==2000000000) { echo "checked=1";}?>>Alway <br/>
                            <input type=radio name="new-thread"
                                   value="2000000000" <?php if ($defaultValue['postBy']==0) { echo "checked=1";}?>>Never<br/>
                            <input type=radio name="new-thread" class="pull-left "
                           value="1" <?php if ($defaultValue['postBy']<2000000000 && $defaultValue['postBy']>0) { echo "checked=1";}?> >
                            <?php
                            echo '<label class="end pull-left">Before:</label>';
                            echo '<div class = "pull-left col-lg-4 time-input">';
                            echo DatePicker::widget([
                                'name' => 'newThreadDate',
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'value' => date("m/d/Y"),
                                'removeButton' => false,
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'mm/dd/yyyy']
                            ]);
                            echo '</div>'; ?>
                            <?php
                            echo '<label class="end pull-left"> at </label>';
                            echo '<div class=" col-lg-6">';
                            echo TimePicker::widget([
                                'name' => 'newThreadTime',
                                'value' =>  $eTime,
                                'pluginOptions' => [
                                    'showSeconds' => false,
                                    'class' => 'time'
                                ]
                            ]);
                            echo '</div>'; ?>

                        </div>
                        </div><BR class=form>

                        <div class="item-alignment">
                            <div class=col-lg-2>Students can reply to posts:</div>
                            <div class="col-lg-10">

                                <input type=radio name="reply-to-posts" value="0" <?php if ($defaultValue['replyBy']==2000000000) { echo "checked=1";}?>>Alway <br/>
                                <input type=radio name="reply-to-posts"
                                       value="2000000000" <?php if ($defaultValue['replyBy']==0) { echo "checked=1";}?>>Never<br/>
                                <input type=radio name="reply-to-posts" class="pull-left "
                               value="1" <?php if ($defaultValue['replyBy']<2000000000 && $defaultValue['replyBy']>0) { echo "checked=1";}?> >
                                <?php
                                echo '<label class="end pull-left">Before:</label>';
                                echo '<div class = "pull-left col-lg-4 time-input">';
                                echo DatePicker::widget([
                                    'name' => 'replayPostDate',
                                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                    'value' => date("m/d/Y"),
                                    'removeButton' => false,
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'mm/dd/yyyy']
                                ]);
                                echo '</div>'; ?>
                                <?php
                                echo '<label class="end pull-left"> at </label>';
                                echo '<div class=" col-lg-6">';
                                echo TimePicker::widget([
                                    'name' => 'replayPostTime',
                                    'value' => $eTime,
                                    'pluginOptions' => [
                                        'showSeconds' => false,
                                        'class' => 'time'
                                    ]
                                ]);
                                echo '</div>'; ?>

                    </div></div><BR class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Calendar icon:</div>
                           <div class=col-lg-10>
                        New Threads: <input type="text" name="calendar-icon-text1" value="<?php echo $defaultValue['postTag'];?>" size="2"> ,
                        Replies: <input type="text" name="calendar-icon-text2" value="<?php echo $defaultValue['replyTag'];?>" size="2">
                            </div><br class=form>
                    </div>

                 <div class="item-alignment">
                    <div class=col-lg-2>Count in gradebook?</div>
                    <div class=col-lg-10>
                        <input type=radio name="count-in-gradebook" value="0" <?php if ($defaultValue['cntInGb']==0) { echo 'checked=1';}?> onclick="toggleGBdetail(false)"/>No<br/>
                        <input type=radio name="count-in-gradebook" value="1" <?php if ($defaultValue['cntInGb']==1) { echo 'checked=1';}?> onclick="toggleGBdetail(true)"/>Yes<br/>
                        <input type=radio name="count-in-gradebook" value="4" <?php if ($defaultValue['cntInGb']==4 && $defaultValue['points'] > 0) { echo 'checked=1';}?> onclick="toggleGBdetail(true)"/>Yes, but hide from students for now<br/>
                        <input type=radio name="count-in-gradebook" value="2" <?php if ($defaultValue['cntInGb']==2) { echo 'checked=1';}?> onclick="toggleGBdetail(true)"/>Yes, as extra credit<br/>

                </div></div><br class="form"/>

                <div class="item-alignment">
                    <div id="gbdetail" <?php if ($defaultValue['cntingb']==0 && $defaultValue['points']==0) { echo 'style="display:none;"';}?>>

                        <div class=col-lg-2>Points:</div>
                        <div class=col-lg-10>
                            <input type="text" name="points" value="<?php echo $defaultValue['points'];?>" size="3"> points
                </div><br class=form>

                    <div class="item-alignment">
                        <div class=col-lg-2>Gradebook Category:</div>
                        <div class=col-lg-10>
                             <?php AssessmentUtility::writeHtmlSelect("gradebook-category",$gbcatsId,$gbcatsLabel,$defaultValue['gbCat'],"Default",0); ?>
                        </div><br class=form>
                         <?php $page_tutorSelect['label'] = array("No access to scores","View Scores","View and Edit Scores");
                        $page_tutorSelect['val'] = array(2,0,1); ?>
                    </div>
                    <div class="item-alignment">
                        <div class=col-lg-2>Tutor Access:</div>
                        <div class=col-lg-10>
                            <?php AssessmentUtility::writeHtmlSelect("tutor-edit",$page_tutorSelect['val'],$page_tutorSelect['label'],$forumData['tutoredit']); ?>
                        </div><br class=form>
                    </div>
                    <div class="item-alignment">
                        <div class="col-lg-2">Use Scoring Rubric</div>
                        <div class=col-lg-10>
                                <?php AssessmentUtility::writeHtmlSelect('rubric',$rubricsId,$rubricsLabel,$forumData['rubric'],'None',0); ?>
                                <a href="<?php echo AppUtility::getURLFromHome('site','work-in-progress') ?>">Add new
                                rubric</a> | <a
                                href="<?php echo AppUtility::getURLFromHome('site','work-in-progress') ?>">Edit
                                rubrics</a>
                          </div>
                          <br class=form>
                    </div>
                    </div>
                    <div class="item-alignment">
                        <?php if (count($pageOutcomesList) > 0) { ?>
                        <div class="col-lg-2">Associate Outcomes:</div><div class="col-lg-10">
                        <?php
                            $gradeoutcomes = array();
                            AssessmentUtility::writeHtmlMultiSelect('outcomes', $pageOutcomesList, $pageOutcomes, $gradeoutcomes, 'Select an outcome...'); ?>
                            <br class="form"/>
                        <?php } ?>
                        <br class=form>
                    </div></div>

                    <div class="item-alignment">
                       <div class=col-lg-2>Forum Type:</div>
                            <div class=col-lg-10>
                                <input type=radio name="forum-type" value="0" <?php if ($forumData['forumtype']==0) { echo 'checked=1';}?>/>Regular forum<br/>
                                <input type=radio name="forum-type" value="1" <?php if ($forumData['forumtype']==1) { echo 'checked=1';}?>/>File sharing forum
                            </div><br class="form"/></div>

                    <div class="item-alignment">
                        <div class=col-lg-2>Categorize posts?</div>
                         <div class=col-lg-10>
                             <input type=checkbox name="categorize-posts" value="1" <?php if ($forumData['taglist'] != '') {
                                  echo "checked=1";
                                } ?>
                              onclick="document.getElementById('tagholder').style.display=this.checked?'':'none';"/>
                              <span id="tagholder" style="display:<?php echo ($forumData['taglist'] == '') ? "none" : "inline"; ?>">
                              Enter in format CategoryDescription:category,category,category<br/>
                              <input type="text" size="50" height="20" name="taglist"><?php echo $forumData['taglist']; ?>
                              </span><br class=form><br class=form>
            </div>
                        </div>
            </div>
    </form>