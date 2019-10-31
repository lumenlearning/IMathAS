<script type="text/javascript">
    var curlibs = '<?php Sanitize::encodeStringForJavascript($item->libs); ?>';
</script>
<div class=breadcrumb><?php echo $curBreadcrumb  ?></div>
<div id="headeraddinlinetext" class="pagetitle"><h1><?php echo $pagetitle ?><img src="<?php echo $imasroot ?>/img/help.gif" alt="Help" onClick="window.open('<?php echo $imasroot ?>/help.php?section=desmosinteractiveitems','help','top=0,width=400,height=500,scrollbars=1,left='+(screen.width-420))"/></h1></div>

<form class="form" enctype="multipart/form-data" method=post action="<?php echo $page_formActionTag ?>">
    <label>Title:</label>
    <input type="text" size=60 name="title" value="<?php echo str_replace('"','&quot;',$item->title);?>" required />
    

    <label>Summary:</label>
    <input type="text" size=60 id="summary" name="summary" value="<?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>" />
    
    <div>
        <div id="datediv" style="display:<?php echo ($item->avail ==1)?"block":"none"; ?>">
            <label>Start Date:</label> 
            <input type="text" size=10 name="sdate" value="<?php echo $sdate;?>">
            <a href="#" onClick="displayDatePicker('sdate', this); return false">
            <img src="../../img/cal.gif" alt="Calendar"/></a>
    
            <label>End Date:</label>
            <input type="text" size=10 name="edate" value="<?php echo $edate;?>">
            <a href="#" onClick="displayDatePicker('edate', this, 'sdate', 'start date'); return false">
            <img src="../../img/cal.gif" alt="Calendar"/></a> 
        </div>
        
        In Libraries:
        <span id="libnames"><?php echo implode(', ', $item->lnames); ?></span>
        <input type="hidden" name="libs" id="libs"  value="<?php echo Sanitize::encodeStringForDisplay($item->libs) ?>">
        <input type="button" value="Select Libraries" onClick="GB_show('Library Select','libtree2.php?libtree=popup&libs='+curlibs,500,500)" />
        <?php
        if (count($outcomes)>0) {
            echo '<span class="form">Associate Outcomes:</span></span class="formright">';
            writeHtmlMultiSelect('outcomes',$outcomes,$outcomenames,$gradeoutcomes,'Select an outcome...');
            echo '</span><br class="form"/>';
        }
        ?>
    </div>
    <div class="submit">
        <button type="submit" name="submitbtn" value="Submit"><?php echo $savetitle; ?></button>
    </div>
</form>