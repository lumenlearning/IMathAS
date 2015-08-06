<?php
use app\components\AppUtility;
use app\components\AssessmentUtility;
use app\components\CourseItemsUtility;
use app\components\AppConstant;

$this->title = AppUtility::t('Message',false );
$this->params['breadcrumbs'][] = $this->title;
$currentTime = AppUtility::parsedatetime(date('m/d/Y'), date('h:i a'));
$now = $currentTime;
?>
<div>
    <input type="hidden" class="send-msg" value="<?php echo $course->id ?>">
    <input type="hidden" class="send-userId" value="<?php echo $course->ownerid ?>">
    <input type="hidden" class="msg-type" value="<?php echo $isNewMessage ?>">
    <?php if ($userRights->rights > AppConstant::STUDENT_RIGHT) { ?>
        <input type="hidden" class="msg-id" value="<?php echo $messages['id'] ?>">
<!--        Condition for toolbar-->
    <?php } else {?>

<?php } ?>
    <div class="item-detail-header">
            <?php echo $this->render("../../itemHeader/_indexWithLeftContent", ['link_title' => [AppUtility::t('Home', false), $course->name], 'link_url' => [AppUtility::getHomeURL() . 'site/index', AppUtility::getHomeURL() . 'instructor/instructor/index?cid=' . $course->id]]); ?>
    </div>
    <div class = "title-container">
        <div class="row">
            <div class="pull-left page-heading">
                <div class="vertical-align title-page"><?php echo $this->title ?></div>
            </div>
        </div>
    </div>
<div class="item-detail-content">

</div>
<div class="tab-content shadowBox ">
    <br>
     <div class="view-message-inner-contents">
         <div class="row">

                 <div class=" col-sm-6 message-title">
                     <h4><?php echo $messages->title ?></h4>
                  </div>
                 <div class="pull right col-sm-6 message-title ">
                     <?php echo date('M d, o g:i a', $messages->senddate) ?>
                 </div>
         </div>
         <div class="second-level-div row">
             <div class="col-sm-2">
                 <span>From: <?php echo ucfirst($fromUser->FirstName) . ' ' . ucfirst($fromUser->LastName) ?></span>
             </div>
             <?php $sent = $messageId;?>
             <?php if ($sent != AppConstant::NUMERIC_ONE) {?>
             <div class=" pull right col-sm-8">
                 <a href="#" id="mark-delete"><?php echo AppUtility::t('Delete Message')?></a>
             </div>
             <div class="col-sm-2 pull right">
                <a  href="#" id="mark-as-unread"><?php echo AppUtility::t('Mark Unread ')?></a>
             </div>
             <?php }?>
         </div>
        <div class="col-md-12 message-body">
        <?php  if (($parent = strpos($messages['message'],'<hr'))!==false)
             {$messages['message'] = substr($messages['message'],0,$parent).'<a href="#" class="small" onclick="showtrimmedcontent(this);return false;">['.AppUtility::t('Show trimmed content', false).']</a><div id="trimmed" style="display:none;">'.substr($messages['message'],$parent).'</div>';
                   } ?>
           <?php echo $messages->message ?>
         </div>

         <div class="reply message-body">
             <?php if ($sent != AppConstant::NUMERIC_ONE) {?>
                    <span class="message-reply">
                         <a href="<?php echo AppUtility::getURLFromHome('message', 'message/reply-message?id=' . $messages->id.'&cid='.$course->id); ?>"
                            class="btn1  reply-button "> <i class="fa fa-reply"></i>&nbsp;&nbsp;<?php echo AppUtility::t('Reply')?></a>
                    </span>
             <?php }?>
                     <span class="message-reply">
                         <a href="<?php echo AppUtility::getURLFromHome('message', 'message/view-conversation?id=' . $messages->id . '&message=' . $sent . '&baseid=' . $messages->baseid.'&cid='.$course->id); ?>" class="btn1  reply-button "><i class="fa fa-twitch"></i>&nbsp;&nbsp;<?php echo AppUtility::t('View Conversation')?></a>
                    </span>
         </div>
</div>
    <br>
 </div>
