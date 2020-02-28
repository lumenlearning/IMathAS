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

?>
<div class="lux-component preview-header">
    <button id="js-return-to-edit" class="button" type="button">Back to Edit</button>
    <span class="u-padding-xs preview-warning">
        <img src="/ohm/img/warning.svg" onerror="this.src='/ohm/img/warning.png'" alt="" aria-hidden="true" class="u-margin-right-xs">
        Desmos graph changes in the preview are not saved for students.
    </span>
</div>

<?php
require_once(__DIR__ . '/view_content.php');
