<link rel="stylesheet" href="/desmos/temp_desmos.css" type="text/css" />
<?php
if ($shownav) {
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}
?>
<div id="headerviewwiki" class="pagetitle"><h1><?php echo $pagetitle ?></h1></div>
<div class=itemsum>
    <?php echo $item->summary; ?>
</div>

<div id="step_box">
    <div id="step_list" class="step-list">
        <h2>Steps</h2>
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
            printf("<span class=\"step-li $selected\" $action>", $i);
            printf(
                "<input type='text' name='step_title[$d]' value='%s' />",
                $item->steps[$i]['title']
            );
            printf("<input type='hidden' name='step[%d]' value='%d'>", $i, $item->steps[$i]['id']);
            echo "</span>";
        }
        ?>
    </div>
    <div id="step_items" class="step-items">
    <?php
    for ($i=0; $i<count($item->steps); $i++) {
        echo "<div class=\"step-item";
        if ($i>0) echo " hidden";
        echo "\">";
        echo $item->steps[$i]['text'];
        echo "</div>";
    } ?>
    </div>
</div>
