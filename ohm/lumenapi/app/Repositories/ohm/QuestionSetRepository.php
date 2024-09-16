<?php
namespace App\Repositories\ohm;

use App\Repositories\Interfaces\QuestionSetRepositoryInterface;

class QuestionSetRepository extends BaseRepository implements QuestionSetRepositoryInterface
{
    public function getById($questionSetId)
    {
        $result = app('db')->select(
            'SELECT * FROM imas_questionset WHERE id = :questionSetId;', ['questionSetId' => $questionSetId]);

        return ($result) ? $this->toAssoc($result[0]) : null;
    }

    public function getByUniqueId($uniqueId)
    {
        $result = app('db')->select(
            'SELECT * FROM imas_questionset WHERE uniqueid = :uniqueId;', ['uniqueId' => $uniqueId]);

        return ($result) ? $this->toAssoc($result[0]) : null;
    }

    public function getFieldsById($questionSetId)
    {
        $result = app('db')->select(
            'SELECT qtype, control, qcontrol, qtext, answer, hasimg, extref, solution, solutionopts
                FROM imas_questionset WHERE id = :questionSetId;', ['questionSetId' => $questionSetId]);

        return $this->toAssoc($result[0]);
    }

    public function getAllByQuestionId(array $questionIds): array
    {
        if (empty($questionIds)) {
            return [];
        }

        $placeholdersAsArray = array_map(fn($ids): string => '?', $questionIds);
        $placeholders = implode(',', $placeholdersAsArray);

        $result = app('db')->select(
            "SELECT * FROM imas_questionset WHERE id IN ($placeholders);", $questionIds);

        return $this->toAssoc($result);
    }

    public function create(array $questionSetData): int
    {
        return app('db')->table('imas_questionset')->insertGetId($questionSetData);
    }
}
