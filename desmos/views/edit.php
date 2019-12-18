<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<script type="text/javascript">
    window.onload = ()=> {
        <?php if (count($item->steps) < 1) {
            echo 'addStep();';
        } else {
            echo 'showSteps("desmos_edit_container", document.getElementById("step_list").children[0])';
        } ?>
    }
</script>

<div class="breadcrumb"><?php echo $curBreadcrumb  ?></div>
<div id="desmos_edit_container">
    <h1 class="-small-type desmos-header">
        <img src="../ohm/img/desmos.png" alt=""/>
        <?php echo $pagetitle ?>
    </h1>

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
            <div class="steps-navigation teacher-view">
                <div class="step-controls">
                    <button class="button --button-secondary js-add" type="button">Add</button>
                </div>
                <span id="step-notifications" aria-live="assertive" class="u-sr-only"></span>
                <span id="step-directions" class="u-sr-only">Press spacebar to toggle drag-and-drop mode, use arrow keys to move selected elements.</span>
                <ol id="step_list" class="js-step-list step-list" data-description="step-directions" data-liveregion="step-notifications">
                    <!-- Changes to step markup must also be duplicated in the addStep JS -->
                    <?php
                    $action = '';
                    $numsteps = 0;
                    if (count($item->steps)>1) {
                        $action = "onClick=\"showSteps('desmos_edit_container', this)\"";
                        $keyaction = "onkeydown=\"javascript: if(event.keyCode == 9) showSteps('desmos_edit_container', this);\"";
                    }
                    foreach ($item->steporder as $i) {
                        $selected = '';
                        if ($numsteps==0) {
                            $selected = "is-selected";
                        }
                        echo "<li class=\"step-li $selected\" $action $keyaction draggable=\"false\" data-num=\"$numsteps\">";
                        echo "<span class=\"js-drag-trigger move-trigger\"><button class=\"u-button-reset\" aria-label=\"Move this item.\" type=\"button\"><svg aria-hidden=\"true\"><use xlink:href=\"#lux-icon-drag\"></use></svg></button></span>";
                        printf(
                            "<label for='step_title[%d]' class='u-sr-only'>%s</label>",
                            $numsteps,
                            $item->steps[$i]['title']
                        );
                        printf(
                            "<input type='text' id='step_title[%d]' name='step_title[%d]' maxlength='100' value='%s' />",
                            $numsteps, $numsteps,
                            $item->steps[$i]['title']
                        );
                        printf(
                            "<input type='hidden' name='step[%d]' value='%d'>",
                            $numsteps,
                            $item->steps[$i]['id']
                        );
                        echo "<button class='js-delete delete-trigger' type='button' aria-label='Delete this item.'><svg aria-hidden='true'><use xlink:href='#lux-icon-x'></use></svg></button>";
                        echo "</li>";
                        $numsteps++;
                    }
                    ?>
                </ol>
            </div>
            <div id="step_items" class="step-items step-details">
                <?php
                $numsteps = 0;
                foreach ($item->steporder as $i) {
                    printf('<div id="step_text_%d" class="step-item-display-%d">', $numsteps, $numsteps);
                    echo "<textarea rows=24 name=\"step_text[$numsteps]\" class=\"step-item\"> ";
                    echo htmlspecialchars($item->steps[$i]['text']);
                    echo "</textarea>";
                    echo  "</div>";
                    $numsteps++;
                } ?>
            </div>
        </div>
        <button id="desmos_form_submit_button" class="button --button-primary -offset" type="submit" name="submitbtn" value="Submit">Save and Exit</button>
        <button id="desmos_preview_button" class="desmos button --button-secondary -offset" type="button">Preview</button>
    </form>
    <?php include 'icons.svg'; ?>
</div>

<div id="desmos_preview_container" style="display: none;">
    <div id="desmos_previewmode_buttons">
        <button id="desmos_return_to_edit_button" class="desmos button" type="button">Back to Edit</button>
        <span id="desmos_preview_warning">
            <img id="desmos_preview_warning_image" src="/ohm/img/warning.svg"
                 onerror="this.src='/ohm/img/warning.png'" alt="Warning">
            Desmos graph changes in the preview are not saved for students.
        </span>
    </div>

    <div id="desmos_preview_content"></div>
</div>
