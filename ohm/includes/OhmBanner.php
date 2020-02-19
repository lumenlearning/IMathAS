<?php

namespace OHM\Includes;

class OhmBanner
{
    // These define the ENV variables we'll look for.
    const ENV_TEACHER_FILENAME_KEY = 'BANNER_TEACHER_FILENAME';
    const ENV_STUDENT_FILENAME_KEY = 'BANNER_STUDENT_FILENAME';

    private $env;
    private $userRights;

    /**
     * OhmBanner constructor.
     *
     * @param int $userRights The user's rights from imas_users.
     */
    public function __construct(int $userRights)
    {
        $this->setEnv($_ENV);
        $this->setUserRights($userRights);
    }

    /**
     * Show the teacher banner if the user is a teacher.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see alwaysShowTeacher Class field override; always show teacher banner
     */
    public function showTeacherBannerForTeachersOnly(): bool
    {
        if (15 >= $this->userRights) {
            return false;
        }
        return $this->showTeacherBanner();
    }

    /**
     * Show the student banner if the user is a student.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see alwaysShowStudent Class field override; always show student banner
     */
    public function showStudentBannerForStudentsOnly(): bool
    {
        if (15 < $this->userRights) {
            return false;
        }
        return $this->showStudentBanner();
    }

    /**
     * Show the teacher banner. User rights are not checked.
     *
     * This happens only if:
     * - Environment variable contains a valid filename.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see showTeacherBannerForTeachersOnly
     */
    public function showTeacherBanner(): bool
    {
        if (!isset($this->env[self::ENV_TEACHER_FILENAME_KEY])) {
            return false;
        }

        $bannerFilename = trim($this->env[self::ENV_TEACHER_FILENAME_KEY]);
        $viewFullPath = __DIR__ . '/../../' . $bannerFilename;
        if (empty($bannerFilename) || !file_exists($viewFullPath)) {
            return false;
        }

        include($viewFullPath);

        return true;
    }

    /**
     * Show the student banner. User rights are not checked.
     *
     * This happens only if:
     * - Environment variable contains a valid filename.
     *
     * @return bool True if a banner was displayed. False if not.
     * @see showStudentBannerForStudentsOnly
     */
    public function showStudentBanner(): bool
    {
        if (!isset($this->env[self::ENV_STUDENT_FILENAME_KEY])) {
            return false;
        }

        $bannerFilename = trim($this->env[self::ENV_STUDENT_FILENAME_KEY]);
        $viewFullPath = __DIR__ . '/../../' . $bannerFilename;
        if (empty($bannerFilename) || !file_exists($viewFullPath)) {
            return false;
        }

        include($viewFullPath);

        return true;
    }

    /*
     * Getters, setters
     */

    /**
     * Set ALL environment variables for this object. Used during testing.
     *
     * @param array $env An associative array of environment variables.
     * @return OhmBanner
     */
    public function setEnv(array $env): OhmBanner
    {
        $this->env = $env;
        return $this;
    }

    /**
     * Set the user's rights. Used during testing.
     *
     * @param int $rights The user's rights from imas_users.
     * @return OhmBanner
     */
    public function setUserRights(int $rights): OhmBanner
    {
        $this->userRights = $rights;
        return $this;
    }
}
