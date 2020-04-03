<?php

namespace OHM\Services;

use OHM\Models\Banner;
use PDO;

class OhmBannerService
{
    private $dbh;
    private $userRights;
    private $bannerId;

    private $displayOnlyOncePerBanner; // only show the teacher and student banners once each.
    private $teacherBannerDisplayed;
    private $studentBannerDisplayed;

    /**
     * OhmBanner constructor.
     *
     * @param PDO $dbh A database connection.
     * @param int $userRights The user's rights from imas_users.
     * @param int $bannerId The banner ID to be displayed.
     */
    public function __construct(PDO $dbh, int $userRights, int $bannerId)
    {
        $this->dbh = $dbh;
        $this->setUserRights($userRights);
        $this->setBannerId($bannerId);
    }

    /**
     * Show the teacher banner if the user is a teacher.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see displayOnlyOncePerBanner
     */
    public function showTeacherBannerForTeachersOnly(): bool
    {
        if (15 >= $this->userRights) {
            return false;
        }
        if ($this->displayOnlyOncePerBanner && $this->teacherBannerDisplayed) {
            return false;
        }
        $this->teacherBannerDisplayed = true;
        return $this->showTeacherBanner();
    }

    /**
     * Show the student banner if the user is a student.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see displayOnlyOncePerBanner
     */
    public function showStudentBannerForStudentsOnly(): bool
    {
        if (15 < $this->userRights) {
            return false;
        }
        if ($this->displayOnlyOncePerBanner && $this->studentBannerDisplayed) {
            return false;
        }
        $this->studentBannerDisplayed = true;
        return $this->showStudentBanner();
    }

    /**
     * Show teacher banners. User rights are not checked.
     *
     * // FIXME: Implement these!
     * This happens only if:
     * - The banner is enabled.
     * - The current time falls between banner start and end times.
     * - The user has never dismissed the banner.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see showTeacherBannerForTeachersOnly
     */
    public function showTeacherBanner(): bool
    {
        $banner = new Banner($this->dbh);
        $banner->find($this->bannerId);

        // Make the banner data available to the view.
        $bannerId = $banner->getId();
        $bannerTitle = $banner->getTeacherTitle();
        $bannerContent = $banner->getTeacherContent();
        $bannerDismissible = $banner->getDismissible();

        include(__DIR__ . '/../views/banner/show_teacher.php');

        return true;
    }

    /**
     * Show student banners. User rights are not checked.
     *
     * // FIXME: Implement these!
     * This happens only if:
     * - The banner is enabled.
     * - The current time falls between banner start and end times.
     * - The user has never dismissed the banner.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see showStudentBannerForStudentsOnly
     */
    public function showStudentBanner(): bool
    {
        $banner = new Banner($this->dbh);
        $banner->find($this->bannerId);

        // Make the banner ID available to the view.
        $bannerId = $banner->getId();
        $bannerTitle = $banner->getStudentTitle();
        $bannerContent = $banner->getStudentContent();
        $bannerDismissible = $banner->getDismissible();

        include(__DIR__ . '/../views/banner/show_student.php');

        return true;
    }

    /*
     * Getters, setters
     */

    /**
     * Set the user's rights.
     *
     * @param int $rights The user's rights from imas_users.
     * @return OhmBannerService
     */
    public function setUserRights(int $rights): OhmBannerService
    {
        $this->userRights = $rights;
        return $this;
    }

    /**
     * Set the banner ID to be displayed.
     *
     * @param int $bannerId The banner ID.
     * @return OhmBannerService
     */
    public function setBannerId(int $bannerId): OhmBannerService
    {
        $this->bannerId = $bannerId;
        return $this;
    }

    /**
     * Set whether each banner is displayed only once or not.
     *
     * @param bool $displayOnlyOncePerBanner Set to true to display each banner only once.
     * @return OhmBannerService
     */
    public function setDisplayOnlyOncePerBanner(bool $displayOnlyOncePerBanner): OhmBannerService
    {
        $this->displayOnlyOncePerBanner = $displayOnlyOncePerBanner;
        return $this;
    }
}
