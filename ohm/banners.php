<?php

use OHM\Exceptions\DatabaseWriteException;
use OHM\Models\Banner;
use OHM\Services\OhmBannerService;

require_once(__DIR__ . '/../init.php');
$placeinhead .= '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>';
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.2/lux-components.min.css\">";
$placeinhead .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';
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
        <a href="?">Notifications</a>
    </div>
<?php


// Sanitize inputs
$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : null;


switch ($_REQUEST['action']) {
    case "view":
        view($id);
        break;
    case "create_form":
        modify_form('Create', null);
        break;
    case "modify_form":
        modify_form('Modify', $id);
        break;
    case "save":
        save($id);
        break;
    case "delete":
        delete($id);
        break;
    case "index":
    default:
        list_banners();
        break;
}


return;


/**
 * Display all banners. This is not paginated.
 */
function list_banners(): void
{
    global $DBH;

    ?>
    <link rel="stylesheet" type="text/css" href="views/banner/admin.css">

    <p>
        It is recommended that only one notification is scheduled per day for
        display purposes.
    </p>

    <form method="POST" action="?action=create_form" class="lux-component">
        <button type="submit" class="button u-margin-vertical-sm">Add</button>
    </form>

    <table class="banner-list gb" id="banner-list">
        <caption class="banner-list-title">Banner Notifications</caption>
        <thead>
        <tr>
            <th>Description</th>
            <th>Status</th>
            <th>Start</th>
            <th>End</th>
            <th colspan="3">Actions</th>
        </tr>
        </thead>
        <tbody>
    <?php

    $bannerRepository = new Banner($DBH);
    $bannerRepository->findAll();

    $alt = 1;
    while ($banner = $bannerRepository->next()) {
        if ($alt == 0) {
            echo "<tr class=\"even\">";
            $alt = 1;
        } else {
            echo "<tr class=\"odd\">";
            $alt = 0;
        }

        $isEnabled = $banner->getEnabled() ? 'Enabled' : '<span class="status-disabled">Disabled</span>';
        $startAt = is_null($banner->getStartAt()) ? 'Immediately' : getTimestampForList($banner->getStartAt());
        $endAt = is_null($banner->getEndAt()) ? 'None' : getTimestampForList($banner->getEndAt());

        $confirmJs = sprintf('onClick="return confirm(\'Are you sure you want to delete the banner: %s?\')"',
            Sanitize::encodeStringForDisplay($banner->getDescription()));

        $viewLink = sprintf('<a href="?action=view&id=%s" class="action-link">View</a>', $banner->getId());
        $modifyLink = sprintf('<a href="?action=modify_form&id=%s" class="action-link">Modify</a>', $banner->getId());
        $deleteLink = sprintf('<a href="?action=delete&id=%s" class="action-link" %s>Delete</a>', $banner->getId(), $confirmJs);

        printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>\n",
            Sanitize::encodeStringForDisplay($banner->getDescription()),
            $isEnabled,
            $startAt,
            $endAt,
            $viewLink,
            $modifyLink,
            $deleteLink
        );
        echo "</tr>\n";
    }
    ?>
        </tbody>
    </table>
<?php
}

/**
 * View a banner.
 *
 * @param int $bannerId The banner ID.
 */
function view(int $bannerId): void
{
    global $DBH, $userid, $myrights;

    $ohmBannerService = new OhmBannerService($DBH, $userid, $myrights);

    echo '<h1>Banner Preview</h1>';
    echo '<h2>Teacher Banner</h2>';
    $ohmBannerService->previewBanner($bannerId, OhmBannerService::TEACHER_ROLE);
    echo '<h2>Student Banner</h2>';
    $ohmBannerService->previewBanner($bannerId, OhmBannerService::STUDENT_ROLE);
}

/**
 * Delete a banner.
 *
 * @param int $bannerId The banner ID.
 * @throws DatabaseWriteException
 */
function delete(int $bannerId): void
{
    global $DBH;

    $banner = new Banner($DBH);
    if (!$banner->find($bannerId)) {
        printf('Banner ID %d not found.', $bannerId);
        return;
    }
    $banner->delete();

    printf('<p>Deleted banner: %s</p>', $banner->getDescription());
    echo '<a href="?">&lt;&lt; Return to OHM banner listing</a>';
}

/**
 * Save a new or existing banner.
 *
 * @param int|null $bannerId The banner ID, if saving an existing banner.
 * @throws DatabaseWriteException
 */
function save(?int $bannerId): void
{
    global $DBH;

    $banner = new Banner($DBH);
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

    $userTimezone = new DateTimeZone(getUserTimezoneName());

    if ('1' != $_POST['start-immediately']) {
        $dateTime = DateTime::createFromFormat('m/d/Y g:i A', $_POST['sdate'], $userTimezone);
        $banner->setStartAt($dateTime);
    } else {
        $banner->setStartAt(null);
    }

    if ('1' != $_POST['never-ending']) {
        $dateTime = DateTime::createFromFormat('m/d/Y g:i A', $_POST['edate'], $userTimezone);
        $banner->setEndAt($dateTime);
    } else {
        $banner->setEndAt(null);
    }

    $banner->save();

    view($banner->getId());
    echo '<a href="?">&lt;&lt; Return to OHM banner listing</a>';
}

/**
 * Display the HTML form for editing or creating a Banner.
 *
 * @param string $action One of: "Modify" or "Create"
 * @param int|null $bannerId The banner ID, if modifying a banner.
 */
function modify_form(string $action, ?int $bannerId): void
{
    global $DBH;

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
        $teacherTitle = Sanitize::encodeStringForDisplay($banner->getTeacherTitle());
        $teacherContent = $banner->getTeacherContent();
        $studentTitle = Sanitize::encodeStringForDisplay($banner->getStudentTitle());
        $studentContent = $banner->getStudentContent();

        if (is_null($banner->getStartAt())) {
            $startImmediately = true;
        } else {
            $startImmediately = false;
            $startDateTime = date('m/d/Y g:i A', $banner->getStartAt()->getTimestamp());
        }
        if (is_null($banner->getEndAt())) {
            $neverEnding = true;
        } else {
            $neverEnding = false;
            $endDateTime = date('m/d/Y g:i A', $banner->getEndAt()->getTimestamp());
        }
    } else {
        $id = '';
        $isEnabled = false;
        $isDismissible = true;
        $displayTeacher = true;
        $displayStudent = true;
        $description = '';
        $teacherTitle = '';
        $teacherContent = '';
        $studentTitle = '';
        $studentContent = '';
        $startImmediately = false;
        $neverEnding = false;
        $startDateTime = date('m/d/Y 12:00 AM', time());
        $endDateTime = date('m/d/Y 12:00 AM', time() + (86400 * 8));
    }

    include(__DIR__ . '/views/banner/edit_banner.php');
}


function getTimestampForList(DateTime $dateTime): string
{
    return date('n/j/Y, g:i:s A', $dateTime->getTimestamp());
}


function getUserTimezoneName(): string
{
    global $tzname;
    return (!isset($tzname) || empty($tzname)) ? 'America/Los_Angeles' : $tzname;
}
