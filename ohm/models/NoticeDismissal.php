<?php

namespace OHM\Models;

use DateTime;
use OHM\Exceptions\DatabaseWriteException;
use PDO;

/**
 * Class NoticeDismissal Represents a single row in ohm_notice_dismissals.
 * @package OHM\Models
 */
class NoticeDismissal
{
    private $id;
    private $userId;
    private $noticeId;
    /* @var DateTime */
    private $dismissedAt;

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
        $stm = $this->dbh->prepare("SELECT * FROM ohm_notice_dismissals WHERE id = :id");
        $stm->execute([':id' => $id]);

        if (1 > $stm->rowCount()) {
            return false;
        }

        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assignFields($row);

        return true;
    }

    /**
     * Find a row by user ID and notice ID.
     *
     * @param int $userId The user's ID from imas_users.
     * @param int $noticeId The notice UUID.
     * @return bool True if found. False if not.
     */
    public function findByUserIdAndNoticeId(int $userId, int $noticeId): bool
    {
        $query = "SELECT * FROM ohm_notice_dismissals
                    WHERE userid = :userid
                        AND noticeid = :noticeid
                    LIMIT 1";
        $stm = $this->dbh->prepare($query);
        $stm->execute([':userid' => $userId, ':noticeid' => $noticeId]);

        if (1 > $stm->rowCount()) {
            return false;
        }

        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assignFields($row);

        return true;
    }

    /**
     * Persist a row.
     *
     * @return bool True on success. False on failure.
     * @throws DatabaseWriteException Thrown if unable to write to the database.
     */
    public function save(): bool
    {
        $params = [
            ':userid' => $this->userId,
            ':noticeid' => $this->noticeId,
            ':dismissed_at' => is_null($this->dismissedAt) ? null :
                $this->dismissedAt->format('Y-m-d H:i:s'),
        ];

        if (empty($this->id)) {
            $action = 'create new';
            $stm = $this->dbh->prepare("INSERT INTO ohm_notice_dismissals
                    (userid, noticeid, dismissed_at) VALUES
                    (:userid, :noticeid, :dismissed_at)");
        } else {
            $action = 'update ID ' . $this->id;
            $stm = $this->dbh->prepare("UPDATE ohm_notice_dismissals SET
                    userid = :userid, noticeid = :noticeid, dismissed_at = :dismissed_at
                WHERE id = :id");
            $params[':id'] = $this->id;
        }

        $result = $stm->execute($params);
        if (false == $result) {
            $dbErrors = implode(' ', $stm->errorInfo());
            throw new DatabaseWriteException(sprintf('Failed to %s NoticeDismissed . %s',
                $action, $dbErrors));
        }

        if ('create new' == $action) {
            $this->id = $this->dbh->lastInsertId();
        }

        return true;
    }

    /**
     * Dismissed the notice immediately with the current time and date.
     *
     * Note: This convenience function will also SAVE to the database.
     *
     * @return bool True on success. False on failure.
     * @throws DatabaseWriteException Thrown if unable to write to the database.
     * @throws \Exception Thrown if a user ID or notice ID are not set.
     */
    public function dismissNoticeNow(): bool
    {
        if (empty($this->userId) || empty($this->noticeId)) {
            throw new \Exception("Unable to dismiss a null notice for a null user."
             . " Please set a user ID and banner ID first!");
        }
        $this->setDismissedAt(DateTime::createFromFormat('U', time()));
        return $this->save();
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
        $this->userId = $rowData['userid'];
        $this->noticeId = $rowData['noticeid'];
        $this->dismissedAt = empty($rowData['dismissed_at']) ? null :
            DateTime::createFromFormat('Y-m-d H:i:s', $rowData['dismissed_at']);
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return NoticeDismissal
     */
    public function setUserId(int $userId): NoticeDismissal
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getNoticeId(): ?string
    {
        return $this->noticeId;
    }

    /**
     * @param int $noticeId
     * @return NoticeDismissal
     */
    public function setNoticeId(int $noticeId): NoticeDismissal
    {
        $this->noticeId = $noticeId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDismissedAt(): ?DateTime
    {
        return $this->dismissedAt;
    }

    /**
     * @param DateTime $dismissedAt
     * @return NoticeDismissal
     */
    public function setDismissedAt(DateTime $dismissedAt): NoticeDismissal
    {
        $this->dismissedAt = $dismissedAt;
        return $this;
    }
}
