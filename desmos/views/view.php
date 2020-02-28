<link rel="stylesheet" href="/desmos/desmos-temp.css" type="text/css" />
<script type="text/javascript">
    window.onload = ()=> {
        showSteps("desmos_view_container", document.getElementById("step_list").children[0]);
    }
</script>
<?php
if ($shownav) {
    if (!isset($usernameinheader) || $usernameinheader==false) {
        echo '<span class="floatright hideinmobile">';
        if ($userfullname != ' ') {
            echo "<a href=\"#\" onclick=\"GB_show('"._('User Preferences')."','$imasroot/admin/ltiuserprefs.php?cid=$cid&greybox=true',800,'auto');return false;\" title=\""._('User Preferences')."\" aria-label=\""._('Edit User Preferences')."\">";
            echo "<span id=\"myname\">".Sanitize::encodeStringForDisplay($userfullname)."</span> ";
            echo "<img style=\"vertical-align:top\" src=\"$imasroot/img/gears.png\" alt=\"\"/></a>";
        } else {
            echo "<a href=\"#\" onclick=\"GB_show('"._('User Preferences')."','$imasroot/admin/ltiuserprefs.php?cid=$cid&greybox=true',800,'auto');return false;\">";
            echo "<span id=\"myname\">".('User Preferences')."</span>";
        }
        echo '</span>';
    }
    echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
}

require_once(__DIR__ . '/view_content.php');
