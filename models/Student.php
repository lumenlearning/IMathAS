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
    public static function getByUserIdentity($uid,$courseid)
    {

        return static::findAll(['userid' => $uid,'courseid' => $courseid]);
    }
    public function createNewStudent($userId,$cid,$param){

        $this->userid = $userId;
        $this->courseid = $cid;
        $this->section = empty($param['section']) ? null : $param['section'];
        $this->code = empty($param['code']) ? null : $param['code'];;
        $this->save();
    }
    public static function findByCid($cId){
        return static::findAll(['courseid'=>$cId]);

    }

    public function insertNewStudent($studentId,$courseId,$section)
    {
        $this->userid = $studentId;
        $this->courseid = $courseId;
        $this->section = empty($section) ? null : $section;
        $this->save();
    }
    public static function updateSectionAndCodeValue($section, $userid, $code, $cid)
    {

        $student = Student::findOne(['userid' => $userid, 'courseid' => $cid]);
        $student->section = $section;
        $student->code = $code;
        $student->save();


    }

    public static function updateLatepasses($latepass,$userid,$cid)
    {

        $student = Student::findOne(['userid' => $userid, 'courseid' => $cid]);
        $student->latepass = $latepass;
        $student->save();
    }

    public static function findByCourseId($cId,$sortBy, $order){
        return static::find()->where(['courseid'=>$cId])->groupBy('section')->orderBy([$sortBy => $order])->all();
    }
    public static function updateLocked($userid,$courseid)
    {

        $student = Student::findOne(['userid' => $userid,'courseid' => $courseid]);
        $student->locked = strtotime(date('F d, o g:i a'));;
        $student->save();
    }
    public static function deleteStudent($userid,$courseid)
    {
        $student = Student::findOne(['userid' => $userid,'courseid' => $courseid]);
        $student->delete();
    }

} 