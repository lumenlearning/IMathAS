<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 22/4/15
 * Time: 6:30 PM
 */

namespace app\models;

use app\components\AppConstant;
use Yii;
use yii\db\Exception;
use app\components\AppUtility;
use app\models\_base\BaseImasMsgs;
use app\controllers\AppController;
use yii\db\Query;

class Message extends BaseImasMsgs
{
    public function create($params,$uid)
    {
        $this->courseid = $params['cid'];
        $this->msgfrom = $uid;
        $this->msgto = $params['receiver'];
        $this->title = $params['subject'];
        $this->message = $params['body'];
        $sendDate = AppController::dateToString();
        $this->senddate = $sendDate;
        if($params['isread'] == 4)
        {
            $this->isread = 4;
        }
        $this->save();
        return $this->id;
    }

    public static function getByCourseId($cid)
    {
        return Message::find()->where(['courseid' => $cid])->all();
    }

    public static function getSenders($cid)
    {
        return Message::find()->where(['courseid' => $cid])->groupBy(['msgfrom'])->all();
    }

    public static function getByCourseIdAsArray($cid)
    {
        return Message::find()->where(['courseid' => $cid])->asArray()->all();
    }

    public static function updateUnread($msgId)
    {
        $message = Message::getById($msgId);
        if($message->isread==1){
            $message->isread=0;
        }
        elseif($message->isread == 5) {
            $message->isread = 4;
        }elseif($message->isread==4) {
            $message->isread = 4;
        }elseif($message->isread>=9){
            $message->isread=8;
        }elseif($message->isread>=13){
            $message->isread=12;
        }
        else{
            $message->isread = 0;
        }
        $message -> save();
    }
    public static function updateRead($msgId)
    {
        $message = Message::getById($msgId);
        if($message->isread==0) {
            $message->isread=1;
        }
        elseif($message->isread==1) {
            $message->isread=1;
        }
        elseif($message->isread== 4) {
            $message->isread = 5;
        }
        elseif($message->isread==5){
                $message->isread=5;
        }
        elseif($message->isread==8){
            $message->isread=9;
        }
        elseif($message->isread==12){
            $message->isread=13;
        }
          $message->save();
    }
    public static function getById($id)
    {
        return Message::findOne($id);
    }

    public static function getByMsgId($msgId)
    {
        $message = Message::findOne($msgId);
        $message ->replied = AppConstant::NUMERIC_ONE;
        $message->save();
        return $message;
    }
    public static function deleteFromReceivedMsg($msgId)
    {
        $message =Message::getById($msgId);
        if($message)
        {
            if($message->isread != 4){
                if($message->isread == 5 ) {
                    $message->delete();
                }
                elseif($message->isread == 1) {
                    $message->isread = 3;
                    $message->save();
                }
                elseif($message->isread == 0) {
                    $message->isread = 2;
                    $message->save();
               }
                else{
                    $message->isread = 3;
                   $message->save();
                }
            }
            elseif($message->isread==4)
            {
                $message->delete();
             }
        }
    }
    public static function deleteFromSentMsg($msgId)
    {
        $message =Message::getById($msgId);
        if($message){
            if($message->isread==2) {
                $message->delete();
            }
            elseif($message->isread==3) {
                $message->delete();
            }
            else {
                if($message->isread>=8) {
                    $message->isread=$message->isread+4;
                }
                else {
                    $message->isread = 4;
                }
                $message->save();
            }
        }
    }
    public static function sentUnsendMsg($msgId)
    {
        $message = Message::getById($msgId);
            if($message){
                $message->delete();
            }
    }

    public function createReply($params)
    {
        $this->courseid = $params['cid'];
        $this->msgfrom = isset($params['sender']) ? $params['sender'] : null;
        $this->msgto = isset($params['receiver']) ? $params['receiver'] : null;
        $this->title = isset($params['subject']) ? $params['subject'] : null;
        $this->message = isset($params['body']) ? $params['body'] : null;
        $this->parent = isset($params['parentId']) ? $params['parentId'] : null;
        $baseId = isset($params['baseId']) ? $params['baseId'] : null;
        if ($baseId != 0)
        {
            $this->baseid = isset($params['baseId']) ? $params['baseId'] : null;

        }else{

            $baseId = isset($params['parentId']) ? $params['parentId'] : null;
            $this->baseid = $baseId;
        }
        $sendDate = AppController::dateToString();
        $this->senddate = $sendDate;
        $this->save();
        return $this->id;
    }

    public static function getByBaseId($msgId, $baseId)
    {
        if($baseId == 0)
        {
            $baseId = $msgId;
        }
        return Message::find()->where(['id' => $baseId])->orWhere(['baseid' => $baseId])->orderBy('senddate')->asArray()->all();
    }

    public static function getUsersToDisplay($uid)
    {
        $query = Yii::$app->db->createCommand("SELECT imas_msgs.id,imas_msgs.courseid,imas_msgs.title,imas_msgs.senddate,imas_msgs.replied,imas_users.LastName,imas_users.FirstName,imas_msgs.isread,imas_courses.name,imas_msgs.msgfrom,imas_users.hasuserimg FROM imas_msgs LEFT JOIN imas_users ON imas_users.id=imas_msgs.msgfrom LEFT JOIN imas_courses ON imas_courses.id=imas_msgs.courseid WHERE  imas_msgs.msgto= :uid AND (imas_msgs.isread&2)=0 ORDER BY imas_msgs.id DESC");
        $query->bindValue('uid',$uid);
        $data = $query->queryAll();
        return $data;
    }

    public static function getUsersToDisplayMessage($uid){
        $query = Yii::$app->db->createCommand("SELECT imas_msgs.id,imas_msgs.courseid,imas_msgs.title,imas_msgs.msgto,imas_msgs.senddate,imas_users.LastName,imas_users.FirstName,imas_msgs.isread FROM imas_msgs,imas_users WHERE imas_users.id=imas_msgs.msgto AND imas_msgs.msgfrom= :uid AND (imas_msgs.isread&4)=0 ORDER BY imas_msgs.id DESC");
        $query->bindValue('uid',$uid);
        $data = $query->queryAll();
        return $data;
    }

    public static function getUsersToUserMessage($userId){
        $query = new Query();
        $query	->select(['imas_users.id', 'imas_users.LastName', 'imas_users.FirstName'])
            ->distinct()
            ->from('imas_users')
            ->join(	'LEFT OUTER JOIN', 'imas_msgs', 'imas_users.id = imas_msgs.msgfrom')
            ->where('imas_msgs.msgto = :userId',[':userId' => $userId])
            ->orderBy('imas_users.LastName');
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public static function getUsersToCourseMessage($userId){
        $query = new Query();
        $query	->select(['imas_courses.id', 'imas_courses.name'])
            ->distinct()
            ->from('imas_courses')
            ->join(	'LEFT OUTER JOIN', 'imas_msgs', 'imas_courses.id=imas_msgs.courseid')
            ->where('imas_msgs.msgfrom= :userId',[':userId' => $userId]);
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public static function getSentUsersMessage($userId){
        $query = new Query();
        $query	->select(['imas_users.id', 'imas_users.LastName', 'imas_users.FirstName'])
            ->distinct()
            ->from('imas_users')
            ->join(	'LEFT OUTER JOIN', 'imas_msgs', 'imas_users.id = imas_msgs.msgto')
            ->where('imas_msgs.msgfrom = :userId',[':userId' => $userId])
            ->orderBy('imas_users.LastName');
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }
    public static function updateFlagValue($row)
    {
        $query = Message::getByMsgId($row);
        $query->isread = ($query->isread)^8;
        $query->save();
    }
    public static function getByCourseIdAndUserId($courseid, $userId)
    {
        return Message::find()->where(['courseid' => $courseid, 'msgto' => $userId])->all();
    }
    public static function isMessageHaveChild($messageId)
    {
        $message =  Message::find()->where(['parent' => $messageId])->all();
        $hasChild = AppConstant::NUMERIC_ZERO;
        if($message){
            $hasChild = AppConstant::NUMERIC_ONE;
        }
        return $hasChild;
    }

    public function saveNewMessage($params,$currentUser)
    {
        $now = time();
        $this->courseid = $params['cid'];
        $this->msgfrom = $currentUser['id'];
        $this->msgto = $params['sendto'];
        $this->title = $params['subject'];
        $this->message = $params['message'];
        $this->senddate = $now;
        $this->isread = AppConstant::NUMERIC_ZERO;
        $this->save();
    }


    public static function updateIsRead($params)
    {

        $setIsRead = Message::find()->where(['id' => $params['parentId']])->one();
       if($setIsRead)
       {
            $setIsRead->replied = AppConstant::NUMERIC_ONE;
           $setIsRead->isread = (($setIsRead->isread)&~1);
           $setIsRead->save();
       }

    }

    /*QUERY FOR TOTAL COUNT OF MESSAGE ON DASHBOARD*/
    public static function getMessageCount($userId)
    {
        $query  = new Query();
        $query  ->select(['courseid','COUNT(id)'])
                ->from('imas_msgs ')
                ->where('msgto= :userId',[':userId' => $userId])
                ->andWhere(['LIKE','isread','0'])
                ->orWhere(['LIKE','isread','4']);
                $query->groupBy('courseid');
        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public function insertFromUtilities($subject,$message,$msgTo,$userId,$now,$isRead,$courseId)
    {
        $this->title = $subject;
        $this->message = $message;
        $this->msgto = $msgTo;
        $this->msgfrom = $userId;
        $this->senddate = $now;
        $this->isread = $isRead;
        $this->courseid = $courseId;
        $this->save();
    }

    public static function getMsgIds($userid, $courseId){
        return Message::find()->select('id')->where(['msgto' => $userid, 'courseid' => $courseId])->andWhere(['OR','isread = 0' , 'isread = 4'])->all();
    }

    public static function deleteByMsgTo($userId)
    {
        $deleteId = Message::find()->where(['msgto' => $userId])->andWhere(['>','isread',1])->all();
        if($deleteId)
        {
            foreach($deleteId as $deleteSingleId){
                $deleteSingleId->delete();
            }
        }
    }

    public static function setIsRead($userId)
    {
        $query = "UPDATE imas_msgs SET isread=isread+2 WHERE msgto='$userId' AND isread<2";
        Yii::$app->db->createCommand($query)->execute();
    }

    public static function deleteByMsgFrom($userId)
    {
        $deleteId = Message::find()->where(['msgfrom' => $userId])->andWhere(['>','isread',1])->all();
        if($deleteId){
            foreach($deleteId as $deleteSingleId){
                $deleteSingleId->delete();
            }
        }

    }

    public static function setMsgFrom($userId)
    {
        $query = "UPDATE imas_msgs SET isread=isread+4 WHERE msgfrom='$userId' AND isread<2";
        Yii::$app->db->createCommand($query)->execute();
    }
}

