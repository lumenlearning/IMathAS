<?php

use OHM\Services\OhmBannerService;

require_once(__DIR__ . '/../init.php');
require_once("../header.php");

?>
    <div class="breadcrumb">
        <?php echo $breadcrumbbase; ?>
        <a href="../admin/admin2.php">Admin</a> &gt;
        <a href="../util/utils.php">Utilities</a> &gt;
        <a href="?">OHM Banners</a>
    </div>
<?php


switch ($_REQUEST['action']) {
    case "view":
        view();
        break;
    case "modify_form":
        modify_form('modify');
        break;
    case "modify":
        modify();
        break;
    case "delete":
        delete();
        break;
    case "create_form":
        modify_form('create');
        break;
    case "create":
        create();
        break;
    case "index":
    default:
        list_banners();
        break;
}

return;


function list_banners()
{
    global $DBH;

    ?>
    <h1>OHM Banners</h1>

    <p>
        <a href="?action=create_form">Create new OHM Banner</a>
    </p>

    <table class="gb">
        <thead>
        <tr>
            <th>ID</th>
            <th>Enabled?</th>
            <th>Active?</th>
            <th>Description</th>
            <th>Start At</th>
            <th>End At</th>
            <th colspan="3">Actions</th>
        </tr>
        </thead>
        <tbody>
    <?php

    $stm = $DBH->query("SELECT id, is_enabled, description, start_at, end_at FROM ohm_notices");
    $stm->execute();

    $alt = 1;
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        if ($alt == 0) {
            echo "<tr class=\"even\">";
            $alt = 1;
        } else {
            echo "<tr class=\"odd\">";
            $alt = 0;
        }

        $isEnabled = $row['is_enabled'] ? 'Yes' : 'No';
        $isActive = '🤷';

        $startAt = '¯\_(ツ)_/¯';
        $endAt = '¯\_(ツ)_/¯';

        $confirmJs = sprintf('onClick="return confirm(\'Are you sure you want to delete the banner: %s?\')"',
            Sanitize::encodeStringForDisplay($row['description']));

        $viewLink = sprintf('<a href="?action=view&id=%s">View</a>', $row['id']);
        $modifyLink = sprintf('<a href="?action=modify_form&id=%s">Modify</a>', $row['id']);
        $deleteLink = sprintf('<a href="?action=delete&id=%s" %s>Delete</a>', $row['id'], $confirmJs);

        printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>\n",
            $row['id'],
            $isEnabled,
            $isActive,
            Sanitize::encodeStringForDisplay($row['description']),
            $startAt,
            $endAt,
            $viewLink,
            $modifyLink,
            $deleteLink
        );
        echo "</tr>\n";
    }
}

function view()
{
    $bannerId = intval($_GET['id']);
    $ohmBannerService = new OhmBannerService($GLOBALS['myrights'], $bannerId);
    $ohmBannerService->setDisplayOnlyOncePerBanner(false);

    echo '<h1>Teacher Banner</h1>';
    $ohmBannerService->showTeacherBanner();
    echo '<h1>Student Banner</h1>';
    $ohmBannerService->showStudentBannerForStudentsOnly();
}
