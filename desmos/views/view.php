<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<?php
if ("preview" == $_GET['mode']) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
    require_once(__DIR__ . '/../../includes/sanitize.php');
    $item = new \Desmos\Models\DesmosItem();
    $item->setName($_POST['title']);
    $item->setSummary($_POST['summary']);
    // Build steps array
    $steps = [];
    for ($i = 0; $i < count($_POST['step_title']); $i++) {
        $steps[] = [
            'id' => intval($_POST['step'][$i]),
            'title' => $_POST['step_title'][$i],
            'text' => $_POST['step_text'][$i],
        ];
    };
    $item->setSteps($steps);
    $pagetitle = Sanitize::encodeStringForDisplay($item->name);
}
if ($shownav) {
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}
?>
<div id="desmos_view_container">
    <div class="desmos-header">
        <h1><img src="../ohm/img/desmos.png" alt=""/> <?php echo $pagetitle ?></h1>
        <p><?php echo $item->summary; ?></p>
    </div>

    <div id="step_box" class="desmos desmos-student-view -offset --xlarge">
        <div class="steps-navigation">
            <ol id="step_list" class="js-step-list step-list" role="listbox">
                <?php
                $clickaction = '';
                $keyaction = '';
                if (count($item->steps)>1) {
                    $clickaction = "onclick=\"showSteps('desmos_view_container', this);\"";
                    $keyaction = "onkeydown=\"javascript: if(event.code === 'Space') showSteps('desmos_view_container', this);\"";
                }
                for ($i=0; $i<count($item->steps); $i++) {
                    $selected = '';
                    $ariastate = 'false';
                    if ($i==0) {
                        $selected = "is-selected";
                        $ariastate = "true";
                    }
                    printf("<li role=\"option\" tabindex=\"0\" class=\"step-li view $selected\" $clickaction $keyaction>", $i, $i);
                    echo $item->steps[$i]['title'];
                    //printf("<input type='hidden' name='step[%d]' value='%d'>", $i, $item->steps[$i]['id']);
                    echo "</li>";
                }
                ?>
            </ol>
        </div>
        <div class="steps-details">
            <div class="step-items">
                <?php
                for ($i=0; $i<count($item->steps); $i++) {
                    $displayed = 0 == $i ? 'block' : 'none';
                    printf('<div id="step-item-display-%d" style="display: %s;" class="step-item-display-%d step-item">', $i, $displayed, $i);
                    echo $item->steps[$i]['text'];
                    echo "</div>";
                } ?>
            </div>
        </div>
    </div>

    <?php include 'icons.svg'; ?>

</div>
