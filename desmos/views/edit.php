<link rel="stylesheet" href="/desmos/temp_desmos.css" type="text/css" />
<script type="text/javascript">
    var curlibs = '<?php Sanitize::encodeStringForJavascript($item->tags); ?>';
</script>

<div class="breadcrumb"><?php echo $curBreadcrumb  ?></div>

<h1 class="-small-type -inset --small">
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
    <!-- <div class="libraries -inset --exlarge">
        In Libraries: 
        <span id="libnames"><?php echo implode(', ', $item->tagnames); ?></span>
        <input type="hidden" name="libs" id="libs"  value="<?php echo Sanitize::encodeStringForDisplay($item->tags) ?>">
        <button type="button" value="Select Libraries" onClick="GB_show('Library Select','libtree2.php?libtree=popup&libs='+curlibs,500,500)" />
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
    </div> -->
    <div id="step_box" class="desmos desmos-steps -offset --exlarge">
        <div class="steps-left">
            <div class="step-controls">
                <a class="button" href="javascript:addStep()">Add</a>
                <a class="button" href="javascript:removeStep()">Delete</a>
            </div>
            <ul id="step_list" class="step-box">
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
                    printf("<li class=\"step-li $selected\" $action>", $i);
                    printf(
                        "<input type='text' name='step_title[$d]' value='%s' />",
                        $item->steps[$i]['title']
                    );
                    printf(
                        "<input type='hidden' name='step[%d]' value='%d'>",
                        $i,
                        $item->steps[$i]['id']
                    );
                    echo "</ul>";
                }
                ?>
        </div>
        <div id="step_items" class="step-items steps-right">
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
    <button type="submit" class="button" name="submitbtn" value="Submit"><?php echo $savetitle; ?></button>
</form>
