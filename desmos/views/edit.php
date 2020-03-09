<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<link rel="stylesheet" href="/desmos/ohm-modal.css" type="text/css" />

<script type="text/javascript" src="<?php echo $imasroot; ?>/desmos/js/ohmModal.js"></script>
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
<div id="desmos_edit_container" class="lux-component">
    <h1 class="desmos-heading">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="u-margin-right-xs"><defs><linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="a"><stop stop-color="#0F6633" offset="0%"/><stop stop-color="#158F48" offset="100%"/></linearGradient></defs><g fill="none" fill-rule="evenodd"><rect fill="url(#a)" width="24" height="24" rx="4"/><g fill="#FFF"><path d="M14.573 17.701l-.028.024c-.873.775-1.817 1.176-2.811 1.18h-.023c-1.014 0-1.97-.424-2.919-1.307-.476-.438-.92-.947-1.335-1.508.023-.018.042-.042.065-.06.252-.243.51-.495.766-.752l.121-.122.215-.214c.019-.019.042-.042.065-.061l.01.01c.392.569.752.994 1.116 1.335.509.467 1.111.97 1.881.97.467 0 .93-.177 1.42-.536.023.023.047.042.07.06.35.313.668.556.98.748.108.065.215.126.327.182a.167.167 0 00.08.051z"/><path d="M22.165 11.953a2.927 2.927 0 00-.878.224c.122-.05.248-.088.374-.121a3.288 3.288 0 00-.79.332l-.037.023c-.037.019-.07.042-.107.065 0 0-.005 0-.005.005-.033.019-.06.042-.093.06-.019.015-.038.024-.06.038l-.048.033c-.723.509-1.335 1.186-1.97 1.793-.304.29-.607.588-.934.859a.064.064 0 01-.019.014l-.135.107c-.374.285-.808.542-1.293.514-.355-.024-.673-.178-.976-.36a5.27 5.27 0 01-.42-.299c.28.234.583.444.91.56-.373-.13-.714-.377-1.027-.653.037.033.075.06.112.093-.22-.182-.42-.382-.602-.565l-.09-.088c-.037-.042-.079-.08-.116-.122l-.43-.443c-.205-.215-.41-.425-.625-.63a6.238 6.238 0 00-1.284-.981c.065.037.13.08.196.121-.621-.378-1.298-.611-2.008-.574.182-.014.369-.01.551.014a2.94 2.94 0 00-.901.023h-.01a2.52 2.52 0 00-.182.038c-.046.009-.088.023-.135.037-.01 0-.014.005-.023.005-.066.018-.131.042-.196.065a3.698 3.698 0 00-.78.392c-.35.23-.663.505-.967.794.084-.08.168-.154.252-.229-.154.136-.303.28-.452.425l.079-.08-1.041.995c-.16.154-.318.309-.481.463-.033.032-.066.06-.098.093-.005.005-.01.005-.01.01-.009.009-.018.013-.023.023-.033.028-.06.056-.093.084-.014.01-.024.023-.038.032a1.208 1.208 0 00-.084.075.327.327 0 01-.046.037l-.08.066c-.033.028-.065.051-.098.075a.102.102 0 00-.023.018l-.047.033c-.005 0-.005.005-.01.005-.317.224-.662.392-1.05.41h-.126a1.619 1.619 0 01-.598-.135c.108.042.22.08.337.103-.355-.075-.677-.257-.972-.49a.064.064 0 00-.018-.014c-.01-.005-.014-.014-.024-.02l-.07-.055c-.705-.575-1.312-1.275-1.98-1.905-.004-.005-.009-.005-.009-.01-.075-.07-.154-.144-.229-.21-.004-.004-.014-.009-.018-.018-.024-.02-.042-.038-.066-.056v2.325c.126.13.253.266.379.392-.126-.126-.253-.261-.379-.392v.019c.505.518 1.028 1.04 1.62 1.461.65.458 1.518.836 2.33.728.304-.023.589-.093.864-.196a3.01 3.01 0 01-1.069.196c.476-.009.943-.126 1.378-.336a3.861 3.861 0 01.004 0c.266-.13.514-.294.752-.471.495-.37.943-.817 1.382-1.256.27-.267.542-.537.808-.808.145-.15.303-.308.476-.458-.014.01-.028.024-.042.033.037-.033.08-.065.117-.098-.028.023-.052.042-.075.065a3.96 3.96 0 01.397-.294l.042-.028c.27-.163.565-.28.897-.308.298-.023.579.051.835.191.201.112.388.257.547.388.177.145.34.294.504.448.047.042.089.084.13.126.052.052.108.103.16.159.102.103.205.21.308.317-.02-.018-.033-.037-.052-.05.06.06.122.12.178.186-.042-.042-.084-.089-.131-.136.205.215.41.435.62.64l.006.005c.467.467.957.91 1.531 1.228.01.005.014.01.023.014l.08.042.056.028a14625.26 14625.26 0 00.065.033h-.004c.014.009.032.014.046.023h-.004c.336.163.686.275 1.06.308.462.07.924 0 1.358-.154a3.141 3.141 0 00.047-.014c-.014.005-.033.01-.047.019l.042-.014c.01-.005.019-.01.028-.01l.042-.014c.038-.014.075-.028.108-.042.004-.004.009-.004.018-.004a.557.557 0 00.075-.038c.019-.01.042-.018.06-.028.038-.018.07-.037.108-.056.014-.004.028-.014.042-.023.047-.023.094-.051.136-.075 0 0 .004 0 .004-.004.075-.043.145-.09.22-.136.032-.019.06-.042.093-.06.01-.01.019-.015.028-.024.028-.019.056-.037.084-.06.01-.005.014-.015.024-.02l.056-.041c.765-.58 1.424-1.303 2.1-1.98l.024-.023c.065-.066.13-.131.196-.192.13-.126.262-.247.402-.364.112-.093.229-.178.35-.262l.023-.014c.052 0 .154-.088.21-.121.01-.005.024-.01.038-.019.023-.009.046-.023.07-.032a1.552 1.552 0 011.036-.098c.15.042.29.098.425.172.005 0 .005.005.01.005.018.01.037.019.056.033l.018.014.042.028c.01.004.019.01.028.018l.038.024c.01.004.018.014.028.018.014.01.023.02.037.024.01.004.014.01.023.014l.047.032c.005 0 .005.005.01.005.102.075.2.154.294.238.004.005.014.01.018.019v-1.998a3.034 3.034 0 00-1.774-.365zM.509 15.847l-.056-.056.126.127-.07-.07z"/><path d="M22.142.047c-.253.966-.458 1.737-.458 1.737-.766 2.792-1.695 5.818-2.858 8.708-.555 1.377-1.326 3.175-2.4 4.875a.46.46 0 01-.112.014c-.018.004-.037.004-.056.004-.15 0-.308-.042-.476-.117-.303-.14-.57-.34-.831-.574-.023-.019-.042-.037-.065-.06l.168-.267c.952-1.49 1.643-3.105 2.25-4.632 1.079-2.712 2.05-5.733 3.03-9.427 0 0 .02-.056.066-.252M3.026.047l.112.462c.737 2.76 1.713 6.07 2.974 9.25.327.826.728 1.797 1.2 2.764l-.07.056c-.266.229-.514.467-.752.705-.13.126-.266.262-.401.388-.024.023-.047.042-.066.065l-.009-.023c-.556-1.088-1.018-2.228-1.396-3.199a86.47 86.47 0 01-2.9-8.727l-.004.005C1.56 1.214 1.41.63 1.265.047"/></g></g></svg>
        <?php echo $pagetitle ?>
    </h1>
    <form id="desmos_item" class="desmos form u-margin-bottom-xs lux-form" enctype="multipart/form-data" method="post" action="<?php echo $page_formActionTag ?>">
        <div class="form-group">
            <div class="controls">
                <label for="title" class="form-label">Title:</label>
                <input type="text" id="title" class="form-input form-input--fw" name="title" value="<?php echo str_replace('"','&quot;',$item->title);?>" required />
            </div>
            <div class="controls">
                <label for="sdate" class="form-label">Start Date:</label>
                <input type="text" class="form-input has-icon icon--suffix icon--calendar" onClick="displayDatePicker('sdate', this); return false" id="sdate" name="sdate" value="<?php echo $sdate;?>"/>
            </div>
            <div class="controls">
                <label for="edate" class="form-label">End Date:</label>
                <input type="text" class="form-input has-icon icon--suffix icon--calendar" onClick="displayDatePicker('edate', this); return false" id="edate" name="edate" value="<?php echo $edate;?>"/>
            </div>
        </div>
        <div class="controls u-margin-top-sm">
            <label for="summary" class="form-label">Summary:</label>
            <!-- <input type="text" id="summary" name="summary" value="<?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>" /> -->
            <textarea name="summary" id="summary" rows="5">
                <?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>
            </textarea>
        </div>
        <div id="step_box" class="u-margin-top-sm desmos desmos-steps">
            <div class="steps-navigation teacher-view">
                <div class="step-controls u-padding-sm">
                    <button class="button js-add" type="button">Add</button>
                </div>
                <span id="step-notifications" aria-live="assertive" class="u-sr-only"></span>
                <span id="step-directions" class="u-sr-only">Press spacebar to toggle drag-and-drop mode, use arrow keys to move selected elements.</span>
                <ol id="step_list" class="js-step-list step-list" data-description="step-directions" data-liveregion="step-notifications">
                    <!-- Changes to step markup must also be duplicated in the addStep JS -->
                    <?php
                    $action = '';
                    $numsteps = 0;
                    $action = "onClick=\"showSteps('desmos_edit_container', this)\"";
                    $keyaction = "onkeydown=\"javascript: if(event.keyCode == 9) showSteps('desmos_edit_container', this);\"";
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
                            "<input type='text' id='step_title[%d]' class='form-input' name='step_title[%d]' maxlength='100' value='%s' />",
                            $numsteps, $numsteps,
                            $item->steps[$i]['title']
                        );
                        printf(
                            "<input type='hidden' name='step[%d]' value='%d'>",
                            $numsteps,
                            $item->steps[$i]['id']
                        );
                        echo "<button class='js-delete u-button-reset delete-trigger' type='button' aria-label='Delete this item.'><svg aria-hidden='true'><use xlink:href='#lux-icon-x'></use></svg></button>";
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
        <div id="desmos_save_buttons" class="u-margin-top-sm">
            <button id="desmos_form_submit_button" class="button button--primary" type="submit" name="submitbtn" value="Submit">Save</button>
            </form>
            <?php
            $blockParam = 0 == strlen($_GET['block']) ? '' : '&block=' . intval($_GET['block']);
            $tbParam = 0 == strlen($_GET['rb']) ? '' : '&tb=' . Sanitize::encodeUrlParam($_GET['tb']);
            $typeIdParam = empty($typeid) ? '' : '&id=' . intval($typeid);
            $previewUrl = sprintf('%s/course/itempreview.php?cid=%d&type=%s&id=%d%s%s%s',
                $basesiteurl, $cid, $type, $typeid, $typeIdParam, $blockParam, $tbParam);
            ?>
            <form id="desmos_preview_form" method="POST" action="<?php echo $previewUrl; ?>" class="u-inline-block">
                <input id="desmos_edit_form_data" type="hidden" name="desmos_form_data"/>
                <button id="desmos_preview_button" class="u-margin-left-xs button" type="button">Preview</button>
            </form>
            <span id="desmos_save_status"></span>
        </div>
    <?php include 'icons.svg'; ?>
</div>
