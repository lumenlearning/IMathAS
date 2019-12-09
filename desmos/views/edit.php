<link rel="stylesheet" href="/desmos/temp_desmos.css" type="text/css" />
<script type="text/javascript">
    var curlibs = '<?php Sanitize::encodeStringForJavascript($item->tags); ?>';
    window.onload = ()=> {
        <?php if (count($item->steps) < 1) {
            echo 'addStep();';
        } else {
            echo 'showSteps("#desmos_edit_container",0);';
        } ?>
    }
</script>

<div class="breadcrumb"><?php echo $curBreadcrumb  ?></div>
<div id="desmos_edit_container">
    <link rel="stylesheet" href="/desmos/temp_desmos.css" type="text/css" />

    <h1 class="-small-type desmos-header">
        <img src="../ohm/img/desmos.png" alt=""/>
        <?php echo $pagetitle ?>
    </h1>

    <button id="desmos_preview_button" class="desmos button" type="button">Preview</button>

    <form id="desmos_item" class="desmos form" enctype="multipart/form-data" method="post" action="<?php echo $page_formActionTag ?>">
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
        <div id="step_box" class="desmos desmos-steps -offset --exlarge">
            <div class="step-navigation">
                <div class="step-controls">
                    <a class="button" href="javascript:addStep()">Add</a>
                </div>
                <ol id="step_list" class="step-box">
                    <?php
                    $action = '';
                    if (count($item->steps)>1) {
                        $action = "onClick=\"showSteps('#desmos_edit_container',%d)\"";
                    }
                    for ($i=0; $i<count($item->steps); $i++) {
                        $selected = '';
                        if ($i==0) {
                            $selected = "selected";
                        }
                        printf("<li class=\"step-li $selected\" $action>", $i);
                        printf(
                            "<input type='text' name='step_title[%d]' value='%s' />",
                            $i,
                            $item->steps[$i]['title']
                        );
                        printf(
                            "<input type='hidden' name='step[%d]' value='%d'>",
                            $i,
                            $item->steps[$i]['id']
                        );
                        echo '<button type="button" onclick="removeStep('.$i.')"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 16 16"><defs><path d="M9.885 8l5.724-5.724a1.332 1.332 0 000-1.885 1.332 1.332 0 00-1.885 0L8 6.115 2.276.39a1.332 1.332 0 00-1.885 0 1.332 1.332 0 000 1.885L6.115 8 .39 13.724A1.332 1.332 0 001.334 16c.34 0 .682-.13.942-.39L8 9.884l5.724 5.724a1.33 1.33 0 001.885 0 1.332 1.332 0 000-1.885L9.885 8z" id="a"/></defs><use fill="#637381" xlink:href="#a" fill-rule="evenodd"/></svg></button>';
                    }
                    ?>
                </ol>
            </div>
            <div id="step_items" class="step-items step-details">
                <?php
                for ($i=0; $i<count($item->steps); $i++) {
                    printf('<div id="step-item-display-%d">', $i);
                    echo "<textarea rows=24 name=\"step_text[$i]\" class=\"step-item\"> ";
                    echo htmlspecialchars($item->steps[$i]['text']);
                    echo "</textarea>";
                    echo "</div>";
                } ?>
            </div>
        </div>
        <button id="desmos_form_submit_button" class="button" type="submit" name="submitbtn"
                value="Submit" style="clear:both">Save and Exit</button>
    </form>
</div>

<div id="desmos_previewmode_buttons" style="display: none;">
    <button id="desmos_edit_button" class="desmos button" type="button">Edit</button>
    <button id="desmos_save_button" class="desmos button" type="button">Save and Exit</button>
</div>

<div id="desmos_preview_container" style="display: none;"></div>
