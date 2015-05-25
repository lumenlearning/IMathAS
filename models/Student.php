<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 22/4/15
 * Time: 5:54 PM
 */

namespace app\models;


use app\components\AppUtility;
use app\models\_base\BaseImasStudents;

class Student extends BaseImasStudents {

    public function create($param)
    {
        $this->attributes = $param;
        $this->save();
    }

    public static function getByCourseId($courseId, $userId)
    {
        return static::findOne(['courseid' => $courseId, 'userid' => $userId]);
    }

    public static function getByUserId($id)
    {
        return static::findAll(['userid' => $id]);
    }

    public static function getByCId($cId)
    {
        return static::findOne(['courseid' => $cId]);
    }
    public static function getByUsername($username)
    {
        return static::findAll(['SID' => $username]);
    }
    public static function findByCid($cid)
    {
        return static::findAll(['courseid' => $cid]);
    }
} 