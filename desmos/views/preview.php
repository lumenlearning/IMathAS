<link rel="stylesheet" href="../themes/lux-temp.css" type="text/css" />
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

// This was stored by /desmos/js/editItem.js.
$previewId = $_GET['preview_id'];
$serializedData = $_SESSION['tempSerializedPreviewData-' . $previewId];
parse_str($serializedData, $desmosFormData);

$item = new \Desmos\Models\DesmosItem();
$item->fromFormData($desmosFormData);
$pagetitle = Sanitize::encodeStringForDisplay($item->name);

?>
<div id="desmos_previewmode_buttons">
    <button id="desmos_return_to_edit_button" class="desmos button"
            type="button">Back to Edit
    </button>
    <span id="desmos_preview_warning">
        <img id="desmos_preview_warning_image" src="/ohm/img/warning.svg"
             onerror="this.src='/ohm/img/warning.png'" alt="Warning">
            Desmos graph changes in the preview are not saved for students.
    </span>
</div>

<?php
require_once(__DIR__ . '/view_content.php');
