<?php
if ($shownav) {
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}
?>
<div id="headerviewwiki" class="pagetitle"><h1><?php echo $pagetitle ?></h1></div>
<div class=itemsum>
    <?php echo $row['summary']; ?>
</div>