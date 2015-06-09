<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 8/6/15
 * Time: 3:07 PM
 */

namespace app\models;


use app\components\AppUtility;
use app\models\_base\BaseImasForumThreads;

class ForumThread extends BaseImasForumThreads
{

    public function createThread($params,$userId)
    {
        $this->forumid = isset($params['forumId']) ? $params['forumId'] : null;
        $postdate = strtotime(date('F d, o g:i a'));
        $this->lastposttime = $postdate;
        $this->lastpostuser = $userId;
        $this->views = 0;
        $this->save();
        return($this->id);


    }


}