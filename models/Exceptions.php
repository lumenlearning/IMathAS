<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 15/5/15
 * Time: 5:58 PM
 */

namespace app\models;


use app\models\_base\BaseImasExceptions;

class Exceptions extends BaseImasExceptions
{
    public static function getByAssessmentId($id)
    {
        return static::findAll(['assessmentid' => $id]);
    }

    public function create($param)
    {
        $this->attributes = $param;
        $this->save();
    }
} 