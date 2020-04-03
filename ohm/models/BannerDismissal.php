<?php

namespace OHM\Models;

use DateTime;
use OHM\Exceptions\DatabaseWriteException;
use PDO;

/**
 * Class BannerDismissal Represents a single row in ohm_notice_dismissals.
 * @package OHM\Models
 */
class BannerDismissal
{
    private $id;
    private $userId;
    private $bannerId;
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
     * Find a row by user ID and banner ID.
     *
     * @param int $userId The user's ID from imas_users.
     * @param int $bannerId The banner UUID.
     * @return bool True if found. False if not.
     */
    public function findByUserIdAndBannerId(int $userId, int $bannerId): bool
    {
        $query = "SELECT * FROM ohm_notice_dismissals
                    WHERE userid = :userid
                        AND noticeid = :bannerid
                    LIMIT 1";
        $stm = $this->dbh->prepare($query);
        $stm->execute([':userid' => $userId, ':bannerid' => $bannerId]);

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
            ':bannerid' => $this->bannerId,
            ':dismissed_at' => is_null($this->dismissedAt) ? null :
                $this->dismissedAt->format('Y-m-d H:i:s'),
        ];

        if (empty($this->id)) {
            $action = 'create new';
            $stm = $this->dbh->prepare("INSERT INTO ohm_notice_dismissals
                    (userid, noticeid, dismissed_at) VALUES
                    (:userid, :bannerid, :dismissed_at)");
        } else {
            $action = 'update ID ' . $this->id;
            $stm = $this->dbh->prepare("UPDATE ohm_notice_dismissals SET
                    userid = :userid, noticeid = :bannerid, dismissed_at = :dismissed_at
                WHERE id = :id");
            $params[':id'] = $this->id;
        }

        $result = $stm->execute($params);
        if (false == $result) {
            $dbErrors = implode(' ', $stm->errorInfo());
            throw new DatabaseWriteException(sprintf('Failed to %s BannerDismissal . %s',
                $action, $dbErrors));
        }

        if ('create new' == $action) {
            $this->id = $this->dbh->lastInsertId();
        }

        return true;
    }

    /**
     * Dismiss the banner immediately with the current time and date.
     *
     * Note: This convenience function will also SAVE to the database.
     *
     * @return bool True on success. False on failure.
     * @throws DatabaseWriteException Thrown if unable to write to the database.
     * @throws \Exception Thrown if a user ID or banner ID are not set.
     */
    public function dismissBannerNow(): bool
    {
        if (empty($this->userId) || empty($this->bannerId)) {
            throw new \Exception("Missing user ID or banner ID."
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
        $this->bannerId = $rowData['noticeid'];
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
     * @return BannerDismissal
     */
    public function setUserId(int $userId): BannerDismissal
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getBannerId(): ?string
    {
        return $this->bannerId;
    }

    /**
     * @param int $bannerId
     * @return BannerDismissal
     */
    public function setBannerId(int $bannerId): BannerDismissal
    {
        $this->bannerId = $bannerId;
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
     * @return BannerDismissal
     */
    public function setDismissedAt(DateTime $dismissedAt): BannerDismissal
    {
        $this->dismissedAt = $dismissedAt;
        return $this;
    }
}
