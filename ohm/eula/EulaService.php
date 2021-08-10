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
        '/diag/index.php', // OHM-1061: Prevent endless redirects for /diag/index.php workflow.
        '/actions.php', // This allows logging out.
        '/forms.php', // Allow users to reset their passwords. See OHM-1054.
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
     * Require the user to accept the latest EULA.
     *
     * Call this method anywhere EULA enforcement is required.
     *
     * - If the user has not accepted the latest EULA, they will be
     *   redirected to the EULA page instead of their originally requested
     *   location.
     * - They will be redirected to their original destination after they
     *   accept the EULA.
     */
    public function enforceOhmEula(): void
    {
        if (!isset($GLOBALS['userid']) || empty($GLOBALS['userid'])) {
            // OHM-1061: Prevent endless redirects for /diag/index.php workflow.
            if (isset($_SESSION['eula_acceptance_required']) && !$_SESSION['eula_acceptance_required']) {
                unset($_SESSION['eula_acceptance_required']);
            }

            // Without a User ID, we can't do anything.
            return;
        }

        $acceptanceRequired = false;
        try {
            $acceptanceRequired = $this->isAcceptanceRequired($GLOBALS['userid']);
        } catch (DatabaseReadException $e) {
            error_log($e->getMessage());
        }

        // OHM-1061: Prevent endless redirects for /diag/index.php workflow.
        if ($acceptanceRequired) {
            $_SESSION['eula_acceptance_required'] = true;
        }

        if ($acceptanceRequired) {
            $this->redirectToEulaPage();
            exit;
        }
    }

    /**
     * Does a user need to accept the latest EULA version.
     *
     * This checks:
     * - EULA feature flag.
     * - User's last accepted EULA version.
     * - Requested URL against a list of excluded URLs.
     *
     * @param int $userId The user's ID. (from imas_users)
     * @return bool True if acceptance is required. False if not.
     * @throws DatabaseReadException
     */
    public function isAcceptanceRequired(int $userId): bool
    {
        // Is the EULA feature enabled?
        if ('true' != getenv('EULA_ENABLED')) {
            return false;
        }

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

        // OHM-1061: Endless redirects for /diag/index.php workflow.
        $_SESSION['eula_acceptance_required'] = false;

        return true;
    }

    /**
     * Display the EULA page. (with Accept / Decline buttons)
     */
    public function redirectToEulaPage(): void
    {
        $destUrl = Sanitize::encodeUrlParam($_SERVER['REQUEST_URI']);
        $randomQueryString = Sanitize::randomQueryStringParam();
        $lmsParam = $this->isLmsUser() ? '&lms=true' : '';
        ob_clean();
        header(sprintf('Location: %s/ohm/eula/index.php?r=%s&dest=%s%s',
            $GLOBALS['basesiteurl'], $randomQueryString, $destUrl, $lmsParam));
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

    /**
     * Determine if the user is logged in via LMS.
     *
     * @return bool True if logged in via LMS. False if not.
     */
    public function isLmsUser(): bool
    {
        if (!isset($GLOBALS['isLmsUser']) || true !== $GLOBALS['isLmsUser']) {
            return false;
        }

        return true;
    }
}
