<link rel="stylesheet" href="/desmos/temp_desmos.css" type="text/css" />
<?php
if ("preview" == $_GET['mode']) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
    require_once(__DIR__ . '/../../includes/sanitize.php');
    $item = new \Desmos\Models\DesmosItem();
    $item->setName($_POST['title']);
    $item->setSummary($_POST['summary']);
    // Build steps array
    $steps = [];
    for ($i = 0; $i < count($_POST['step']); $i++) {
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
    <div class="steps-left">
        <ul id="step_list" class="step-box">
            <?php
            $action = '';
            if (count($item->steps)>1) {
                $action = "onClick=\"showSteps('#desmos_view_container', %d)\"";
            }
            for ($i=0; $i<count($item->steps); $i++) {
                $selected = '';
                if ($i==0) {
                    $selected = "selected";
                }
                printf("<li class=\"step-li view $selected\" $action>", $i);
                // printf(
                //     "<input type='text' name='step_title[$d]' value='%s' />",
                //     $item->steps[$i]['title']
                // );
                printf($item->steps[$i]['title']);
                printf("<input type='hidden' name='step[%d]' value='%d'>", $i, $item->steps[$i]['id']);
                echo "</li>";
            }
            ?>
        </ul>
    </div>
    <div class="steps-right">
        <div class="step-items">
            <?php
            for ($i=0; $i<count($item->steps); $i++) {
                echo "<div class=\"step-item";
                if ($i>0) echo " hidden";
                echo "\">";
                echo $item->steps[$i]['text'];
                echo "</div>";
            } ?>
        </div>
        <div class="desmos-nav-btns -inset">
            <button class="button --primary">Previous</button>
            <button class="button --primary">Next</button>
        </div>
    </div>
</div>
</div>