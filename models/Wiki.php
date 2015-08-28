<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 29/4/15
 * Time: 4:46 PM
 */

namespace app\models;


use app\components\AppConstant;
use app\components\AppUtility;
use app\models\_base\BaseImasWikis;

class Wiki extends BaseImasWikis
{
    public static function getByCourseId($courseId)
    {
        return static::findAll(['courseid' => $courseId]);
    }

    public static function getById($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function getAllData($wikiId)
    {
        $query = Wiki::find(['name','startdate','enddate','editbydate','avail'])->where(['id' => $wikiId])->all();
        return $query;
    }

    public function createItem($params)
    {
        $this->courseid = $params['courseid'];
        $this->name = $params['title'];
        $this->description = $params['description'];
        $this->avail = $params['avail'];
        $this->startdate = $params['startdate'];
        $this->enddate = $params['enddate'];
        $this->save();
        return $this->id;
    }

    public function updateChange($params)
    {
        $updateWiki = Wiki::findOne(['id' => $params['id']]);
        $endDate = AppUtility::parsedatetime($params['edate'],$params['etime']);
        $startDate = AppUtility::parsedatetime($params['sdate'],$params['stime']);
        $updateWiki->courseid = $params['cid'];
        $updateWiki->name = $params['name'];
        $updateWiki->description = $params['description'];
        $updateWiki->avail = $params['avail'];
        if($params['avail'] == AppConstant::NUMERIC_ONE)
        {
            if($params['available-after'] == 0){
                $startDate = 0;
            }
            if($params['available-until'] == AppConstant::ALWAYS_TIME){
                $endDate = AppConstant::ALWAYS_TIME;
            }
            $updateWiki->startdate = $startDate;
            $updateWiki->enddate = $endDate;
        }else
        {
            $updateWiki->startdate = AppConstant::NUMERIC_ZERO;
            $updateWiki->enddate = AppConstant::ALWAYS_TIME;
        }
        $updateWiki->save();
    }

    public static function deleteById($itemId){
        $wikiData = Wiki::findOne($itemId);
        if($wikiData){
            $wikiData->delete();
        }
    }
    public static function getAllDataWiki($wikiId)
    {
        $query =\Yii::$app->db->createCommand("SELECT name,startdate,enddate,editbydate,avail FROM imas_wikis WHERE id='$wikiId'")->queryOne();
        return $query;
    }

    public function addWiki($wiki){
        $this->courseid = isset($wiki['courseid']) ? $wiki['courseid'] : null;
        $this->name = isset($wiki['name']) ? $wiki['name'] : null;
        $this->description = isset($wiki['description']) ? $wiki['description'] : null;
        $this->startdate = isset($wiki['startdate']) ? $wiki['startdate'] : null;
        $this->enddate = isset($wiki['enddate']) ? $wiki['enddate'] : null;
        $this->editbydate = isset($wiki['editbydate']) ? $wiki['editbydate'] : null;
        $this->avail = isset($wiki['avail']) ? $wiki['avail'] : null;
        $this->settings = isset($wiki['settings']) ? $wiki['settings'] : null;
        $this->groupsetid = isset($wiki['groupsetid']) ? $wiki['groupsetid'] : null;
        $this->save();
        return $this->id;
    }

    public  static function setEditByDate($shift,$typeId)
    {
        $date = Wiki::find()->where(['id'=>$typeId])->andWhere(['>','editbydate','0'])->andWhere(['<','editbydate','2000000000'])->one();
        if($date)
        {
            $date->editbydate = $date['editbydate']+$shift;
            $date->save();
        }
    }

    public static function getByGroupSetId($deleteGrpSet)
    {
        return Wiki::find()->where(['groupsetid' => $deleteGrpSet])->all();
    }

    public static function updateWikiForGroups($deleteGrpSet)
    {
        $query = Wiki::find()->where(['groupsetid' => $deleteGrpSet])->all();
        if($query)
        {
            foreach($query as $singleData)
            {
                $singleData->groupsetid = AppConstant::NUMERIC_ZERO;
                $singleData->save();
            }
        }
    }

    public static function updateWikiById($startdate, $enddate, $avail, $id)
    {
        $wiki = Wiki::findOne(['id' => $id]);
        if($wiki){
            $wiki->startdate = $startdate;
            $wiki->enddate = $enddate;
            $wiki->avail = $avail;
            $wiki->save();
        }

    }

    public static function getWikiMassChanges($courseId)
    {
        $query = Wiki::find()->where(['courseid' => $courseId])->all();
        return $query;
    }
} 