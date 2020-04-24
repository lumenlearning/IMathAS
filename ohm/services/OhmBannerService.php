<?php

namespace OHM\Services;

use OHM\Models\Banner;
use OHM\Models\BannerDismissal;
use PDO;

class OhmBannerService
{
    const TEACHER_ROLE = 1;
    const STUDENT_ROLE = 2;

    private $dbh;
    private $userId;
    private $userRights;

    private $displayOnlyOncePerBanner = true; // only show the teacher and student banners once per page.
    private $displayedBannerIds = []; // track which banner IDs have been displayed.

    // Used during unit testing.
    private $bannerForTesting;
    private $bannerDismissalForTesting;

    /**
     * OhmBanner constructor.
     *
     * @param PDO $dbh A database connection.
     * @param int $userId The user's ID from imas_users.
     * @param int $userRights The user's rights from imas_users.
     */
    public function __construct(PDO $dbh, int $userId, int $userRights)
    {
        $this->dbh = $dbh;
        $this->userId = $userId;
        $this->setUserRights($userRights);
    }

    /**
     * Show teacher banners if the user is a teacher.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see displayOnlyOncePerBanner
     */
    public function showTeacherBannersForTeachersOnly(): bool
    {
        if (15 >= $this->userRights) {
            return false;
        }
        return $this->showBanners(self::TEACHER_ROLE);
    }

    /**
     * Show student banners if the user is a student.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see displayOnlyOncePerBanner
     */
    public function showStudentBannersForStudentsOnly(): bool
    {
        if (15 < $this->userRights) {
            return false;
        }
        return $this->showBanners(self::STUDENT_ROLE);
    }

    /**
     * Show banners. User rights are not checked.
     *
     * @param int $role 1 for teacher, 2 for student.
     * @return bool True if a banner was displayed. False if not.
     */
    public function showBanners(int $role): bool
    {
        $bannerDbHelper = $this->getNewBannerInstance();
        $banners = $bannerDbHelper->findEnabledAndAvailable();

        $bannerDismissal = $this->getNewBannerDismissalInstance();
        $dismissedBannerIds = $bannerDismissal->getDismissedBannerIds($this->userId);

        $allBannerIds = [];
        foreach ($banners as $banner) {
            $allBannerIds[] = $banner->getId();
        }

        $bannerDisplayed = false;
        foreach ($banners as $banner) {
            // Teacher banner is disabled.
            if (self::TEACHER_ROLE == $role && !$banner->getDisplayTeacher()) {
                continue;
            }
            // Student banner is disabled.
            if (self::STUDENT_ROLE == $role && !$banner->getDisplayStudent()) {
                continue;
            }
            // User has dismissed this banner.
            if (in_array($banner->getId(), $dismissedBannerIds)) {
                continue;
            }
            // User has already seen this banner. (displayed multiple times on same page)
            if ($this->displayOnlyOncePerBanner &&
                in_array($banner->getId(), $this->displayedBannerIds)) {
                continue;
            }

            // Make the banner data available to the view.
            $bannerData = $this->getViewDataByRole($banner, $role);
            $bannerId = $bannerData['id'];
            $bannerTitle = $bannerData['title'];
            $bannerContent = $bannerData['content'];
            $bannerDismissible = $bannerData['dismissible'];

            include(__DIR__ . '/../views/banner/show_teacher.php');
            $this->displayedBannerIds[] = $banner->getId();
            $bannerDisplayed = true;
        }

        return $bannerDisplayed;
    }

    /**
     * Preview a banner.
     *
     * @param int $bannerId The Banner ID to display.
     * @param int $role 1 for teacher, 2 for student.
     * @return bool True if a banner was displayed.
     */
    public function previewBanner(int $bannerId, int $role): bool
    {
        $banner = $this->getNewBannerInstance();
        $found = $banner->find($bannerId);

        if (!$found) {
            return false;
        }

        // Make the banner data available to the view.
        $bannerData = $this->getViewDataByRole($banner, $role);
        $bannerId = $bannerData['id'];
        $bannerTitle = $bannerData['title'];
        $bannerContent = $bannerData['content'];
        $bannerDismissible = $bannerData['dismissible'];

        if (self::TEACHER_ROLE == $role) {
            include(__DIR__ . '/../views/banner/show_teacher.php');
            return true;
        }
        if (self::STUDENT_ROLE == $role) {
            include(__DIR__ . '/../views/banner/show_student.php');
            return true;
        }
        return false;
    }

    /**
     * Get a Banner's content by user role, for a view.
     *
     * @param Banner $banner A Banner instance.
     * @param int $role 1 for teacher, 2 for student.
     * @return array An array of variables for a view.
     */
    protected function getViewDataByRole(Banner $banner, int $role): array
    {
        $data = [];
        if (self::TEACHER_ROLE == $role) {
            $data['title'] = $banner->getTeacherTitle();
            $data['content'] = $banner->getTeacherContent();
        }
        if (self::STUDENT_ROLE == $role) {
            $data['title'] = $banner->getStudentTitle();
            $data['content'] = $banner->getStudentContent();
        }
        $data['id'] = $banner->getId();
        $data['dismissible'] = $banner->getDismissible();

        return $data;
    }

    /*
     * Getters, setters
     */

    /**
     * Get a new Banner instance.
     *
     * This method allows for easier unit testing.
     *
     * @return Banner
     */
    protected function getNewBannerInstance(): Banner
    {
        if (!is_null($this->bannerForTesting)) {
            return $this->bannerForTesting;
        }

        return new Banner($this->dbh);
    }

    /**
     * Get a new BannerDismissal instance.
     *
     * This method allows for easier unit testing.
     *
     * @return BannerDismissal
     */
    public function getNewBannerDismissalInstance(): BannerDismissal
    {
        if (!is_null($this->bannerDismissalForTesting)) {
            return $this->bannerDismissalForTesting;
        }

        return new BannerDismissal($this->dbh);
    }

    /**
     * Set the Banner model instance. Used during testing.
     *
     * @param Banner $bannerForTesting
     * @return OhmBannerService
     */
    public function setBannerForTesting(Banner $bannerForTesting): OhmBannerService
    {
        $this->bannerForTesting = $bannerForTesting;
        return $this;
    }

    /**
     * Set the BannerDismissal model instance. Used during testing.
     *
     * @param BannerDismissal $bannerDismissalForTesting
     * @return OhmBannerService
     */
    public function setBannerDismissalForTesting(BannerDismissal $bannerDismissalForTesting): OhmBannerService
    {
        $this->bannerDismissalForTesting = $bannerDismissalForTesting;
        return $this;
    }

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
