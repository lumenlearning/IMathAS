<?php

namespace App\Services\Interfaces;

interface QuestionServiceInterface
{
    public function getQuestionsWithAnswers(array $questionSetIdsAndSeeds): array;
}
