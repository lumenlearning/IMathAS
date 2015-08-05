<?php
use yii\helpers\Html;
use app\components\AppUtility;
use app\components\AppConstant;
$this->title = AppUtility::t('View Conversations',false);
$this->params['breadcrumbs'][] = $this->title;
$currentLevel = AppConstant::ZERO_VALUE;
AppUtility::includeCSS('forums.css');
AppUtility::includeCSS('imascore.css');
AppUtility::includeCSS('default.css');
AppUtility::includeCSS('handheld.css');
AppUtility::includeJS('general.js'); ?>
<script type="text/javascript" src="<?php echo AppUtility::getHomeURL() ?>js/mathjax/MathJax.js?config=AM_HTMLorMML"></script>
<?php AppUtility::includeJS('ASCIIsvg_min.js');?>
<div class="item-detail-header">
    <?php echo $this->render("../../itemHeader/_indexWithLeftContent", ['link_title' => [AppUtility::t('Home', false), $course->name], 'link_url' => [AppUtility::getHomeURL() . 'site/index', AppUtility::getHomeURL() . 'instructor/instructor/index?cid=' . $course->id]]); ?>
</div>
<div class = "title-container">
    <div class="row">
        <div class="pull-left page-heading">
            <div class="vertical-align title-page"><?php echo AppUtility::t('Message:',false);?><?php echo $this->title ?></div>
        </div>
    </div>
</div>
<div class="item-detail-content">
    <?php echo $this->render("../../instructor/instructor/_toolbarTeacher", ['course' => $course, 'section' => '']);?>
</div>
<div class="tab-content shadowBox">
    <div class=mainbody>
        <div class="headerwrapper">
            <div id="navlistcont">
                <ul id="navlist"></ul>
                <div class="clear"></div>
            </div>
        </div>

        <div class="midwrapper">
            <?php $sent = $messageId;
            if ($sent != AppConstant::NUMERIC_ONE) {
                ?>
            <div class="align-buttons">
                <a href="<?php echo AppUtility::getURLFromHome('message', 'message/view-message?message=' . $sent . '&id=' . $messages[0]['id'] . '&cid=' . $course->id); ?>">Back to Message</a>
             </div>
            <?php } ?>
            <?php $sent = $messageId;
            if ($sent == AppConstant::NUMERIC_ONE) {
                ?>
            <div class="align-buttons">
                <a href="<?php echo AppUtility::getURLFromHome('message', 'message/view-message?message=' . $sent . '&id=' . $messages[0]['id'] . '&cid=' . $course->id); ?>">Back to Message</a>
            </div>
            <?php } ?>
            <div class="align-buttons">
            <button onclick="expandall()" class="btn btn-primary1 btn-color">Expand All</button>
            <button onclick="collapseall()" class="btn btn-primary1 btn-color ">Collapse All</button>
            <button onclick="showall()" class="btn btn-primary1 btn-color ">Show All</button>
            <button onclick="hideall()" class="btn btn-primary1 btn-color ">Hide All</button>
            </div>
            <?php
            $DivCount = AppConstant::ZERO_VALUE;
            foreach ($messages as $index => $message){
            ?>
            <?php
            if ($message['level'] != AppConstant::ZERO_VALUE && $message['level'] < $currentLevel)
            {
            $DivCount--;
            for ($i = $currentLevel;$message['level'] < $i;$i--){
            ?>
        </div>
        <?php
        }
        ?>
        <?php } ?>
        <?php if ($message['level'] != AppConstant::ZERO_VALUE && $message['level'] > $currentLevel)
        {
        $DivCount++;?>
        <div class="forumgrp" id="block<?php echo $index - AppConstant::NUMERIC_ONE ?>">
        <?php } ?>

            <div class="padding-to-block"><div class="block"><span class="leftbtns">
          <?php  if($message['hasChild'] == AppConstant::NUMERIC_ONE){ ?>
              <img class="pointer" id="butb<?php echo $index ?>"
                   src="<?php echo AppUtility::getHomeURL() ?>img/collapse.gif"
                   onClick="toggleshow(<?php echo $index ?>)"/>
          <?php } ?></span>
            <span class=right>
                 <?php if ($user['id'] != $message['senderId']) { ?>
                     <a href="<?php echo AppUtility::getURLFromHome('message', 'message/reply-message?id=' . $message['id'] . '&cid=' . $course->id); ?>">
                         Reply</a>
                 <?php } ?>
                <button class="btn btn-primary1 btn-color" id="buti<?php echo $index ?>"
                       onClick="toggleitem(<?php echo $index ?>)">Hide</button>
                </span>
                <b><?php echo $message['title'] ?></b><br/>Posted by: <a
                    href="mailto:<?php echo '#' ?>"><?php echo $message['senderName'] ?></a>, <?php echo date('M d, o g:i a', $message['msgDate']) ?>

                <span style="color:red;">New</span>
            </div>
            <div class="blockitems" id="item<?php echo $index ?>"><p><?php
                    if (($p = strpos($message['message'],'<hr'))!==false) {
                        $message['message'] = substr($message['message'],0,$p).'<a href="#" class="small" onclick="showtrimmedcontent(this,\''.$message['id'].'\');return false;">[Show trimmed content]</a><div id="trimmed'.$message['id'].'" style="display:none;">'.substr($message['message'],$p).'</div>';
                    }
                    echo $message['message'] ?></p></div></div>
            <?php if ($index == (count($messages) - AppConstant::NUMERIC_ONE))
            {
            for ($i = $DivCount;$i >= AppConstant::ZERO_VALUE;$i--){
            ?>
        </div>
    <?php
    }
    } ?>
        <?php
        $currentLevel = $message['level'];
        $messageCount = (count($messages) - AppConstant::NUMERIC_ONE);
        ?>
        <input type="hidden" id="messageCount" value="<?php echo $messageCount ?>">
        <?php } ?>
    </div>
</div>
