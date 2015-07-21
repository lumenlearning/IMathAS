<?php
namespace app\controllers\forum;

use app\components\AppConstant;
use app\models\Course;
use app\models\ExternalTools;
use app\models\forms\ChangeUserInfoForm;
use app\models\forms\ForumForm;
use app\controllers\AppController;
use app\models\forms\ThreadForm;
use app\models\ForumLike;
use app\models\ForumPosts;
use app\models\ForumSubscriptions;
use app\models\ForumThread;
use app\models\ForumView;
use app\models\Forums;
use app\models\GbCats;
use app\models\Items;
use app\models\LinkedText;
use app\models\Outcomes;
use app\models\Rubrics;
use app\models\StuGroupSet;
use app\models\Thread;
use app\models\User;
use app\components\htmLawed;
use yii\web\UploadedFile;
use app\components\AppUtility;
use Yii;

class ForumController extends AppController
{
    public $postData = array();
    public $totalPosts = array();
    public $children = array();
    public $filehandertypecfiles = 'local';

    /*
    * Controller Action To Redirect To Search Forum Page
    */
    public function actionSearchForum()
    {
        $this->guestUserHandler();
        $cid = $this->getParamVal('cid');
        $forum = Forums::getByCourseId($cid);
        $course = Course::getById($cid);
        $user = $this->getAuthenticatedUser();
        $model = new ForumForm();
        $model->thread = 'subject';
        $this->includeCSS(['dataTables.bootstrap.css', 'forums.css', 'dashboard.css']);
        $this->includeJS(['forum/forum.js', 'general.js?ver=012115', 'jquery.dataTables.min.js', 'dataTables.bootstrap.js']);
        $this->setReferrer();
        $responseData = array('model' => $model, 'forum' => $forum, 'cid' => $cid, 'users' => $user, 'course' => $course);
        return $this->renderWithData('forum', $responseData);
    }

    /*
    * Controller Action To Search All threads By Subject
    */
    public function actionGetForumNameAjax()
    {
        $this->guestUserHandler();
        $param = $this->getRequestParams();
        $userId = $this->getAuthenticatedUser()->id;
        $search = $param['search'];
        $courseId = $param['courseId'];
        $query = ForumForm::byAllSubject($search, $courseId, $userId);
        if ($query) {
            $searchThread = array();
            foreach ($query as $data) {
                $username = User::getById($data['userid']);
                $postdate = Thread::getById($data['threadid']);
                $repliesCount = ForumPosts::findCount($data['threadid']);
                $tempArray = array
                (
                    'parent' => $data['parent'],
                    'forumIdData' => $data['forumid'],
                    'threadId' => $data['threadid'],
                    'subject' => $data['subject'],
                    'views' => $data['views'],
                    'replyBy' => $repliesCount[0]['count'],
                    'postdate' => date('F d, o g:i a', $postdate->lastposttime),
                    'name' => ucfirst($username->FirstName) . ' ' . ucfirst($username->LastName),
                );
                array_push($searchThread, $tempArray);
            }
            $this->includeJS(['forum/forum.js']);
            return $this->successResponse($searchThread);

        } else {
            return $this->terminateResponse("No data Found");
        }
    }

    /*
    * Controller Action To Display All The Forums
    */
    public function actionGetForumsAjax()
    {
        $this->guestUserHandler();
        $currentTime = time();
        $param = $this->getRequestParams();
        $cid = $param['cid'];
        $sort = AppConstant::DESCENDING;
        $orderBy = 'id';
        $forums = Forums::getByCourseIdOrdered($cid, $sort, $orderBy);
        $user = $this->getAuthenticatedUser();
        if ($forums) {
            $forumArray = array();
            foreach ($forums as $key => $forum) {
                $threadCount = ForumThread::findThreadCount($forum->id);
                $postCount = count($forum->imasForumPosts);
                $lastObject = '';
                if ($postCount > AppConstant::NUMERIC_ZERO) {
                    $lastObject = $forum->imasForumPosts[$postCount - AppConstant::NUMERIC_ONE];
                }
                $tempArray = array
                (
                    'forumId' => $forum->id,
                    'forumName' => $forum->name,
                    'threads' => count($threadCount),
                    'posts' => $postCount,
                    'currentTime' => $currentTime,
                    'endDate' => $forum->enddate,
                    'rights' => $user->rights,
                    'lastPostDate' => ($lastObject != '') ? date('F d, o g:i a', $lastObject->postdate) : ''
                );
                array_push($forumArray, $tempArray);
            }
            $this->includeCSS(['forums.css']);
            $this->includeJS(['forum/forum.js']);

            return $this->successResponse($forumArray);
        } else {
            return $this->terminateResponse('No data');
        }
    }

    /*
     * Controller Action To Redirect To Thread Page
     */
    public function actionThread()
    {
        $this->guestUserHandler();
        $cid = $this->getParamVal('cid');
        $course = Course::getById($cid);
        $forumId = $this->getParamVal('forumid');
        $users = $this->getAuthenticatedUser();
        $this->setReferrer();
        $qq = 0;
        $this->includeCSS(['dataTables.bootstrap.css', 'forums.css', 'dashboard.css']);
        $this->includeJS(['jquery.dataTables.min.js', 'dataTables.bootstrap.js', 'general.js?ver=012115', 'forum/thread.js?ver=' . time() . '']);
        $responseData = array('cid' => $cid, 'users' => $users, 'forumid' => $forumId, 'course' => $course,'qq' => $qq);
        return $this->renderWithData('thread', $responseData);
    }

    /*
    * Controller Action To Display The Thraeds Present In That Particular Forum
    */
    public function actionGetThreadAjax()
    {
        $params = $this->getRequestParams();
        $currentUser = $this->getAuthenticatedUser();
        $isValue = $params['isValue'];
        $forumId = $params['forumid'];
        $hideLink = $params['hideLink'];
        $threads = ThreadForm::thread($forumId);
        $threadArray = array();
        $uniquesDataArray = array();
        if (!empty($threads)) {
            if ($isValue == AppConstant::NUMERIC_ONE) {
                foreach ($threads as $thread) {
                    $username = User::getById($thread['userid']);
                    $uniquesData = ForumView::getByThreadId($thread['threadid']);
                    $lastView = ForumView::getLastView($currentUser, $thread['threadid']);
                    $count = ForumView::uniqueCount($thread['threadid']);
                    $tagged = ForumView::forumViews($thread['threadid'], $currentUser['id']);
//AppUtility::dump($threads);
                    if ($tagged[0]['tagged'] == AppConstant::NUMERIC_ONE) {
                        $temparray = array
                        (
                            'parent' => $thread['parent'],
                            'threadId' => $thread['threadid'],
                            'forumiddata' => $thread['forumid'],
                            'subject' => $thread['subject'],
                            'views' => $thread['views'],
                            'replyby' => $thread['replyby'],
                            'postdate' => date('F d, o g:i a', $thread['postdate']),
                            'name' => AppUtility::getFullName($username->FirstName, $username->LastName),
                            'tagged' => $tagged[0]['tagged'],
                            'lastview' => date('F d, o g:i a', $lastView[0]['lastview']),
                            'userright' => $currentUser['rights'],
                            'postUserId' => $username->id,
                            'currentUserId' => $currentUser['id'],
                            'countArray' => $count,
                            'posttype' => $thread['posttype'],
                        );
                        array_push($threadArray, $temparray);
                        array_push($uniquesDataArray, $uniquesData);
                    }
                }
            } else if ($isValue == AppConstant::NUMERIC_TWO || $isValue == AppConstant::NUMERIC_THREE) {
                foreach ($threads as $thread) {
                    $username = User::getById($thread['userid']);
                    $uniquesData = ForumView::getByThreadId($thread['threadid']);
                    $lastView = ForumView::getLastView($currentUser, $thread['threadid']);
                    $count = ForumView::uniqueCount($thread['threadid']);
                    $tagged = ForumView::forumViews($thread['threadid'], $currentUser['id']);
                    if ($thread['postdate'] >= $lastView[AppConstant::NUMERIC_ZERO]['lastview'] && $currentUser['id'] != $username->id) {
                        $temparray = array
                        (
                            'parent' => $thread['parent'],
                            'threadId' => $thread['threadid'],
                            'forumiddata' => $thread['forumid'],
                            'subject' => $thread['subject'],
                            'views' => $thread['views'],
                            'replyby' => $thread['replyby'],
                            'postdate' => date('F d, o g:i a', $thread['postdate']),
                            'name' => AppUtility::getFullName($username->FirstName, $username->LastName),
                            'tagged' => $tagged[0]['tagged'],
                            'lastview' => date('F d, o g:i a', $lastView[0]['lastview']),
                            'userright' => $currentUser['rights'],
                            'postUserId' => $username->id,
                            'currentUserId' => $currentUser['id'],
                            'countArray' => $count,
                            'posttype' => $thread['posttype'],
                        );
                        if ($isValue == AppConstant::NUMERIC_THREE) {
                            array_push($threadArray, $temparray);
                            $ViewData = new ForumView();
                            $ViewData->inserIntoTable($threadArray);
                        } else {
                            array_push($threadArray, $temparray);
                        }
                        array_push($uniquesDataArray, $uniquesData);
                    }
                }
            } else {
                foreach ($threads as $thread) {
                    $username = User::getById($thread['userid']);
                    $uniquesData = ForumView::getByThreadId($thread['threadid']);
                    $lastView = ForumView::getLastView($currentUser, $thread['threadid']);
                    $tagged = ForumView::forumViews($thread['threadid'], $currentUser['id']);
                    $count = ForumView::uniqueCount($thread['threadid']);
                    $temparray = array
                    (
                        'parent' => $thread['parent'],
                        'threadId' => $thread['threadid'],
                        'forumiddata' => $thread['forumid'],
                        'subject' => $thread['subject'],
                        'views' => $thread['views'],
                        'replyby' => $thread['replyby'],
                        'postdate' => date('F d, o g:i a', $thread['postdate']),
                        'name' => AppUtility::getFullName($username->FirstName, $username->LastName),
                        'tagged' => $tagged[0]['tagged'],
                        'userright' => $currentUser['rights'],
                        'lastview' => date('F d, o g:i a', $lastView[0]['lastview']),
                        'postUserId' => $username->id,
                        'currentUserId' => $currentUser['id'],
                        'countArray' => $count,
                        'posttype' => $thread['posttype'],
                    );
                    array_push($threadArray, $temparray);
                    array_push($uniquesDataArray, $uniquesData);
                }
            }
            $FinalUniquesData = array();
            foreach ($uniquesDataArray as $unique) {
                foreach ($unique as $un) {
                    $username = User::getById($un['userid']);
                    $temparrayForUnique = array(
                        'threadId' => $un['threadid'],
                        'lastView' => date('F d, o g:i a', $un['lastview']),
                        'name' => AppUtility::getFullName($username->FirstName, $username->LastName),
                    );
                    array_push($FinalUniquesData, $temparrayForUnique);
                }
            }
        } else
        {
            return $this->terminateResponse('');

        }
        $this->includeJS(['forum/forum.js']);
        $responseData = array('threadArray' => $threadArray, 'uniquesDataArray' => $FinalUniquesData, 'isValue' => $isValue);
        return $this->successResponse($responseData);
    }

    /*
     * controller method for redirect to Move Thread page,This method is used to store moved thread data in database.
     */
    public function actionMoveThread()
    {
        $courseId = $this->getParamVal('courseId');
        $course = Course::getById($courseId);
        $threadId = $this->getParamVal('threadId');
        $forumId = $this->getParamVal('forumId');
        $forums = Forums::getByCourseId($courseId);
        $thread = ThreadForm::thread($forumId);
        $user = $this->getAuthenticatedUser();
        $forumArray = array();
        foreach ($forums as $key => $forum) {
            $tempArray = array
            (
                'forumId' => $forum->id,
                'forumName' => $forum->name,
                'courseId' => $forum->courseid,
            );
            array_push($forumArray, $tempArray);
        }
        if ($thread) {
            $threadArray = array();
            foreach ($thread as $data) {
                $tempArray = array(
                    'threadId' => $data['id'],
                    'forumIdData' => $data['forumid'],
                    'subject' => $data['subject'],
                    'parent' => $data['parent'],
                );
                array_push($threadArray, $tempArray);
            }
            if ($this->isPost()) {
                $params = $this->getRequestParams();
                $moveType = $params['movetype'];
                $thread_Id = $params['threadId'];
                if ($moveType == AppConstant::NUMERIC_ONE) {
                    $moveThreadId = $params['thread-name'];
                    ForumPosts::updatePostMoveThread($thread_Id, $moveThreadId);
                    Thread::deleteThreadById($thread_Id);
                } else {
                    $forum_Id = $params['forum-name'];
                    Thread::moveAndUpdateThread($forum_Id, $thread_Id);
                    ForumPosts::updateMoveThread($forum_Id, $thread_Id);
                }
                $this->includeCSS(['forums.css']);
                $this->includeJS(['forum/thread.js?ver=' . time() . '']);
                $responseData = array('cid' => $courseId, 'users' => $user, 'forumid' => $forumId, 'course' => $course);
                return $this->renderWithData('thread', $responseData);
            }
            $this->setReferrer();
            $this->includeJS(['forum/movethread.js']);
            $responseData = array('forums' => $forumArray, 'threads' => $threadArray, 'threadId' => $threadId, 'forumId' => $forumId, 'course' => $course, 'user' => $user);
            return $this->renderWithData('moveThread', $responseData);
        }
    }

    /*
    * controller method for redirect to modify post page with selected thread data.
    */
    public function actionModifyPost()
    {
        $this->guestUserHandler();
        $courseId = $this->getParamVal('courseId');
        $course = Course::getById($courseId);
        $currentUser = $this->getAuthenticatedUser();
        $threadId = $this->getParamVal('threadId');
        $forumId = $this->getParamVal('forumId');
        $thread = ThreadForm::thread($forumId);
        $threadArray = array();
        $this->includeJS(["editor/tiny_mce.js", 'editor/tiny_mce_src.js', 'general.js', 'forum/modifypost.js']);
        foreach ($thread as $data) {
            if (($data['id']) == $threadId) {
                $tempArray = array(
                    'threadId' => $data['threadid'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                );
//                AppUtility::dump($data);
                array_push($threadArray, $tempArray);
            }
        }

        if($this->isPost())
        {
            $params = $this->getRequestParams();
            ForumPosts::modifyPost($params);
            $responseData = array('threadId' => $threadId, 'forumId' => $forumId, 'course' => $course, 'thread' => $threadArray, 'currentUser' => $currentUser);
            return $this->renderWithData('modifyPost', $responseData);
        }
        $this->setReferrer();
        $responseData = array('threadId' => $threadId, 'forumId' => $forumId, 'course' => $course, 'thread' => $threadArray, 'currentUser' => $currentUser);
        return $this->renderWithData('modifyPost', $responseData);
    }

    /*
    * controller ajax method for fetch modified thread from Modify page and store in database.
    */
//    public function actionModifyPostAjax()
//    {
//        $params = $this->getRequestParams();
//        AppUtility::dump($params);
//        $threadId = $params['threadId'];
//        $message = trim($params['message']);
//        $subject = trim($params['subject']);
//        $replyBy = trim($params['replyBy']);
//        $postType = trim($params['postType']);
//        ForumPosts::modifyPost($threadId, $message, $subject, $postType, $replyBy);
//        $this->includeJS(['forum/modifypost.js']);
//        return $this->successResponse();
//    }

    /*
    * Controller Action To Redirect To Post Page
    */
    public function actionPost()
    {
        $this->guestUserHandler();
        $currentUser = $this->getAuthenticatedUser();
        $courseId = $this->getParamVal('courseid');
        $course = Course::getById($courseId);
        $threadId = $this->getParamVal('threadid');
        $forumId = $this->getParamVal('forumid');
        $prev = $this->getParamVal('prev');
        $next = $this->getParamVal('next');
        if ($next || $prev) {
            $prevNextValueArray = Thread::getNextThreadId($threadId, $next, $prev, $forumId);
            $threadId = $prevNextValueArray['threadId'];
        }
        $isNew = ForumView::getById($threadId, $currentUser);
        $tagValue = $isNew[0]['tagged'];
        $FullThread = ForumPosts::getbyid($threadId);
        $data = array();
        if ($currentUser['rights'] == AppConstant::NUMERIC_TEN && $FullThread[0]['posttype'] == AppConstant::NUMERIC_THREE) {
            $forumPostData = ForumPosts::getByThreadIdAndUserID($threadId, $currentUser['id']);
            $parentThread = ForumPosts::getbyParentId($forumPostData[0]['parent']);
            array_push($data, $parentThread);
            foreach ($forumPostData as $single) {
                array_push($data, $single);
            }
        } else {
            $data = ForumPosts::getbyid($threadId);
        }
        $titleCountArray = array();
        foreach ($data as $postData) {
            $this->children[$postData['parent']][] = $postData['id'];
            $username = User::getById($postData['userid']);
            $forumName = Forums::getById($postData['forumid']);
            $titleLevel = AppUtility::calculateLevel($postData['subject']);
            $likeImage = ForumLike::checkStatus($postData['id'], $currentUser);
            $count = new ForumLike();
            $likeCnt = $count->CalculateCount($postData['id']);
            $studentCount = AppConstant::NUMERIC_ZERO;
            $teacherCount = AppConstant::NUMERIC_ZERO;
            foreach ($likeCnt as $like) {
                $Rights = User::getById($like['userid']);
                if ($Rights->rights == AppConstant::STUDENT_RIGHT) {
                    $studentCount = $studentCount + AppConstant::NUMERIC_ONE;
                } elseif ($Rights->rights >= AppConstant::TEACHER_RIGHT) {
                    $teacherCount = $teacherCount + AppConstant::NUMERIC_ONE;
                }
                $tempArray = array(
                    'postId' => $like['postid'],
                    'studentCount' => $studentCount,
                    'teacherCount' => $teacherCount,
                );
            }
            array_push($titleCountArray, $tempArray);
            $tempArray = array();
            $tempArray['id'] = $postData['id'];
            $tempArray['threadId'] = $postData['threadid'];
            $tempArray['forumIdData'] = $postData['forumid'];
            $tempArray['subject'] = $titleLevel['title'];
            $tempArray['forumName'] = ucfirst($forumName->name);
            $tempArray['postdate'] = date('F d, o g:i a', $postData->postdate);
            $tempArray['postType'] = $postData['posttype'];
            $tempArray['name'] = AppUtility::getFullName($username->FirstName, $username->LastName);
            $tempArray['userRights'] = $username->rights;
            $tempArray['userId'] = $username->id;
            $tempArray['settings'] = $forumName->settings;
            $tempArray['hasImg'] = $username->hasuserimg;
            $tempArray['likeImage'] = $likeImage;
            $tempArray['studentCount'] = $studentCount;
            $tempArray['teacherCount'] = $teacherCount;
            $tempArray['likeCnt'] = count($likeCnt);
            $tempArray['lastView'] = $isNew[AppConstant::NUMERIC_ZERO]['lastview'];
            $tempArray['message'] = $postData['message'];
            $tempArray['level'] = $titleLevel['level'];
            $tempArray['replyBy'] = $postData['replyby'];
            $this->postData[$postData['id']] = $tempArray;
        }
        ForumPosts::saveViews($threadId);
        $viewsData = new ForumView();
        $viewsData->updateData($threadId, $currentUser);
        $this->createChild($this->children[key($this->children)]);
        $Count = new ForumLike();
        $likeCount = $Count->findCOunt($threadId);
        $myLikes = $Count->UserLikes($threadId, $currentUser);
        $this->setReferrer();
        $this->includeCSS(['forums.css']);
        $this->includeJS(["general.js", "forum/post.js?ver=<?php echo time() ?>"]);
        $responseData = array('postdata' => $this->totalPosts, 'course' => $course, 'currentUser' => $currentUser, 'forumId' => $forumId, 'threadId' => $threadId, 'tagValue' => $tagValue, 'prevNextValueArray' => $prevNextValueArray, 'likeCount' => $likeCount, 'mylikes' => $myLikes, 'titleCountArray' => $titleCountArray);
        return $this->render('post', $responseData);
    }

    public function createChild($childArray, $arrayKey = AppConstant::NUMERIC_ZERO)
    {
        $this->children = AppUtility::removeEmptyAttributes($this->children);
        foreach ($childArray as $superKey => $child) {
            array_push($this->totalPosts, $this->postData[$child]);
            unset($this->children[$arrayKey][$superKey]);
            if (isset($this->children[$child])) {
                return $this->createChild($this->children[$child], $child);
            } else {
                continue;
            }
        }
        if (count($this->children)) {
            $this->createChild($this->children[key($this->children)], key($this->children));
        }
    }

    /*
     * controller ajax method for fetch select as remove thread from Thread page and remove from database.
     */
    public function actionMarkAsRemoveAjax()
    {
        $params = $this->getBodyParams();
        $threadId = $params['threadId'];
        $checkPostOrThread = $params['checkPostOrThread'];
        ForumPosts::removeThread($threadId, $checkPostOrThread);
        ForumThread::removeThread($threadId);
        ForumView::removeThread($threadId);
        return $this->successResponse();
    }

    /*
     * Controller Action To Reply To A Post
     */
    public function actionReplyPost()
    {
        $this->guestUserHandler();
        $isPost = $this->getParamVal('listbypost');
        $courseId = $this->getParamVal('courseid');
        $course = Course::getById($courseId);
        $threadArray = array();
        $forumId = $this->getParamVal('forumid');
        $Id = $this->getParamVal('id');
        $threadId = $this->getParamVal('threadId');
        $threadData = ForumPosts::getbyidpost($Id);
        foreach ($threadData as $data) {
            $tempArray = array
            (
                'subject' => $data['subject'],
                'userName' => $data->user->FirstName . ' ' . $data->user->LastName,
                'message' => $data['message'],
                'postDate' => date('F d, o g:i a', $data['postdate']),
            );
            array_push($threadArray, $tempArray);
        }
        $this->includeCSS(['forums.css']);
        $this->includeJS(['editor/tiny_mce.js', 'editor/tiny_mce_src.js', 'general.js', 'forum/replypost.js']);
        $responseData = array('reply' => $threadArray, 'course' => $course, 'forumId' => $forumId, 'threadId' => $threadId, 'parentId' => $Id,'isPost' => $isPost);
        return $this->renderWithData('replyPost', $responseData);
    }

    public function actionReplyPostAjax()
    {
        $this->guestUserHandler();
        if ($this->isPost()) {
            $params = $this->getRequestParams();
            $isPost = $params['isPost'];
            $user = $this->getAuthenticatedUser();
            $reply = new ForumPosts();
            $reply->createReply($params, $user);
            return $this->successResponse($isPost);
        }
    }

    /*
     * Controller Action To Redirect To New Thread Page
     */
    public function actionAddNewThread()
    {
        $users = $this->getAuthenticatedUser();
        $userId = $this->getUserId();
        $rights = $users->rights;
        $forumId = $this->getParamVal('forumid');
        $courseId = $this->getParamVal('cid');
        $course = Course::getById($courseId);
        $forumName = Forums::getById($forumId);
        $this->includeJS(['editor/tiny_mce.js', 'editor/tiny_mce_src.js', 'general.js', 'forum/addnewthread.js']);
        $responseData = array('forumName' => $forumName, 'course' => $course, 'userId' => $userId, 'rights' => $rights);
        return $this->renderWithData('addNewThread', $responseData);
    }

    /*
     * Controller Action To Save The Newly Added Thread In Database
     */
    public function actionAddNewThreadAjax()
    {
        $this->guestUserHandler();
        if ($this->isPost()) {
            $params = $this->getRequestParams();
            $postType = AppConstant::NUMERIC_ZERO;
            if ($this->getAuthenticatedUser()->rights > AppConstant::NUMERIC_TEN) {
                $postType = $params['postType'];
            }
            $alwaysReplies = $params['alwaysReplies'];
            $date = strtotime($params['date'] . ' ' . $params['time']);
            $userId = $this->getUserId();
            $newThread = new ForumPosts();
            $threadId = $newThread->createThread($params, $userId, $postType, $alwaysReplies, $date);
            $newThread = new ForumThread();
            $newThread->createThread($params, $userId, $threadId);
            $views = new ForumView();
            $views->createThread($userId, $threadId);
            return $this->successResponse();
        }
    }

    /*Controller Action To Toggle The Flag Image On Click*/
    public function actionChangeImageAjax()
    {
        $params = $this->getRequestParams();
        $rowId = $params['rowId'];
        if ($rowId == -1) {
            $threadId = $params['threadId'];
            $userId = $params['userId'];
            ForumView::deleteByUserIdAndThreadId($threadId, $userId);
        } else {
            $updateView = new ForumView();
            $updateView->updateFlagValue($rowId, $params['userId']);
        }
        return $this->successResponse();
    }

    /*
     * Controller Action To Search All Post In A Forum
     */
    public function actionGetSearchPostAjax()
    {
        $this->guestUserHandler();
        $params = $this->getRequestParams();
        $courseId = $params['courseid'];
        $now = time();
        $forum = Forums::getByCourseId($courseId);
        $search = $params['search'];
        $checkBoxVal = $params['value'];
        $sort = AppConstant::DESCENDING;
        $orderBy = 'postdate';
        $query = ForumForm::byAllpost($search, $sort, $orderBy);
        if ($query) {
            $searchPost = array();
            foreach ($forum as $forumId) {
                foreach ($query as $data) {
                    if ($forumId['id'] == $data['forumid']) {
                        if ($this->getAuthenticatedUser()->rights == AppConstant::NUMERIC_TEN) {
                            if ($forumId['enddate'] > $now) {
                                $username = User::getById($data['userid']);
                                $postdate = Thread::getById($data['threadid']);
                                $forumName = Forums::getById($data['forumid']);
                                $tempArray = array
                                (
                                    'forumIdData' => $data['forumid'],
                                    'threadId' => $data['threadid'],
                                    'subject' => $data['subject'],
                                    'views' => $data['views'],
                                    'forumName' => ucfirst($forumName->name),
                                    'postdate' => date('F d, o g:i a', $postdate->lastposttime),
                                    'name' => ucfirst($username->FirstName) . ' ' . ucfirst($username->LastName),
                                    'message' => $data['message'],
                                );
                                array_push($searchPost, $tempArray);
                            }
                        } else {
                            $username = User::getById($data['userid']);
                            $postdate = Thread::getById($data['threadid']);
                            $forumName = Forums::getById($data['forumid']);
                            $tempArray = array
                            (
                                'forumIdData' => $data['forumid'],
                                'threadId' => $data['threadid'],
                                'subject' => $data['subject'],
                                'views' => $data['views'],
                                'forumName' => ucfirst($forumName->name),
                                'postdate' => date('F d, o g:i a', $postdate->lastposttime),
                                'name' => ucfirst($username->FirstName) . ' ' . ucfirst($username->LastName),
                                'message' => $data['message'],
                            );
                            array_push($searchPost, $tempArray);
                        }
                    }
                }
            }
            $this->includeJS(['forum/forum.js', 'forum/thread.js']);
            $responseData = array('data' => $searchPost, 'checkvalue' => $checkBoxVal, 'search' => $search);
            return $this->successResponse($responseData);
        } else {
            return $this->terminateResponse('No data');
        }
    }

    /*
     * Controller Action To Search Post Of That Forum
     */
    public function actionGetOnlyPostAjax()
    {
        $this->guestUserHandler();
        $params = $this->getRequestParams();
        $search = $params['search'];
        $forumId = $params['forumid'];
        $query = ForumForm::byAllpost($search);
        if ($query) {
            $searchPost = array();
            foreach ($query as $data) {
                if ($forumId == $data['forumid']) {
                    $username = User::getById($data['userid']);
                    $postdate = Thread::getById($data['threadid']);
                    $forumName = Forums::getById($data['forumid']);
                    $tempArray = array
                    (
                        'forumIdData' => $data['forumid'],
                        'threadId' => $data['threadid'],
                        'subject' => $data['subject'],
                        'views' => $data['views'],
                        'forumName' => ucfirst($forumName->name),
                        'postdate' => date('F d, o g:i a', $postdate->lastposttime),
                        'name' => ucfirst($username->FirstName) . ' ' . ucfirst($username->LastName),
                        'message' => $data['message'],
                    );
                    array_push($searchPost, $tempArray);
                }
            }
            $this->includeJS(['forum/forum.js', 'forum/thread.js']);
            $responseData = array('data' => $searchPost);
            return $this->successResponse($responseData);
        } else {
            return $this->terminateResponse('No Data');
        }
    }

    /*
     *
     */
    public function actionListPostByName()
    {
        $this->guestUserHandler();
        $userRights = $this->getAuthenticatedUser()->rights;
        $params = $this->getRequestParams();
        $courseId = $this->getParamVal('cid');
        $course = Course::getById($courseId);
        $sort = AppConstant::DESCENDING;
        $forumId = $params['forumid'];
        $forumName = Forums::getById($forumId);
        $orderBy = 'postdate';
        $thread = ThreadForm::postByName($forumId, $sort, $orderBy);
        if ($thread) {
            $nameArray = array();
            $sortByName = array();
            $finalSortedArray = array();
            $threadArray = array();
            foreach ($thread as $data) {
                $username = User::getById($data['userid']);
                $isNew = ForumView::getLastViewOfPost($data['threadid'], $this->getAuthenticatedUser()->id);
                $tempArray = array
                (
                    'id' => $data['id'],
                    'parent' => $data['parent'],
                    'threadId' => $data['threadid'],
                    'forumIdData' => $data['forumid'],
                    'userId' => $username->id,
                    'hasImg' => $username->hasuserimg,
                    'lastView' => $isNew[0]['lastview'],
                    'subject' => $data['subject'],
                    'postdate' => date('F d, o g:i a', $data['postdate']),
                    'message' => $data['message'],
                    'name' => AppUtility::getFullName($username->LastName, $username->FirstName),
                );
                if (!in_array($tempArray['name'], $nameArray))
                    array_push($nameArray, $tempArray['name']);
                array_push($threadArray, $tempArray);
            }
            sort($nameArray);
            foreach ($nameArray as $name) {
                foreach ($threadArray as $threadA) {
                    if ($name == $threadA['name']) {
                        array_push($finalSortedArray, $threadA);
                    }
                }
                array_push($sortByName, $name);
            }
            $this->setReferrer();
            $this->includeCSS(['forums.css']);
            $this->includeJS(['forum/listpostbyname.js']);
            $status = AppConstant::NUMERIC_ONE;
            $responseData = array('threadArray' => $finalSortedArray, 'forumId' => $forumId, 'forumName' => $forumName, 'course' => $course, 'status' => $status, 'userRights' => $userRights);
            return $this->renderWithData('listPostByName', $responseData);
        } else {
            $this->includeCSS(['forums.css']);
            $this->includeJS(['forum/listpostbyname.js']);
            $status = AppConstant::NUMERIC_ZERO;
            $responseData = array('status' => $status, 'forumId' => $forumId, 'course' => $course);
            return $this->renderWithData('listPostByName', $responseData);
        }
    }

    /*
     *
     */
    function actionReplyPostByName()
    {
        $this->guestUserHandler();
        $courseId = $this->getParamVal('cid');
        $course = Course::getById($courseId);
        $forumId = $this->getParamVal('forumid');
        $Id = $this->getParamVal('replyto');
        $threadId = $this->getParamVal('threadId');
        $threadData = ForumPosts::getbyidpost($Id);
        $threadArray = array();
        foreach ($threadData as $data) {
            $tempArray = array
            (
                'subject' => $data['subject'],
                'parent' => $data['id'],
            );
            array_push($threadArray, $tempArray);
        }
        $this->includeJS(['editor/tiny_mce.js', 'editor/tiny_mce_src.js', 'general.js', 'forum/replypostbyname.js']);
        $responseData = array('reply' => $threadArray, 'courseId' => $courseId, 'forumId' => $forumId, 'threadId' => $threadId, 'course' => $course);
        return $this->renderWithData('replyPostByName', $responseData);
    }

    public function actionReplyListPostAjax()
    {
        $this->guestUserHandler();
        if ($this->isPost()) {
            $params = $this->getRequestParams();
            $user = $this->getAuthenticatedUser();
            $reply = new ForumPosts();
            $reply->createReply($params, $user);
            return $this->successResponse();
        }
    }

    public function actionLikePostAjax()
    {
        $this->guestUserHandler();
        $userId = $this->getAuthenticatedUser()->id;
        $params = $this->getRequestParams();
        $like = $params['like'];
        if ($this->isPost()) {

            if ($like == AppConstant::NUMERIC_ZERO) {
                $like = new ForumLike();
                $like->InsertLike($params, $userId);

            } elseif ($like == AppConstant::NUMERIC_ONE) {
                $like = new ForumLike();
                $like->DeleteLike($params, $userId);
            }
        }
        return $this->successResponse();
    }

    public function actionDataLikePostAjax()
    {
        $this->guestUserHandler();
        $params = $this->getRequestParams();
        $count = new ForumLike();
        $displayCountData = $count->checkCount($params);
        $countDataArray = array();

        foreach ($displayCountData as $data) {
            $user = User::getById($data->userid);
            $tempArray = array('id' => $data->userid, 'userName' => AppUtility::getFullName($user->FirstName, $user->LastName));
            array_push($countDataArray, $tempArray);
        }
        $responseData = array('displayCountData' => $countDataArray);
        return $this->successResponse($responseData);
    }

    public function actionMarkAllReadAjax()
    {

        $this->guestUserHandler();
        $userId = $this->getAuthenticatedUser()->id;
        $viewsData = new ForumView();
        $viewsData->updateDataForPostByName($userId);
        return $this->successResponse();

    }

    public function actionAddForum()
    {
        $this->guestUserHandler();
        $params = $this->getRequestParams();
        $user = $this->getAuthenticatedUser();
        $courseId = $params['cid'];
        $course = Course::getById($courseId);
        $modifyForumId = $params['id'];
        $groupNames = StuGroupSet::getByCourseId($courseId);
        $key = AppConstant::NUMERIC_ZERO;
        foreach ($groupNames as $group) {
            $groupNameId[$key] = $group['id'];
            $groupNameLabel[$key] = 'Use group set:' . $group['name'];
            $key++;
        }
        $key = AppConstant::NUMERIC_ZERO;
        $gbcatsData = GbCats::getByCourseId($courseId);
        foreach ($gbcatsData as $singleGbcatsData) {
            $gbcatsId[$key] = $singleGbcatsData['id'];
            $gbcatsLabel[$key] = $singleGbcatsData['name'];
            $key++;
        }
        $rubrics = Rubrics::getByUserId($user['id']);
        foreach ($rubrics as $rubric) {
            $rubricsId[$key] = $rubric['id'];
            $rubricsLabel[$key] = $rubric['name'];
            $key++;
        }
        $OutcomesData = Outcomes::getByCourse($courseId);
        $key = AppConstant::NUMERIC_ONE;
        $pageOutcomes = array();
        if ($OutcomesData) {
            foreach ($OutcomesData as $singleData) {
                $pageOutcomes[$singleData['id']] = $singleData['name'];
                $key++;
            }
        }
        $pageOutcomesList = array();
        $query = $course['outcomes'];
        $outcomeArray = unserialize($query);
        $result = $this->flatArray($outcomeArray);
        if ($result) {
            foreach ($result as $singlePage) {
                array_push($pageOutcomesList, $singlePage);
            }
        }
        $pageTitle = AppConstant::ADD_FORUM;
        $saveTitle = AppConstant::CREATE_FORUM;
        $defaultValue = array(
            'startDate' => time(),
            'replyBy' => AppConstant::ALWAYS_TIME,
            'postBy' => AppConstant::ALWAYS_TIME,
            'hasSubScrip' => false,
            'hasGroupThreads' => AppConstant::NUMERIC_ZERO,
            'postTag' => 'FP',
            'replyTag' => 'FR',
            'cntInGb' => AppConstant::NUMERIC_ZERO,
            'points' => AppConstant::NUMERIC_ZERO,
            'forumType' => AppConstant::NUMERIC_ZERO,
            'tagList' => '',
            'rubric' => AppConstant::NUMERIC_ZERO,
            'groupSetId' => AppConstant::NUMERIC_ZERO,
            'gbCat' => AppConstant::NUMERIC_ZERO,
            'sortBy' => AppConstant::NUMERIC_ZERO,
            'tutorEdit' => AppConstant::NUMERIC_ZERO,
            'sDate' => date("m-d-Y"),
            'sTime' => time(),
            'eDate' => date("m-d-Y"),
            'eTime' => time("h-m-s"),
        );
        if ($this->isPost()) {
            $params = $this->getRequestParams();
            if (isset($modifyForumId)) {
                $updateForum = new Forums();
                $updateForum->UpdateForum($params, $modifyForumId);
            } else {
                $newForum = new Forums();
                $forumId = $newForum->addNewForum($params);
                $itemType = 'Forum';
                $itemId = new Items();
                $lastItemId = $itemId->saveItems($courseId, $forumId, $itemType);
                $subscriptionEntry = new ForumSubscriptions();
                $subscriptionEntry->AddNewEntry($forumId, $user['id']);
                $courseItemOrder = Course::getItemOrder($courseId);
                $itemOrder = $courseItemOrder->itemorder;
                $items = unserialize($itemOrder);
                $blockTree = array(0);
                $sub =& $items;
                for ($i = AppConstant::NUMERIC_ONE; $i < count($blockTree); $i++) {
                    $sub =& $sub[$blockTree[$i] - AppConstant::NUMERIC_ONE]['items'];
                }
                array_unshift($sub, intval($lastItemId));
                $itemOrder = serialize($items);
                $saveItemOrderIntoCourse = new Course();
                $saveItemOrderIntoCourse->setItemOrder($itemOrder, $courseId);
            }
            return $this->redirect(AppUtility::getURLFromHome('instructor', 'instructor/index?cid=' . $course->id));
        }
        if (isset($modifyForumId)) {
            $pageTitle = 'Modify Forum';
            $saveTitle = AppConstant::SAVE_BUTTON;
            $forumData = Forums::getById($modifyForumId);
            if ($forumData['groupsetid'] > AppConstant::NUMERIC_ZERO) {
                $threadData = Thread::getByForumId($modifyForumId);
                if (count($threadData) > AppConstant::NUMERIC_ZERO) {
                    $hasGroupThreads = true;
                } else {
                    $hasGroupThreads = false;
                }
            }
            $startDate = $forumData['startdate'];
            $endDate = $forumData['enddate'];
            $courseDefTime = $course['deftime'] % AppConstant::NUMERIC_THOUSAND;
            $hour = floor($courseDefTime / AppConstant::SECONDS) % AppConstant::NUMERIC_TWELVE;
            $minutes = $courseDefTime % AppConstant::SECONDS;
            $am = ($courseDefTime < AppConstant::NUMERIC_TWELVE * AppConstant::SECONDS) ? AppConstant::AM : AppConstant::PM;
            $defTime = (($hour == AppConstant::NUMERIC_ZERO) ? AppConstant::NUMERIC_TWELVE : $hour) . ':' . (($minutes < AppConstant::NUMERIC_TEN) ? '0' : '') . $minutes . ' ' . $am;
            $hour = floor($courseDefTime / AppConstant::SECONDS) % AppConstant::NUMERIC_TWELVE;
            $minutes = $courseDefTime % AppConstant::SECONDS;
            $am = ($courseDefTime < AppConstant::NUMERIC_TWELVE * AppConstant::SECONDS) ? AppConstant::AM : AppConstant::PM;
            $defStartTime = (($hour == AppConstant::NUMERIC_ZERO) ? AppConstant::NUMERIC_TWELVE : $hour) . ':' . (($minutes < AppConstant::NUMERIC_TEN) ? '0' : '') . $minutes . ' ' . $am;

            if ($startDate != AppConstant::NUMERIC_ZERO) {
                $sDate = AppUtility::tzdate("m/d/Y", $startDate);
                $sTime = AppUtility::tzdate("g:i a", $startDate);
            } else {
                $sDate = AppUtility::tzdate("m/d/Y", time());
                $sTime = $defStartTime;
            }
            if ($endDate != AppConstant::ALWAYS_TIME) {
                $eDate = AppUtility::tzdate("m/d/Y", $endDate);
                $eTime = AppUtility::tzdate("g:i a", $endDate);
            } else {
                $eDate = AppUtility::tzdate("m/d/Y", time() + AppConstant::WEEK_TIME);
                $eTime = $defTime;
            }
            $allNon = (($forumData['settings'] & AppConstant::NUMERIC_ONE) == AppConstant::NUMERIC_ONE);
            $allMod = (($forumData['settings'] & AppConstant::NUMERIC_TWO) == AppConstant::NUMERIC_TWO);
            $allDel = (($forumData['settings'] & AppConstant::NUMERIC_FOUR) == AppConstant::NUMERIC_FOUR);
            $allLikes = (($forumData['settings'] & AppConstant::NUMERIC_EIGHT) == AppConstant::NUMERIC_EIGHT);
            $viewAfterPost = (($forumData['settings'] & AppConstant::SIXTEEN) == AppConstant::SIXTEEN);
            $sortBy = $forumData['sortby'];
            $replyBy = $forumData['replyby'];
            $postBy = $forumData['postby'];
            $defDisplay = $forumData['defdisplay'];
            $subscriptionsData = ForumSubscriptions::getByForumIdUserId($modifyForumId, $user['id']);
            if (count($subscriptionsData) > AppConstant::NUMERIC_ZERO) {
                $hasSubScrip = true;
            }
            list($postTag, $replyTag) = explode('--', $forumData['caltag']);
            $cntInGb = $forumData['cntingb'];
            $defaultValue = array(
                'allowanon' => $allNon,
                'allowmod' => $allMod,
                'allowdel' => $allDel,
                'allowlikes' => $allLikes,
                'viewAfterPost' => $viewAfterPost,
                'sDate' => $sDate,
                'sTime' => $sTime,
                'eDate' => $eDate,
                'eTime' => $eTime,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'hasGroupThreads' => $hasGroupThreads,
                'hasSubScrip' => $hasSubScrip,
                'defDisplay' => $defDisplay,
                'sortBy' => $sortBy,
                'postBy' => $postBy,
                'replyBy' => $replyBy,
                'postTag' => $postTag,
                'replyTag' => $replyTag,
                'cntInGb' => $cntInGb,
                'points' => $forumData['points'],
                'gbCat' => $forumData['gbcategory']
            );
        }
            $this->includeJS(["forum/addforum.js","editor/tiny_mce.js", 'editor/tiny_mce_src.js', 'general.js', 'editor.js']);
            $responseData = array('course' => $course,'groupNameId' => $groupNameId, 'groupNameLabel' => $groupNameLabel,'saveTitle' => $saveTitle, 'pageTitle' => $pageTitle, 'rubricsLabel' => $rubricsLabel, 'rubricsId' => $rubricsId, 'pageOutcomesList' => $pageOutcomesList,
            'pageOutcomes' => $pageOutcomes, 'defaultValue' => $defaultValue,'forumData' => $forumData, 'modifyForumId' => $modifyForumId,
                'gbcatsLabel' => $gbcatsLabel, 'gbcatsId' => $gbcatsId);
            return $this->renderWithData('addForum', $responseData);
    }
    public function flatArray($outcomesData)
    {
        global $pageOutcomesList;
        if ($outcomesData) {
            foreach ($outcomesData as $singleData) {
                if (is_array($singleData)) { //outcome group
                    $pageOutcomesList[] = array($singleData['name'], AppConstant::NUMERIC_ONE);
                    $this->flatArray($singleData['outcomes']);
                } else {
                    $pageOutcomesList[] = array($singleData, AppConstant::NUMERIC_ZERO);
                }
            }
        }
        return $pageOutcomesList;
    }
}