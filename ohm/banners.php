<?php

use OHM\Models\Banner;
use OHM\Services\OhmBannerService;

require_once(__DIR__ . '/../init.php');
$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>";
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.2/lux-components.min.css\">";
require_once("../header.php");

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

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
    case "create_form":
        modify_form('Create');
        break;
    case "modify_form":
        modify_form('Modify');
        break;
    case "save":
        save();
        break;
    case "delete":
        delete();
        break;
    case "index":
    default:
        list_banners();
        break;
}

include(__DIR__ . '/../footer.php');

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
        $startAt = is_null($row['start_at']) ? 'Immediately' : $row['start_at'];
        $endAt = is_null($row['end_at']) ? 'Never' : $row['end_at'];

        $confirmJs = sprintf('onClick="return confirm(\'Are you sure you want to delete the banner: %s?\')"',
            Sanitize::encodeStringForDisplay($row['description']));

        $viewLink = sprintf('<a href="?action=view&id=%s">View</a>', $row['id']);
        $modifyLink = sprintf('<a href="?action=modify_form&id=%s">Modify</a>', $row['id']);
        $deleteLink = sprintf('<a href="?action=delete&id=%s" %s>Delete</a>', $row['id'], $confirmJs);

        printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>\n",
            $row['id'],
            $isEnabled,
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
    global $DBH, $myrights;

    $bannerId = intval($_GET['id']);
    $ohmBannerService = new OhmBannerService($DBH, $myrights, $bannerId);
    $ohmBannerService->setDisplayOnlyOncePerBanner(false);

    echo '<h1>Teacher Banner</h1>';
    $ohmBannerService->showTeacherBanner();
    echo '<h1>Student Banner</h1>';
    $ohmBannerService->showStudentBanner();
}

function delete()
{
    global $DBH;

    $bannerId = intval($_GET['id']);
    $banner = new Banner($DBH);
    if (!$banner->find($bannerId)) {
        printf('Banner ID %d not found.', $bannerId);
        return;
    }
    $banner->delete();

    printf('<p>Deleted banner: %s</p>', $banner->getDescription());
    echo '<a href="?">&lt;&lt; Return to OHM banner listing</a>';
}

function save()
{
    global $DBH;

    $banner = new Banner($DBH);
    $bannerId = intval($_POST['id']);
    if (!empty($bannerId)) {
        $banner->find($bannerId);
    }
    $banner
        ->setEnabled($_POST['is-enabled'] ? true : false)
        ->setDismissible($_POST['is-dismissible'] ? true : false)
        ->setDisplayStudent($_POST['display-student'] ? true : false)
        ->setDisplayTeacher($_POST['display-teacher'] ? true : false)
        ->setDescription($_POST['description'])
        ->setTeacherTitle($_POST['teacher-title'])
        ->setTeacherContent($_POST['teacher-content'])
        ->setStudentTitle($_POST['student-title'])
        ->setStudentContent($_POST['student-content']);

    if ('1' != $_POST['has-start-at']) {
        $banner->setStartAt(null);
    } else {
        $dateTime = DateTime::createFromFormat('m/d/Y H:i:s', $_POST['sdate'] . ' ' . $_POST['stime']);
        $banner->setStartAt($dateTime);
    }

    if ('1' != $_POST['has-end-at']) {
        $banner->setEndAt(null);
    } else {
        $dateTime = DateTime::createFromFormat('m/d/Y H:i:s', $_POST['edate'] . ' ' . $_POST['etime']);
        $banner->setEndAt($dateTime);
    }

    $banner->save();

    printf('<p>Saved banner: %s</p>', $banner->getDescription());
    echo '<a href="?">&lt;&lt; Return to OHM banner listing</a>';
}

function modify_form($action)
{
    global $DBH;

    $bannerId = intval($_GET['id']);

    // Make variables available for the view.
    $action = Sanitize::simpleString($action);

    if ('modify' == strtolower($action)) {
        $banner = new Banner($DBH);
        $banner->find($bannerId);
        $id = $banner->getId();
        $isEnabled = $banner->getEnabled();
        $isDismissible = $banner->getDismissible();
        $displayTeacher = $banner->getDisplayTeacher();
        $displayStudent = $banner->getDisplayStudent();
        $description = $banner->getDescription();
        $teacherTitle = $banner->getTeacherTitle();
        $teacherContent = $banner->getTeacherContent();
        $studentTitle = $banner->getStudentTitle();
        $studentContent = $banner->getStudentContent();
        $startAt = is_null($banner->getStartAt()) ? null : $banner->getStartAt()->getTimestamp();
        $endAt = is_null($banner->getEndAt()) ? null : $banner->getEndAt()->getTimestamp();

        if (is_null($banner->getStartAt())) {
            $hasStartAt = false;
        } else {
            $hasStartAt = true;
            $startDate = $banner->getStartAt()->format('m/d/Y');
            $startTime = $banner->getStartAt()->format('h:m:s');
        }
        if (is_null($banner->getEndAt())) {
            $hasEndAt = false;
        } else {
            $hasEndAt = true;
            $endDate = $banner->getEndAt()->format('m/d/Y');
            $endTime = $banner->getEndAt()->format('h:m:s');
        }
    } else {
        $id = '';
        $isEnabled = true;
        $isDismissible = true;
        $displayTeacher = true;
        $displayStudent = true;
        $description = '';
        $teacherTitle = '';
        $teacherContent = '';
        $studentTitle = '';
        $studentContent = '';
        $hasStartAt = false;
        $hasEndAt = false;
        $startTime = '23:59:59';
        $endTime = '23:59:59';
    }

    include(__DIR__ . '/views/banner/edit_banner.php');
}
