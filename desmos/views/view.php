<link rel="stylesheet" href="https://lux.lumenlearning.com/use-lux/1.0.0/lux-components.min.css" type="text/css" />
<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<script type="text/javascript">
    window.onload = ()=> {
        showSteps("desmos_view_container", document.getElementById("step_list").children[0]);
    }
</script>
<?php
if ("preview" == $_GET['mode']) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
    require_once(__DIR__ . '/../../includes/sanitize.php');
    $item = new \Desmos\Models\DesmosItem();
    $item->setName($_POST['title']);
    $item->setSummary($_POST['summary']);
    // Build steps array
    $steps = [];
    foreach ($_POST['step_title'] as $key => $title) {
        $steps[$key] = [
            "title" => $title,
            "text" => $_POST['step_text'][$key],
            "id" => $_POST['step'][$key],
        ];
    }
    $item->setSteps($steps);
    $pagetitle = Sanitize::encodeStringForDisplay($item->name);
}
if ($shownav) {
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}
?>
<div id="desmos_view_container">
    <div class="desmos-header">
        <h1 class="-small-type desmos-header"><img src="../ohm/img/desmos.png" alt=""/> <?php echo $pagetitle ?></h1>
        <p><?php echo $item->summary; ?></p>
    </div>

    <div id="step_box" class="desmos desmos-student-view -offset --xlarge">
        <div class="lux-component steps-navigation">
            <nav id="mobile_nav" class="lux-form">
                <select name="step_nav" id="js-step-nav" class="form-input step-nav">
                <?php
                    $numsteps = 0;
                    foreach ($item->steporder as $i) {
                        $selected = '';
                        if ($i==$item->steporder[0]) {
                            $selected = "selected";
                        }
                        $title = ($numsteps + 1) . ". " . $item->steps[$i]['title'];
                        printf("<option $selected value=\"$numsteps\" data-num=\"$numsteps\">", $i, $i);
                        echo $title;
                        echo "</option>";
                        $numsteps++;
                    }
                ?>
                </select>
                <div class="js-desmos-nav button-group">
                    <button aria-label="Previous" type="button" class="u-padding-xs button js-prev" disabled><svg aria-hidden="true"><use xlink:href="#lux-icon-caret-left"></use></svg></button>
                    <button aria-label="Next" type="button" class="u-padding-xs button js-next"><svg aria-hidden="true"><use xlink:href="#lux-icon-caret-right"></use></svg></button>
                </div>
            </nav>
            <ol id="step_list" class="js-step-list step-list" role="listbox">
                <?php
                $numsteps = 0;
                $clickaction = '';
                $keyaction = '';
                if (count($item->steps)>1) {
                    $clickaction = "onclick=\"showSteps('desmos_view_container', this);\"";
                    $keyaction = "onkeydown=\"javascript: if(event.code === 'Space') showSteps('desmos_view_container', this);\"";
                }
                foreach ($item->steporder as $i) {
                    $selected = '';
                    $ariastate = 'false';
                    if ($i==$item->steporder[0]) {
                        $selected = "is-selected";
                        $ariastate = "true";
                    }
                    printf("<li role=\"option\" tabindex=\"0\" class=\"step-li view $selected\" $clickaction $keyaction data-num=\"$numsteps\">", $i, $i);
                    echo $item->steps[$i]['title'];
                    //printf("<input type='hidden' name='step[%d]' value='%d'>", $i, $item->steps[$i]['id']);
                    echo "</li>";
                    $numsteps++;
                }
                ?>
            </ol>
        </div>
        <div class="steps-details">
            <div class="step-items">
                <?php
                $numsteps = 0;
                foreach ($item->steporder as $i) {
                    $displayed = $item->steporder[0] == $i ? 'block' : 'none';
                    printf('<div id="step-item-display-%d" style="display: %s;" class="step-item-display-%d step-item">', $numsteps, $displayed, $numsteps);
                    echo $item->steps[$i]['text'];
                    echo "</div>";
                    $numsteps++;
                } ?>
            </div>
            <div class="js-desmos-nav desmos-nav-btns lux-component">
                <button type="button" class="button js-prev" disabled>Previous</button>
                <button type="button" class="button js-next">Next</button>
            </div>
        </div>
    </div>

    <?php include 'icons.svg'; ?>

</div>
