<?php

use Ramsey\Uuid\Uuid;

/**
 * Save a new external ID for a question.
 *
 * @param PDO $dbh
 * @param int $id The question ID from imas_questionset.
 * @return array An associative array containing the question's external ID.
 *               An empty array is returned on failure.
 *                   Example:
 *                       [
 *                           'external_id' => 'c103e9c9-5cff-4a3d-8285-eb24041d19e1',
 *                       ]
 */
function onCreateQuestionSet(PDO $dbh, int $id): array
{
    $question = _getQuestionById($dbh, $id);
    if (!$question) {
        return [];
    }

    $externalId = Uuid::uuid4();
    $success = _saveExternalId($dbh, $id, $externalId);

    return $success ? ['external_id' => $externalId] : [];
}

/**
 * Save a new external ID for a question if it doesn't already have one.
 *
 * @param PDO $dbh
 * @param int $id The question ID from imas_questionset.
 * @return array An associative array containing the question's external ID.
 *               An empty array is returned on failure.
 *                   Example:
 *                       [
 *                           'external_id' => 'c103e9c9-5cff-4a3d-8285-eb24041d19e1',
 *                       ]
 */
function onUpdateQuestionSet(PDO $dbh, int $id): array
{
    $question = _getQuestionById($dbh, $id);
    if (!$question) {
        return [];
    }

    if (!empty($question['external_id'])) {
        return ['external_id' => $question['external_id']];
    }

    $externalId = Uuid::uuid4();
    $success = _saveExternalId($dbh, $id, $externalId);

    return $success ? ['external_id' => $externalId] : [];
}

/**
 * Get a question by ID.
 *
 * @param PDO $dbh
 * @param int $questionId The question's ID from imas_questionset.
 * @return array|null An associative array representing the question's DB row.
 *                    Null if not found.
 */
function _getQuestionById(PDO $dbh, int $questionId): ?array
{
    $query = "SELECT id, external_id FROM imas_questionset WHERE id = :id";
    $stm = $dbh->prepare($query);
    $stm->execute(['id' => $questionId]);
    $question = $stm->fetch(PDO::FETCH_ASSOC);

    if (!$question) return null;

    return $question;
}

/**
 * Save a question's external ID.
 *
 * @param PDO $dbh
 * @param int $questionId The question's ID from imas_questionset.
 * @param string $externalId The question's external ID.
 * @return bool True on successful save. False on failure to save.
 */
function _saveExternalId(PDO $dbh, int $questionId, string $externalId): bool
{
    $query = "UPDATE imas_questionset SET external_id = :externalId WHERE id = :id";
    $stm = $dbh->prepare($query);
    $success = $stm->execute([
        ':id' => $questionId,
        ':externalId' => $externalId,
    ]);

    return $success;
}
