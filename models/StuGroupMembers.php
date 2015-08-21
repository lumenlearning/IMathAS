<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 5/8/15
 * Time: 8:34 PM
 */

namespace app\models;

use Yii;
use yii\db\Exception;
use app\components\AppUtility;
use app\models\_base\BaseImasStugroupmembers;
use yii\db\Query;

class StuGroupMembers extends BaseImasStugroupmembers{

    public static function deleteMemberFromCourse($toUnEnroll, $stuGroups)
    {
        $query = StuGroupMembers::find()->where(['IN', 'stugroupid', $stuGroups])->andWhere(['IN', 'userid', $toUnEnroll])->all();
        if($query){
            foreach($query as $object){
                $object->delete();
            }
        }
    }

    public static function findByStuGroupId($groupId)
    {
        $query = new Query();
        $query ->select('userid')
                ->from('imas_stugroupmembers')
                ->where(['stugroupid' => $groupId]);
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public function insertStuGrpMemberData($userId,$newStuGrpId)
    {
        $this->userid = $userId;
        $this->stugroupid = $newStuGrpId;
        $this->save();
    }

    public static function deleteStuGroupMembers($grpId)
    {
        $query = StuGroupMembers::find()->where(['stugroupid' => $grpId])->all();
        if($query)
        {
            foreach($query as $data)
            {
                $data->delete();
            }
        }
    }

    public static function manageGrpSet($grpIds)
    {
        $query = "SELECT stugroupid,userid FROM imas_stugroupmembers WHERE stugroupid IN ($grpIds)";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;

    }

    public static function alreadyStuAdded($grpSetId,$stuList)
    {
        $query = "SELECT i_sgm.userid FROM imas_stugroupmembers as i_sgm JOIN imas_stugroups as i_sg ON i_sgm.stugroupid=i_sg.id ";
        $query .= "WHERE i_sg.groupsetid='$grpSetId' AND i_sgm.userid IN ($stuList)";

        return Yii::$app->db->createCommand($query)->queryAll();
    }

    public static function removeGrpMember($uid,$grpId)
    {
        $query = StuGroupMembers::find()->where(['stugroupid' => $grpId])->andWhere(['userid' => $uid])->all();
        if($query)
        {
            foreach($query as $data)
            {
                $data->delete();
            }
        }
    }
}
