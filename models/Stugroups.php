<?php


namespace app\models;

use Yii;
use yii\db\Exception;
use app\components\AppUtility;
use app\models\_base\BaseImasStugroups;
use yii\db\Query;

class Stugroups extends BaseImasStugroups {
    public static function findByCourseId($courseId){
        $query = new Query();
        $query	->select(['imas_stugroups.id'])
            ->from('imas_stugroups')
            ->join(	'INNER JOIN',
                'imas_stugroupset',
                'imas_stugroups.groupsetid=imas_stugroupset.id'
            )
            ->where(['imas_stugroupset.courseid' => $courseId]);
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public static function findByGrpSetId($copyGrpSet)
    {
        return Stugroups::find()->where(['groupsetid' => $copyGrpSet])->one();
    }
    public function insertStuGrpData($stuGroupName,$NewGrpSetId)
    {
        $this->name = $stuGroupName;
        $this->groupsetid = $NewGrpSetId;
        $this->save();
        return $this->id;

    }

    public static  function findByGrpSetIdToDlt($deleteGrpSet)
    {
        $query = new Query();
        $query	->select(['id'])
            ->from('imas_stugroups')
            ->where(['groupsetid' => $deleteGrpSet]);
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public static function deleteGrp($grpId)
    {
        $query = Stugroups::find()->where(['id' => $grpId])->all();
        if($query)
        {
            foreach($query as $data)
            {
                $data->delete();
            }
        }

    }

    public static  function findByGrpSetIdForCopy($copyGrpSet)
    {
        $query = new Query();
        $query	->select(['id','name'])
            ->from('imas_stugroups')
            ->where(['groupsetid' => $copyGrpSet]);
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public static function findByGrpSetIdToManageSet($grpSetId)
    {
        $query = new Query();
        $query	->select(['id','name'])
            ->from('imas_stugroups')
            ->where(['groupsetid' => $grpSetId]);
        $query->orderBy('id');
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;

    }
    public static function getById($renameGrp)
    {
        return Stugroups::find()->where(['id' => $renameGrp])->one();

    }

    public static function renameGrpName($renameGrp,$grpName)
    {
        $query = Stugroups::find()->where(['id' => $renameGrp])->one();
        if($query)
        {
                $query ->name = $grpName;
                $query ->save();
        }
    }
    public function insertStuGrpName($grpSetId,$newGrpName)
    {
        $this->groupsetid = $grpSetId;
        $this->name = $newGrpName;
        $this->save();
        return $this->id;
    }
}