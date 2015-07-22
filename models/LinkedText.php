<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 25/6/15
 * Time: 3:37 PM
 */

namespace app\models;
use app\components\AppConstant;
use app\components\AppUtility;
use app\models\_base\BaseImasLinkedtext;
use yii\db\Query;

class LinkedText extends BaseImasLinkedtext
{
    public static function findExternalToolsInfo($courseId, $canviewall, $istutor, $isteacher, $catfilter, $now){
        $query = new Query();
        $query->select(['id', 'title', 'text', 'startdate', 'enddate', 'points', 'avail'])
            ->from('imas_linkedtext')
            ->where(['courseid'=>$courseId])
            ->andWhere(['>', 'points', 0])
            ->andWhere(['>', 'avail', 0]);
        if (!$canviewall) {
            $query->andWhere(['<','startdate', $now]);
        }
        /*if ($istutor) {
            $query->andWhere(['<','tutoredit', 2]);
        }
        if ($catfilter>-1) {
            $query->andWhere(['gbcategory' => $catfilter]);
        }*/
        $query->orderBy('enddate, startdate');
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }
    public function addLinkedText($params)
    {
        if ($params['id']){
            $endDate = $params['enddate'];
            $startDate = $params['startdate'];
        }else{
            $endDate = AppUtility::parsedatetime($params['edate'], $params['etime']);
            $startDate = AppUtility::parsedatetime($params['sdate'], $params['stime']);
        }
        $this->courseid = $params['courseid'];
        $this->title = $params['title'];
        $this->summary = $params['summary'];
        $this->text = $params['text'];
        $this->avail = $params['avail'];
        $this->oncal = $params['oncal'];
        $this->caltag = $params['caltag'];
        $this->target = $params['target'];
        if($params['outcomes']){
            $this->outcomes = $params['outcomes'];
        }
        $this->points = $params['points'];
        if ($params['avail'] == AppConstant::NUMERIC_ONE) {
            if ($params['available-after'] == AppConstant::NUMERIC_ZERO) {
                $startDate = AppConstant::NUMERIC_ZERO;
            }
            if ($params['available-until'] == AppConstant::ALWAYS_TIME) {
                $endDate = AppConstant::ALWAYS_TIME;
            }
            $this->startdate = $startDate;
            $this->enddate = $endDate;
        } else {
            $this->startdate = AppConstant::NUMERIC_ZERO;
            $this->enddate = AppConstant::ALWAYS_TIME;
        }
        $this->save();
        return $this->id;
    }

    public static function getById($id)
    {
        $linkData = LinkedText::find()->where(['id' => $id])->one();
        return $linkData;
    }

    public function updateLinkData($params)
    {
//        courseid,title,summary,text,startdate,enddate,avail,oncal,caltag,target,outcomes,points
        $updaateLink = LinkedText::findOne(['id' => $params['id']]);
        $endDate =   AppUtility::parsedatetime($params['edate'],$params['etime']);
        $startDate = AppUtility::parsedatetime($params['sdate'],$params['stime']);
        $updaateLink->courseid = $params['cid'];
        $updaateLink->title = $params['name'];
        $updaateLink->summary = $params['summary'];
        $updaateLink->text = $params['text'];
        $updaateLink->avail = $params['avail'];
        $updaateLink->oncal = $params['place-on-calendar'];
        $updaateLink->caltag = $params['tag'];
        $updaateLink->target = $params['open-page-in'];
//        $this->outcomes = $outcomes;
        $updaateLink->points= $params['points'];
        if($params['avail'] == AppConstant::NUMERIC_ONE)
        {
            if($params['available-after'] == 0){
                $startDate = 0;
            }
            if($params['available-until'] == AppConstant::ALWAYS_TIME){
                $endDate = AppConstant::ALWAYS_TIME;
            }
            $updaateLink->startdate = $startDate;
            $updaateLink->enddate = $endDate;
        }else
        {
            $updaateLink->startdate = AppConstant::NUMERIC_ZERO;
            $updaateLink->enddate = AppConstant::ALWAYS_TIME;
        }
        $updaateLink->save();
    }
}