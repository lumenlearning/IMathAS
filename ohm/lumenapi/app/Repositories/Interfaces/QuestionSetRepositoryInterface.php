<?php
namespace App\Repositories\Interfaces;

interface QuestionSetRepositoryInterface
{
    public function getById($questionSetId);

    public function getFieldsById($questionSetId);

    public function getAllByQuestionId($questionIds, $placeholders);
}