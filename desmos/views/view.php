<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<?php if (count($item->steps) > 1) { ?>
<script type="text/javascript">
    window.onload = ()=> {
        showSteps("desmos_view_container", document.getElementById("step_list").children[0]);
    }
</script>
<?php } ?>
<?php
if ($shownav) {
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}

require_once(__DIR__ . '/view_content.php');
