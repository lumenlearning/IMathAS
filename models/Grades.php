<?php

/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 5/6/15
 * Time: 7:13 PM
 */

namespace app\models;
use app\components\AppConstant;
use Yii;

use app\components\AppUtility;
use app\models\_base\BaseImasGrades;
use yii\db\Query;

class Grades extends BaseImasGrades
{
    public function createGradesByUserId($grade)
    {
            $this->gradetypeid = $grade['gradetypeid'];
            $this->userid = $grade['userid'];
            $this->score = $grade['score'];
            $this->feedback = $grade['feedback'];
            $this->gradetype = $grade['gradetype'];
            $this->save();

    }

    public static function GetOtherGrades($gradetypeselects, $limuser){
            $sel = implode(' OR ',$gradetypeselects);
            $query = "SELECT * FROM imas_grades WHERE ($sel)";
            if ($limuser>0) { $query .= " AND userid='$limuser' ";}
        $data = \Yii::$app->db->createCommand($query)->queryAll();
        return $data;
    }
    public static function outcomeGrades($sel,$limuser)
    {
        $query = new Query();
        $query->select(['*'])
            ->from('imas_grades')
            ->where([$sel]);
        if ($limuser > 0)
        {
            $query->andWhere(["userid" => $limuser]);
        }
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public static function deleteByGradeTypeId($linkId){
        $externalTool = 'exttool';
        $linkData = Grades::findAll(['gradetypeid'=> $linkId,'gradetype' => $externalTool]);
        if($linkData){
            foreach($linkData as $singleData){
                $singleData->delete();
            }
        }
    }

    public static function deleteGradesUsingType($gradeType, $tools, $toUnEnroll)
    {
        $query = Grades::find()->where(['gradetype' => $gradeType])->andWhere(['IN', 'gradetypeid', $tools])->andWhere(['IN', 'userid', $toUnEnroll])->all();
        if ($query) {
            foreach ($query as $grades) {
                $grades->delete();
            }
        }
    }
    public static function getByGradeTypeId($gbItemsId){
        return Grades::find('userid','score')->where(['gradetypeid' => $gbItemsId])->andWhere(['gradetype' => 'offline'])->all();
    }
    public function addGradeToStudent($cuserid,$gbItemsId,$feedback,$score){

                $this->gradetype = 'offline';
                $this->gradetypeid = $gbItemsId;
                $this->userid = $cuserid;
                $this->score = $score;
                $this->feedback = $feedback;
                $this->save();

    }
    public static function updateGradeToStudent($score,$feedback,$userid,$gbItemsId)
    {
        $grade = Grades::find()->where(['userid' => $userid])->andWhere(['gradetypeid'=> $gbItemsId])->andWhere(['gradetype' => 'offline'])->one();
        if($grade){
            $grade->score = $score;
            $grade->feedback = $feedback;
            $grade->save();
        }
    }
    public static function deleteByGradeTypeIdAndGradeType($gradeId,$gradeType){
        $grades = Grades::find()->where(['gradetype' => $gradeType])->andWhere(['gradetypeid' => $gradeId])->all();
        if($grades){
            foreach($grades as $grade){
                $grade->delete();
            }
        }

    }
    public static function getByGradeTypeIdAndUserId($gbitemId,$grades)
    {
        $query = new Query();
        $query	->select(['userid','score','feedback'])
            ->from('imas_grades')
            ->where(['gradetype' => 'offline'])
            ->andWhere(['gradetypeid' => $gbitemId]);
        if($grades != 'all'){
            $query->andWhere(['userid' => $grades]);
        }
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }
    public static function updateScoreTostudnt($score,$feedback,$studentId,$gbitem){
        $grade = Grades::find()->where(['userid' => $studentId])->andWhere(['gradetype' => 'offline'])->andWhere(['gradetypeid' => $gbitem])->one();
        $grade->score = $score;
        $grade->feedback = $feedback;
        $grade->save();
    }
    public static function getUserId($gbItem,$kl)
    {
       return Grades::find()->where(['gradetype' => 'offline'])->andWhere(['gradetypeid' => $gbItem])->andWhere(['IN','userid',$kl])->all();
    }
    public static function deleteByUserId($userId)
    {
        $grades = Grades::find()->where(['userid' => $userId])->all();
        foreach($grades as $grade)
        {
            $grade->delete();
        }
    }

    public static function deleteById($id)
    {
        $query = "DELETE FROM imas_grades WHERE gradetypeid={$id} AND gradetype='exttool'";
        Yii::$app->db->createCommand($query)->execute();
    }
    public static function deleteByGradeId($id)
    {
        $query = "DELETE FROM imas_grades WHERE gradetype='offline' AND gradetypeid={$id}";
        Yii::$app->db->createCommand($query)->execute();
    }
}

