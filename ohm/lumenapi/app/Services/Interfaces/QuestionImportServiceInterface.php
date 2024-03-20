<?php

namespace App\Services\Interfaces;

interface QuestionImportServiceInterface
{
    public function createMultipleQuestions(array $mgaQuestionArray, int $ownerId): array;
}
