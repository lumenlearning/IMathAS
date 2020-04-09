<?php

namespace OHM\Models;

use Cassandra\Date;
use DateTime;
use OHM\Exceptions\DatabaseWriteException;
use PDO;

/**
 * Class Banner Represents a single row in ohm_notices.
 * @package OHM\Models
 */
class Banner
{
    private $id; // int
    private $enabled; // bool
    private $dismissible; // bool
    private $displayStudent; // bool
    private $displayTeacher; // bool
    private $description; // string
    private $studentTitle; // string
    private $studentContent; // string
    private $teacherTitle; // string
    private $teacherContent; // string

    /* @var DateTime */
    private $startAt;
    /* @var DateTime */
    private $endAt;
    /* @var DateTime */
    private $createdAt;

    /* @var PDO */
    private $dbh;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Find a row by ID.
     *
     * @param int $id The row ID.
     * @return bool True if found. False if not.
     */
    public function find(int $id): bool
    {
        $stm = $this->dbh->prepare("SELECT * FROM ohm_notices WHERE id = :id");
        $stm->execute([':id' => $id]);

        if (1 > $stm->rowCount()) {
            return false;
        }

        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assignFields($row);

        return true;
    }

    /**
     * Delete this banner.
     *
     * @return bool True on success. False on failure.
     * @throws DatabaseWriteException Thrown if unable to write to the database.
     */
    public function delete(): bool
    {
        if (empty($this->id)) {
            return false;
        }

        $stm = $this->dbh->prepare("DELETE FROM ohm_notices WHERE id = :id");
        $result = $stm->execute([':id' => $this->id]);

        if (false == $result) {
            $dbErrors = implode(' ', $stm->errorInfo());
            throw new DatabaseWriteException(
                sprintf('Failed to delete Banner. %s', $dbErrors));
        }

        return true;
    }

    /**
     * Save this banner.
     *
     * @return bool True on success. False on failure.
     * @throws DatabaseWriteException Thrown if unable to write to the database.
     */
    public function save(): bool
    {
        $params = [
            ':is_enabled' => $this->enabled,
            ':is_dismissible' => $this->dismissible,
            ':display_student' => $this->displayStudent,
            ':display_teacher' => $this->displayTeacher,
            ':description' => $this->description,
            ':student_title' => $this->studentTitle,
            ':student_content' => $this->studentContent,
            ':teacher_title' => $this->teacherTitle,
            ':teacher_content' => $this->teacherContent,
            ':start_at' => is_null($this->startAt) ? null :
                $this->startAt->format('Y-m-d H:i:s'),
            ':end_at' => is_null($this->endAt) ? null :
                $this->endAt->format('Y-m-d H:i:s'),
        ];

        // lastInsertId() does not work with "ON DUPLICATE KEY UPDATE" :(
        if (empty($this->id)) {
            $action = 'create new';
            $stm = $this->dbh->prepare("INSERT INTO ohm_notices
                    (is_enabled, is_dismissible, display_student, display_teacher,
                     description, student_title, student_content, teacher_title,
                     teacher_content, start_at, end_at) VALUES
                    (:is_enabled, :is_dismissible, :display_student, :display_teacher,
                     :description, :student_title, :student_content, :teacher_title,
                     :teacher_content, :start_at, :end_at)");
        } else {
            $action = 'update ID ' . $this->id;
            $stm = $this->dbh->prepare("UPDATE ohm_notices SET
                    is_enabled = :is_enabled, is_dismissible = :is_dismissible,
                       display_student = :display_student, display_teacher = :display_teacher,
                       description = :description, student_title = :student_title,
                       student_content = :student_content, teacher_title = :teacher_title,
                       teacher_content = :teacher_content, start_at = :start_at,
                       end_at = :end_at
                WHERE id = :id");
            $params[':id'] = $this->id;
        }

        $result = $stm->execute($params);
        if (false == $result) {
            $dbErrors = implode(' ', $stm->errorInfo());
            throw new DatabaseWriteException(
                sprintf('Failed to %s Banner. %s', $action, $dbErrors));
        }

        if ('create new' == $action) {
            $this->id = $this->dbh->lastInsertId();
        }

        return true;
    }

    /*
     * Getters, setters
     */

    /**
     * Assign class fields from a DB row from ohm_notice_dismissals.
     *
     * @param array $rowData An associative array containing a single DB row of data.
     */
    private function assignFields(array $rowData): void
    {
        $this->id = $rowData['id'];
        $this->enabled = $rowData['is_enabled'];
        $this->dismissible = $rowData['is_dismissible'];
        $this->displayStudent = $rowData['display_student'];
        $this->displayTeacher = $rowData['display_teacher'];
        $this->description = $rowData['description'];
        $this->studentTitle = $rowData['student_title'];
        $this->studentContent = $rowData['student_content'];
        $this->teacherTitle = $rowData['teacher_title'];
        $this->teacherContent = $rowData['teacher_content'];
        $this->startAt = empty($rowData['start_at']) ? null :
            DateTime::createFromFormat('Y-m-d H:i:s', $rowData['start_at']);
        $this->endAt = empty($rowData['end_at']) ? null :
            DateTime::createFromFormat('Y-m-d H:i:s', $rowData['end_at']);
        $this->createdAt = empty($rowData['created_at']) ? null :
            DateTime::createFromFormat('Y-m-d H:i:s', $rowData['created_at']);
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Determine if this banner is enabled for display to users.
     *
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Determine if this banner is enabled for display to users.
     *
     * @param bool $enabled
     * @return Banner
     */
    public function setEnabled(bool $enabled): Banner
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Determine if this banner is dismissible by users.
     *
     * @return bool
     */
    public function getDismissible(): bool
    {
        return $this->dismissible;
    }

    /**
     * Determine if this banner is dismissible by users.
     *
     * @param bool $dismissible
     * @return Banner
     */
    public function setDismissible(bool $dismissible): Banner
    {
        $this->dismissible = $dismissible;
        return $this;
    }

    /**
     * If this banner is enabled, determine if student banners will be displayed.
     *
     * @return bool
     */
    public function getDisplayStudent(): bool
    {
        return $this->displayStudent;
    }

    /**
     * If this banner is enabled, determine if student banners will be displayed.
     *
     * @param bool $displayStudent
     * @return Banner
     */
    public function setDisplayStudent(bool $displayStudent): Banner
    {
        $this->displayStudent = $displayStudent;
        return $this;
    }

    /**
     * If this banner is enabled, determine if teacher banners will be displayed.
     *
     * @return bool
     */
    public function getDisplayTeacher(): bool
    {
        return $this->displayTeacher;
    }

    /**
     * If this banner is enabled, determine if teacher banners will be displayed.
     *
     * @param bool $displayTeacher
     * @return Banner
     */
    public function setDisplayTeacher(bool $displayTeacher): Banner
    {
        $this->displayTeacher = $displayTeacher;
        return $this;
    }

    /**
     * The banner description, for admin use only when managing banners.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * The banner description, for admin use only when managing banners.
     *
     * @param string $description
     * @return Banner
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The banner title displayed to students.
     *
     * @return string|null
     */
    public function getStudentTitle(): ?string
    {
        return $this->studentTitle;
    }

    /**
     * The banner title displayed to students.
     *
     * @param string|null $studentTitle
     * @return Banner
     */
    public function setStudentTitle(?string $studentTitle): Banner
    {
        $this->studentTitle = $studentTitle;
        return $this;
    }

    /**
     * The banner content displayed to students.
     *
     * @return string|null
     */
    public function getStudentContent(): ?string
    {
        return $this->studentContent;
    }

    /**
     * The banner content displayed to students.
     *
     * @param string|null $studentContent
     * @return Banner
     */
    public function setStudentContent(?string $studentContent): Banner
    {
        $this->studentContent = $studentContent;
        return $this;
    }

    /**
     * The banner title displayed to teachers.
     *
     * @return string|null
     */
    public function getTeacherTitle(): ?string
    {
        return $this->teacherTitle;
    }

    /**
     * The banner title displayed to teachers.
     *
     * @param string|null $teacherTitle
     * @return Banner
     */
    public function setTeacherTitle(?string $teacherTitle): Banner
    {
        $this->teacherTitle = $teacherTitle;
        return $this;
    }

    /**
     * The banner content displayed to teachers.
     *
     * @return string|null
     */
    public function getTeacherContent(): ?string
    {
        return $this->teacherContent;
    }

    /**
     * The banner content displayed to teachers.
     *
     * @param string|null $teacherContent
     * @return Banner
     */
    public function setTeacherContent(?string $teacherContent): Banner
    {
        $this->teacherContent = $teacherContent;
        return $this;
    }

    /**
     * If enabled, when to start displaying this banner.
     * Null = Display immediately.
     *
     * @return DateTime|null
     */
    public function getStartAt(): ?DateTime
    {
        return $this->startAt;
    }

    /**
     * If enabled, when to start displaying this banner.
     * Null = Display immediately.
     *
     * @param DateTime|null $startAt
     * @return Banner
     */
    public function setStartAt(?DateTime $startAt): Banner
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * If enabled, when to stop displaying this banner.
     * Null = never stop.
     *
     * @return DateTime|null
     */
    public function getEndAt(): ?DateTime
    {
        return $this->endAt;
    }

    /**
     * If enabled, when to stop displaying this banner.
     * Null = never stop.
     *
     * @param DateTime|null $endAt
     * @return Banner
     */
    public function setEndAt(?DateTime $endAt): Banner
    {
        $this->endAt = $endAt;
        return $this;
    }

    /**
     * Banner creation timestamp.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Banner creation timestamp.
     *
     * @param DateTime $createdAt
     * @return Banner
     */
    public function setCreatedAt(DateTime $createdAt): Banner
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
