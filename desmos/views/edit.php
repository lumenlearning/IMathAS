<script type="text/javascript">
    var curlibs = '<?php Sanitize::encodeStringForJavascript($item->libs); ?>';
</script>

<div class=breadcrumb><?php echo $curBreadcrumb  ?></div>


<h1 class="-small-type">
    <img src="../ohm/img/desmos.png" alt=""/> 
    <?php echo $pagetitle ?>    
</h1>

<form class="desmos form" enctype="multipart/form-data" method="post" action="<?php echo $page_formActionTag ?>">
    <div class="form-group">
        <div class="form-left">
            <div class="controls">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo str_replace('"','&quot;',$item->title);?>" required />
            </div>
            <div class="controls">
                <label for="summary">Summary:</label>
                <input type="text" id="summary" name="summary" value="<?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>" />
            </div>
        </div>
        <div class="form-right">
            <div class="controls">
                <label for="sdate">Start Date:</label>
                <input type="text" class="--input-icon --icon-calendar" onClick="displayDatePicker('sdate', this); return false" id="sdate" name="sdate" value="<?php echo $sdate;?>"/>
            </div>
            <div class="controls">
                <label for="edate">End Date:</label>
                <input type="text" class="--input-icon --icon-calendar" onClick="displayDatePicker('edate', this); return false" id="edate" name="edate" value="<?php echo $edate;?>"/>
            </div>
        </div>
    </div>
    <div class="libraries -inset --exlarge">
        In Libraries:
        <span id="libnames"><?php echo implode(', ', $item->lnames); ?></span>
        <input type="hidden" name="libs" id="libs"  value="<?php echo Sanitize::encodeStringForDisplay($item->libs) ?>"/>
        <button class="button" type="button" onClick="GB_show('Library Select','libtree2.php?libtree=popup&libs='+curlibs,500,500)" >Select Libraries</button>
        <?php
        if (count($outcomes)>0) {
            echo '<span class="form">Associate Outcomes:</span></span class="formright">';
            writeHtmlMultiSelect('outcomes',$outcomes,$outcomenames,$gradeoutcomes,'Select an outcome...');
            echo '</span><br class="form"/>';
        }
        ?>
    </div>
    <button class="button" type="submit" name="submitbtn" value="Submit"><?php echo $savetitle; ?></button>
</form>
