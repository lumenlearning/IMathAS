<?php

namespace OHM\Eula;

use OHM\Exceptions\DatabaseReadException;
use OHM\Exceptions\DatabaseWriteException;
use PDO;
use Ramsey\Uuid\Uuid;
use Sanitize;

/**
 * Class Eula Manages EULA user acceptance and displays the EULA pages.
 * @package OHM\Eula
 */
class EulaService
{
    // TODO: This should not live in code!
    const EULA_LATEST_VERSION = 1;

    const EULA_EXCLUDE_PAGES = [
        '/ohm/eula',
        '/ohm/eula/',
        '/ohm/eula/index.php',
        '/actions.php', // This allows logging out.
    ];

    private $dbh;

    /**
     * Constructor.
     *
     * @param PDO $dbh A database connection.
     */
    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Does a user need to accept the latest EULA version.
     *
     * @param int $userId The user's ID. (from imas_users)
     * @return bool True if acceptance is required. False if not.
     * @throws DatabaseReadException
     */
    public function isAcceptanceRequired(int $userId): bool
    {
        // Check URL exclusions.
        if ($this->isCurrentPageExcludedFromEula()) {
            return false;
        }

        // Check if user has accepted latest EULA version.
        if (self::EULA_LATEST_VERSION <= $this->getUserAcceptanceVersion($userId)) {
            return false;
        }

        return true;
    }

    /**
     * Get the last EULA version a user has accepted.
     *
     * @param int $userId The user's ID. (from imas_users)
     * @return int The last EULA version accepted. Zero if none accepted.
     * @throws DatabaseReadException
     */
    public function getUserAcceptanceVersion(int $userId): int
    {
        $stm = $this->dbh->prepare("SELECT eula_version_accepted FROM imas_users WHERE id = :userId");
        $stm->execute([':userId' => $userId]);

        if (1 > $stm->rowCount()) {
            $uuid = Uuid::uuid4()->toString();
            $dbErrors = implode(', ', $stm->errorInfo());
            $message = 'Unable to get EULA acceptance status for User ID: ' . $userId;
            error_log(sprintf('%s - Error ID: %s - %s', $message, $uuid, $dbErrors));
            throw new DatabaseReadException($message);
        }

        $results = $stm->fetch(PDO::FETCH_NUM);

        return $results[0];
    }

    /**
     * Record a user's acceptance of the current EULA.
     *
     * @param int $userId The user's ID. (from imas_users)
     * @return bool True on success. Exception on error.
     * @throws DatabaseWriteException
     */
    public function updateUserAcceptanceToLatest(int $userId): bool
    {
        $stm = $this->dbh->prepare("UPDATE imas_users
            SET eula_version_accepted = :eulaVersion,
                eula_accepted_at = NOW()
            WHERE id = :userId");
        $result = $stm->execute([
            ':userId' => $userId,
            ':eulaVersion' => self::EULA_LATEST_VERSION
        ]);

        if (false == $result) {
            $uuid = Uuid::uuid4()->toString();
            $dbErrors = implode(', ', $stm->errorInfo());
            $message = 'Unable to update EULA acceptance status for User ID: ' . $userId;
            error_log(sprintf('%s - Error ID: %s - %s', $message, $uuid, $dbErrors));
            throw new DatabaseWriteException($message);
        }

        return true;
    }

    /**
     * Display the EULA page. (with Accept / Decline buttons)
     */
    public function redirectToEulaPage(): void
    {
        $destUrl = Sanitize::encodeUrlParam($_SERVER['REQUEST_URI']);
        $randomQueryString = Sanitize::randomQueryStringParam();
        ob_clean();
        header(sprintf('Location: %s/ohm/eula/index.php?r=%s&dest=%s',
            $GLOBALS['basesiteurl'], $randomQueryString, $destUrl));
    }

    /**
     * Determine if the current page is excluded from EULA enforcement.
     *
     * @return bool False if the EULA should be enforced. True if it should be.
     * @see EULA_EXCLUDE_PAGES constant for list of exclusions.
     */
    public function isCurrentPageExcludedFromEula(): bool
    {
        $requestedUrlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (in_array($requestedUrlPath, self::EULA_EXCLUDE_PAGES)) {
            return true;
        }
        return false;
    }
}
