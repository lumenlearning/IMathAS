<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 30/5/15
 * Time: 1:05 PM
 */

namespace app\models;


use app\components\AppConstant;
use app\components\AppUtility;
use app\models\_base\BaseImasForumThreads;
use app\models\_base\BaseImasInstrFiles;
use yii\db\Query;

class Thread extends BaseImasForumThreads
{


    public static function getById($id)
    {
        return Thread::findOne(['id' => $id]);
    }

    public static function getNextThreadId($currentId,$next=null,$prev=null,$forumId)
    {

        if($next == AppConstant::NUMERIC_TWO){
            $thread = Thread::find()->where(['>', 'id', $currentId])->andWhere(['forumid' => $forumId])->one();
        }elseif($prev == AppConstant::NUMERIC_ONE){
            $thread = Thread::find()->where(['<', 'id', $currentId])->andWhere(['forumid' => $forumId])->one();
        }
        $minThreadId = Thread::find()->where(['forumid' => $forumId])->min('id');
        $maxThreadId = Thread::find()->where(['forumid' => $forumId])->max('id');
        $prevNextValueArray = array();
        $prevNextValueArray = array(
        'threadId' =>$thread->id,
        'maxThread' =>$maxThreadId,
            'minThread' =>$minThreadId,
    );
        return $prevNextValueArray;
    }
    public static function deleteThreadById($id)
    {
        $thread = Thread::findOne(['id' => $id]);
        if($thread){
            $thread->delete();
        }

    }
    public static function moveAndUpdateThread($forumId,$threadId)
    {
        $ForumPost = Thread::findOne(['id' => $threadId]);
        $ForumPost->forumid = $forumId;
        $ForumPost->save();
    }

    public static function  findNewPostCnt($cid,$user)
    {

        $query = "SELECT imas_forum_threads.forumid, COUNT(imas_forum_threads.id) FROM imas_forum_threads ";
        $query .= "JOIN imas_forums ON imas_forum_threads.forumid=imas_forums.id AND imas_forums.courseid='$cid' ";
        $query .= "LEFT JOIN imas_forum_views as mfv ON mfv.threadid=imas_forum_threads.id AND mfv.userid='$user->id' ";
        $query .= "WHERE (imas_forum_threads.lastposttime>mfv.lastview OR (mfv.lastview IS NULL)) ";
        if ($user->rights == AppConstant::TEACHER_RIGHT) {
            $query .= "AND (imas_forum_threads.stugroupid=0 OR imas_forum_threads.stugroupid IN (SELECT stugroupid FROM imas_stugroupmembers WHERE userid='$userid')) ";
        }
        $query .= "GROUP BY imas_forum_threads.forumid";
        $data = \Yii::$app->db->createCommand($query)->queryAll();
//
//                $query = new Query();
//                $query ->select('imas_forum_threads.forumid , COUNT(imas_forum_threads.id)')
//                       ->from('imas_forum_threads')
//                        ->join( 'JOIN',
//                               'imas_forums','imas_forum_threads.forumid=imas_forums.id')
//                        ->andWhere(['imas_forums.courseid' =>$cid])
//                        ->leftJoin('imas_forum_views AS mfv','mfv.threadid=imas_forum_threads.id')
//                        ->andWhere(['mfv.userid' => $user->id])
//                         ->andWhere(['>','imas_forum_threads.lastposttime','mfv.lastview'])
//                          ->orWhere(['LIKE','mfv.lastview' ,'NULL']);
////                if ($user->rights == AppConstant::TEACHER_RIGHT){
////                    $query->andWhere(['LIKE','imas_forum_threads.stugroupid','0'])
////                            ->orWhere(['IN','imas_forum_threads.stugroupid',' SELECT stugroupid FROM imas_stugroupmembers'])
////                            ->andWhere(['userid' => $user->id]);
////                }
//                $query->groupBy(['imas_forum_threads.forumid']);
//
//                $command = $query->createCommand();
//                $data = $command->queryAll();
                return $data;





    }

} 