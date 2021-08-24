<?php

use OHM\Tracking\FullStory;

/**
 * Insert FullStory snippet into the <head> element.
 */
function insertIntoHead(): void
{
    global $DBH, $userid, $cid;

    // For FullStory metadata.
    if (FullStory::isFullStoryEnabled() && !empty($cid) && 0 < $cid) {
        $stm = $DBH->prepare("SELECT id FROM imas_students WHERE userid=:userId AND courseid=:courseId");
        $stm->execute([':userId' => $userid, ':courseId' => $cid]);
        $GLOBALS['ohmEnrollmentId'] = $stm->fetchColumn(0);

        $stm = $DBH->prepare("SELECT ownerid FROM imas_courses WHERE id=:courseId");
        $stm->execute([':courseId' => $cid]);
        $GLOBALS['ohmCourseTeacherId'] = $stm->fetchColumn(0);
    }

    $fullStory = new FullStory();
    $fullStory::outputHeaderSnippet();
}
