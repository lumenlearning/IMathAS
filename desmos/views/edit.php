<link rel="stylesheet" href="/desmos/temp_desmos.css" type="text/css" />
<script type="text/javascript">
    var curlibs = '<?php Sanitize::encodeStringForJavascript($item->tags); ?>';
</script>
<div class=breadcrumb><?php echo $curBreadcrumb  ?></div>
<div id="headeraddinlinetext" class="pagetitle"><h1><?php echo $pagetitle ?><img src="<?php echo $imasroot ?>/img/help.gif" alt="Help" onClick="window.open('<?php echo $imasroot ?>/help.php?section=desmositemitems','help','top=0,width=400,height=500,scrollbars=1,left='+(screen.width-420))"/></h1></div>

<form enctype="multipart/form-data" method=post action="<?php echo $page_formActionTag ?>">
    Title: <br />
    <input type=text size=60 name=title value="<?php echo str_replace('"','&quot;',$item->title);?>" required /><br />
    <BR class=form>

    Summary: (shows on course page) <br />
    <input type=text size=60 id=summary name=summary value="<?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>" /><br />
    <BR class=form>

    <div>
        <div id="datediv" style="display:<?php echo ($item->avail ==1)?"block":"none"; ?>">
            <span class=form>Start Date:</span>
            <span class=formright>
                <input type=radio name="sdatetype" value="0" <?php writeHtmlChecked($item->startdate,'0',0) ?>/>
                Always available until End Date<br/>
                <input type=radio name="sdatetype" value="sdate" <?php writeHtmlChecked($item->startdate,'0',1) ?>/>
                <input type=text size=10 name=sdate value="<?php echo $sdate;?>">
                <a href="#" onClick="displayDatePicker('sdate', this); return false">
                <img src="../../img/cal.gif" alt="Calendar"/></a>
                at <input type=text size=10 name=stime value="<?php echo $stime;?>">
            </span>
            <BR class=form>
            <span class=form>End Date:</span>
            <span class=formright>
                <input type=radio name="edatetype" value="2000000000" <?php writeHtmlChecked($item->enddate,'2000000000',0) ?>/>
                Always available after Start Date<br/>
                <input type=radio name="edatetype" value="edate" <?php writeHtmlChecked($item->enddate,'2000000000',1) ?>/>
                <input type=text size=10 name=edate value="<?php echo $edate;?>">
                <a href="#" onClick="displayDatePicker('edate', this, 'sdate', 'start date'); return false">
                <img src="../../img/cal.gif" alt="Calendar"/></a>
                at <input type=text size=10 name=etime value="<?php echo $etime;?>">
            </span>
            <BR class=form>
        </div>
        In Libraries:
        <span id="libnames"><?php echo implode(', ', $item->tagnames); ?></span>
        <input type=hidden name="libs" id="libs"  value="<?php echo Sanitize::encodeStringForDisplay($item->tags) ?>">
        <input type="button" value="Select Libraries" onClick="GB_show('Library Select','libtree2.php?libtree=popup&libs='+curlibs,500,500)" />
        <?php
        if (count($outcomes)>0) {
            echo '<span class="form">Associate Outcomes:</span></span class="formright">';
            writeHtmlMultiSelect(
                'outcomes',
                $outcomes,
                $outcomenames,
                $gradeoutcomes,
                'Select an outcome...'
            );
            echo '</span><br class="form"/>';
        }
        ?>

    </div>
    <div id="step_box">
        <div id="step_list" class="step-list">
            <h2>Steps</h2>
            <a class="button" href="javascript:addStep()">Add</a>
            <a class="button" href="javascript:removeStep()">Delete</a>
            <?php
            $action = '';
            if (count($item->steps)>1) {
                $action = "onClick=\"showSteps(%d)\"";
            }
            for ($i=0; $i<count($item->steps); $i++) {
                $selected = '';
                if ($i==0) {
                    $selected = "selected";
                }
                printf("<span class=\"step-li $selected\" $action>", $i);
                printf(
                    "<input type='text' name='step_title[$d]' value='%s' />",
                    $item->steps[$i]['title']
                );
                printf(
                    "<input type='hidden' name='step[%d]' value='%d'>",
                    $i,
                    $item->steps[$i]['id']
                );
                echo "</span>";
            }
            ?>
        </div>
        <div id="step_items" class="step-items">
        <?php
        for ($i=0; $i<count($item->steps); $i++) {
            echo "<textarea name=\"step_text[$i]\" class=\"step-item editor";
            if ($i>0) echo " hidden";
            echo "\"> ";
            echo $item->steps[$i]['text'];
            echo "</textarea>";
        } ?>
        </div>
    </div>
    <div class=submit><button type=submit name="submitbtn" value="Submit"><?php echo $savetitle; ?></button></div>
</form>