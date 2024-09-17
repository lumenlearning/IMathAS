<?php
namespace App\Repositories\Interfaces;

interface QuestionSetRepositoryInterface
{
    public function getById($questionSetId);

    public function getByUniqueId($uniqueId);

    public function getFieldsById($questionSetId);

    public function getAllByQuestionId(array $questionIds): array;

    public function create(array $questionSetData): int;
}
