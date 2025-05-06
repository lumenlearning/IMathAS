<?php

namespace IMathAS\assess2\questions\answerboxes;

/**
 * Interface AnswerBoxInterface
 *
 * This interface contains AnswerBox methods that should exist only in OHM.
 */
interface AnswerBoxOhmExtensions
{
    /**
     * Get an associative array of question option variable names that may appear in this
     * question's code and their values.
     *
     * Some variables, such as "randkeys", do not appear in question code but will also be
     * returned.
     *
     * The return value of this method is intended to be used by OHM's question API (using
     * the Laravel/Lumen framework) to return all components of a question.
     *
     * @return array An associative array of question option variable names and their values.
     */
    public function getQuestionOptionVariables(): array;
}
