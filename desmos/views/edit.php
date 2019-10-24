<div class=breadcrumb><?php echo $curBreadcrumb  ?></div>
<div id="headeraddinlinetext" class="pagetitle"><h1><?php echo $pagetitle ?><img src="<?php echo $imasroot ?>/img/help.gif" alt="Help" onClick="window.open('<?php echo $imasroot ?>/help.php?section=desmosinteractiveitems','help','top=0,width=400,height=500,scrollbars=1,left='+(screen.width-420))"/></h1></div>

<form enctype="multipart/form-data" method=post action="<?php echo $page_formActionTag ?>">
    Title: <br />
    <input type=text size=60 name=title value="<?php echo str_replace('"','&quot;',$item->title);?>" required /><br />
    <BR class=form>

    Summary: (shows on course page) <br />
    <input type=text size=60 id=summary name=summary value="<?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>" /><br />
    <BR class=form>

    <div>
        <span class=form>Show:</span>
        <span class=formright>
            <input type=radio name="avail" value="0" <?php writeHtmlChecked($item->avail,0);?> onclick="$('#datediv').slideUp(100);$('#altcaldiv').slideUp(100);"/>Hide<br/>
            <input type=radio name="avail" value="1" <?php writeHtmlChecked($item->avail,1);?> onclick="$('#datediv').slideDown(100);$('#altcaldiv').slideUp(100);"/>Show by Dates<br/>
            <input type=radio name="avail" value="2" <?php writeHtmlChecked($item->avail,2);?> onclick="$('#datediv').slideUp(100);$('#altcaldiv').slideDown(100);"/>Show Always<br/>
        </span>
        <br class="form"/>
        <!-- ############################### OHM SPECIFIC CHANGES ########################################### -->
        <div id="datediv" style="display:<?php echo ($item->avail ==1)?"block":"none"; ?>">
            <!-- ############################### OHM SPECIFIC CHANGES ########################################### -->
            <span class=form>Start Date:</span>
            <span class=formright>
        <input type=radio name="sdatetype" value="0" <?php writeHtmlChecked($item->startdate,'0',0) ?>/>
                <!-- ############################### OHM SPECIFIC CHANGES ########################################### -->
         Always available until End Date<br/>
        <input type=radio name="sdatetype" value="sdate" <?php writeHtmlChecked($item->startdate,'0',1) ?>/>
        <input type=text size=10 name=sdate value="<?php echo $sdate;?>">
        <a href="#" onClick="displayDatePicker('sdate', this); return false">
        <img src="../../img/cal.gif" alt="Calendar"/></a>
        at <input type=text size=10 name=stime value="<?php echo $stime;?>">
    </span><BR class=form>
            <!-- ############################### OHM SPECIFIC CHANGES ########################################### -->
            <span class=form>End Date:</span>
            <span class=formright>
        <input type=radio name="edatetype" value="2000000000" <?php writeHtmlChecked($item->enddate,'2000000000',0) ?>/>
                <!-- ############################### OHM SPECIFIC CHANGES ########################################### -->
        Always available after Start Date<br/>
        <input type=radio name="edatetype" value="edate" <?php writeHtmlChecked($item->enddate,'2000000000',1) ?>/>
        <input type=text size=10 name=edate value="<?php echo $edate;?>">
        <a href="#" onClick="displayDatePicker('edate', this, 'sdate', 'start date'); return false">
        <img src="../../img/cal.gif" alt="Calendar"/></a>
        at <input type=text size=10 name=etime value="<?php echo $etime;?>">
    </span><BR class=form>
        </div>
        In Libraries:
        <span id="libnames"><?php echo Sanitize::encodeStringForDisplay($lnames) ?></span>
        <input type=hidden name="libs" id="libs"  value="<?php echo Sanitize::encodeStringForDisplay($searchlibs) ?>">
        <input type="button" value="Select Libraries" onClick="GB_show('Library Select','libtree2.php?libtree=popup&libs='+curlibs,500,500)" />
        <?php
        if (count($outcomes)>0) {
            echo '<span class="form">Associate Outcomes:</span></span class="formright">';
            writeHtmlMultiSelect('outcomes',$outcomes,$outcomenames,$gradeoutcomes,'Select an outcome...');
            echo '</span><br class="form"/>';
        }
        ?>

    </div>
    <div class=submit><button type=submit name="submitbtn" value="Submit"><?php echo $savetitle; ?></button></div>
</form>