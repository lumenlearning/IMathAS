<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<script type="text/javascript">
    window.onload = ()=> {
        showSteps("desmos_view_container", document.getElementById("step_list").children[0]);
    }
</script>
<?php
if ($shownav) {
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}

// De-serialize Desmos edit form data.
$serializedData = base64_decode($_POST['desmos_form_data']);
parse_str($serializedData, $desmosFormData);

$item = new \Desmos\Models\DesmosItem();
$item->fromFormData($desmosFormData);
$pagetitle = Sanitize::encodeStringForDisplay($item->name);

$block = 0 == strlen($_GET['block']) ? '' : '&block=' . intval($_GET['block']);
$tb = 0 == strlen($_GET['rb']) ? '' : '&tb=' . Sanitize::encodeUrlParam($_GET['tb']);
$editUrl = sprintf('%s/course/itemadd.php?mode=returning_from_preview&type=%s&id=%d&cid=%d%s%s',
    $basesiteurl, $type, $itemId, $cid, $block, $tb);
?>
<div class="lux-component preview-header">
    <form method="POST" action="<?php echo $editUrl ?>">
        <input type="hidden" name="desmos_form_data" value="<?echo $serializedData; ?>"/>
        <input type="submit" class="button" value="Back to Edit"/>
    </form>
    <span class="u-padding-xs preview-warning">
        <img src="/ohm/img/warning.svg" onerror="this.src='/ohm/img/warning.png'" alt="" aria-hidden="true" class="u-margin-right-xs">
        Desmos graph changes in the preview are not saved for students.
    </span>
</div>

<?php
require_once(__DIR__ . '/view_content.php');
