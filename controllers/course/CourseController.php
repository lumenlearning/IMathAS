<?php

namespace app\controllers\course;

use app\components\AppConstant;
use app\components\AppUtility;
use app\components\AssessmentUtility;
use app\components\filehandler;
use app\components\ShowItemCourse;
use app\models\_base\BaseImasGroups;
use app\models\AppModel;
use app\models\AssessmentSession;
use app\models\Blocks;
use app\models\CalItem;
use app\models\ContentTrack;
use app\models\Course;
use app\models\Assessments;
use app\models\Exceptions;
use app\models\forms\ChangeUserInfoForm;
use app\models\forms\CourseSettingForm;
use app\models\forms\ThreadForm;
use app\models\ForumPosts;
use app\models\ForumSubscriptions;
use app\models\ForumThread;
use app\models\ForumView;
use app\models\Grades;
use app\models\InstrFiles;
use app\models\LinkedText;
use app\models\Links;
use app\models\Forums;
use app\models\GbScheme;
use app\models\Items;
use app\models\Message;
use app\models\Questions;
use app\models\QuestionSet;
use app\models\SetPassword;
use app\models\Student;
use app\models\Teacher;
use app\models\InlineText;
use app\models\Thread;
use app\models\Wiki;
use app\models\User;
use app\models\GbCats;
use app\models\StuGroupSet;
use app\models\Rubrics;
use app\models\Outcomes;
use app\models\WikiRevision;
use app\models\WikiView;
use yii\web\UploadedFile;
use app\models\ExternalTools;
use Yii;
use app\controllers\AppController;
use app\models\forms\DeleteCourseForm;
use yii\db\Exception;
use yii\helpers\Html;
use app\components\CopyItemsUtility;

class CourseController extends AppController
{
    public $filehandertypecfiles = 'local';
    /**
     * Display all course in item order
     */
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $this->guestUserHandler();
        $params = $this->getRequestParams();
        $courseId = $this->getParamVal('cid');
        $this->setSessionData('courseId',$courseId);
        $this->checkSession($params);
        $user = $this->getAuthenticatedUser();
        $this->setSessionData('user',$user);
        $msgList = $this->getNotificationDataMessage($courseId,$user);
        $countPost = $this->getNotificationDataForum($courseId,$user);
        $this->setSessionData('messageCount',$msgList);
        $this->setSessionData('postCount',$countPost);
        $this->layout = 'master';
        $this->userAuthentication($user, $courseId);
        $userId = $user->id;
        $id = $this->getParamVal('id');
        $assessmentSession = AssessmentSession::getAssessmentSession($this->getUserId(), $id);
        $exception = Exceptions::getTotalData($userId);
        $responseData = array();
        $now = time();
        $calendarCount = array();
        $exceptionDataCount = array();
        $exceptions = array();
        $latePassHrs = array();
        $useviewbuttons = false;
        $previewshift = $this->getParamVal('stuview');
        $course = Course::getById($courseId);
        $topbar = explode('|',$course['topbar']);
        $topbar[0] = explode(',',$topbar[0]);
        $topbar[1] = explode(',',$topbar[1]);
        if (!isset($topbar[2])) {
            $topbar[2] = 0;
        }
        if ($topbar[0][0] == null) {
            unset($topbar[0][0]);
        }
        if ($topbar[1][0] == null) {
            unset($topbar[1][0]);
        }

        if ($course && ($itemOrders = unserialize($course->itemorder))) {

            foreach ($itemOrders as $key => $itemOrder) {
                $tempAray = array();
                if (is_array($itemOrder) || count($blockItems = $itemOrder['items'])) {
                    $tempAray['Block'] = $itemOrder;
                    $tempItemList = array();
                    $blockItems = $itemOrder['items'];
                    foreach ($blockItems as $blockKey => $blockItem) {
                        $tempItem = array();
                        $item = Items::getById($blockItem);
                        switch ($item->itemtype) {
                            case 'Assessment':

                                $assessment = Assessments::getByAssessmentId($item->typeid);
                                $exceptionData = Exceptions::getExceptionDataLatePass($userId);
                                $result = Course::getByLatePasshrs($courseId);
                                $hours = $result[0]['latepasshrs'];
                                foreach($exceptionData as $key1 => $line) {
                                    $exceptions[$line['id']] = array($line['startdate'],$line['enddate'],$line['islatepass'],$line['waivereqscore']);
                                }

                                if($previewshift > AppConstant::NUMERIC_ZERO){
                                    if($assessment['enddate'] > ($now + $previewshift))
                                    {
                                    $tempItem[$item->itemtype] = $assessment;
                                    array_push($calendarCount, $assessment);
                                    array_push($exceptionDataCount, $exceptions);
                                    array_push($latePassHrs, $hours);
                                    }
                                } else {
                                    $tempItem[$item->itemtype] = $assessment;
                                    array_push($calendarCount, $assessment);
                                    array_push($exceptionDataCount, $exceptions);
                                    array_push($latePassHrs, $hours);
                                }
                                break;
                            case 'Calendar':
                                $tempItem[$item->itemtype] = $itemOrder;
                                break;
                            case 'Forum':
                                $form = Forums::getById($item->typeid);
                                if($previewshift > AppConstant::NUMERIC_ZERO){
                                    if($form['enddate'] > ($now + $previewshift))
                                    {
                                    $tempItem[$item->itemtype] = $form;
                                    }
                                } else{
                                    $tempItem[$item->itemtype] = $form;
                                }
                                break;
                            case 'Wiki':
                                $wiki = Wiki::getById($item->typeid);
                                if($previewshift > AppConstant::NUMERIC_ZERO){
                                    if($wiki['enddate'] > ($now + $previewshift))
                                    {
                                    $tempItem[$item->itemtype] = $wiki;
                                    }
                                } else{
                                    $tempItem[$item->itemtype] = $wiki;
                                }
                                break;
                            case 'LinkedText':
                                $linkedText = Links::getById($item->typeid);
                                if($previewshift > AppConstant::NUMERIC_ZERO){
                                    if($linkedText['enddate'] > ($now + $previewshift))
                                    {
                                    $tempItem[$item->itemtype] = $linkedText;
                                    }
                                } else{
                                    $tempItem[$item->itemtype] = $linkedText;
                                }
                                break;
                            case 'InlineText':
                                $inlineText = InlineText::getById($item->typeid);
                                if($previewshift > AppConstant::NUMERIC_ZERO){
                                    if($inlineText['enddate'] > ($now + $previewshift))
                                    {
                                    $tempItem[$item->itemtype] = $inlineText;
                                    }
                                } else{
                                    $tempItem[$item->itemtype] = $inlineText;
                                }
                                break;
                        }

                        array_push($tempItemList, $tempItem);
                    }
                    $tempAray['itemList'] = $tempItemList;
                    array_push($responseData, $tempAray);
                } else {
                    $item = Items::getById($itemOrder);
                    switch ($item->itemtype) {
                        case 'Assessment':
                            $assessment = Assessments::getByAssessmentId($item->typeid);
                            $exceptionData = Exceptions::getExceptionDataLatePass($userId);
                            $result = Course::getByLatePasshrs($courseId);
                            $hours = $result[0]['latepasshrs'];
                            foreach($exceptionData as $key1 => $line) {

                                $exceptions[$line['typeid']] = array($line['startdate'],$line['enddate'],$line['islatepass'],$line['waivereqscore']);
                            }
                            if($previewshift > AppConstant::NUMERIC_ZERO)
                            {
                               if($assessment['enddate'] > ($now + $previewshift))
                               {
                                   $exception = Exceptions::getByAssessmentIdAndUserId($user->id, $assessment->id);
                                   if($exception){
                                       $assessment->startdate = $exception->startdate;
                                       $assessment->enddate = $exception->enddate;
                                   }
                                   $tempAray[$item->itemtype] = $assessment;
                                   array_push($responseData, $tempAray);
                                   array_push($calendarCount, $assessment);
                                   array_push($exceptionDataCount, $exceptions);
                                   $latePassHrs[$assessment['id']] = $hours;
                               }
                            }else
                            {
                                $exception = Exceptions::getByAssessmentIdAndUserId($user->id, $assessment->id);
                                if($exception){
                                    $assessment->startdate = $exception->startdate;
                                    $assessment->enddate = $exception->enddate;
                                }
                                $tempAray[$item->itemtype] = $assessment;
                                array_push($responseData, $tempAray);
                                array_push($calendarCount, $assessment);
                                array_push($exceptionDataCount, $exceptions);
                                $latePassHrs[$assessment['id']] = $hours;
                            }
                            break;
                        case 'Calendar':
                            $tempAray[$item->itemtype] = $itemOrder;
                            array_push($responseData, $tempAray);
                            break;
                        case 'Forum':
                            $form = Forums::getById($item->typeid);
                            if($previewshift > AppConstant::NUMERIC_ZERO){
                                if($form['enddate'] > ($now + $previewshift))
                                {
                                    $tempAray[$item->itemtype] = $form;
                                    array_push($responseData, $tempAray);
                                }
                            } else
                            {
                                $tempAray[$item->itemtype] = $form;
                                array_push($responseData, $tempAray);
                            }
                            break;
                        case 'Wiki':
                            $wiki = Wiki::getById($item->typeid);
                            if($previewshift > AppConstant::NUMERIC_ZERO){
                                if($wiki['enddate'] > ($now + $previewshift)){
                                    $tempAray[$item->itemtype] = $wiki;
                                    array_push($responseData, $tempAray);
                                }
                            }else{
                                $tempAray[$item->itemtype] = $wiki;
                                array_push($responseData, $tempAray);
                            }
                            break;
                        case 'InlineText':
                            $inlineText = InlineText::getById($item->typeid);
                            if($previewshift > AppConstant::NUMERIC_ZERO){
                                if($inlineText['enddate'] > ($now + $previewshift)){
                                    $tempAray[$item->itemtype] = $inlineText;
                                    array_push($responseData, $tempAray);
                                }
                            }else{
                                $tempAray[$item->itemtype] = $inlineText;
                                array_push($responseData, $tempAray);
                            }
                            break;
                        case 'LinkedText':
                            $linkedText = Links::getById($item->typeid);
                            if($previewshift > AppConstant::NUMERIC_ZERO){
                                if($linkedText['enddate'] > ($now + $previewshift)){
                                    $tempAray[$item->itemtype] = $linkedText;
                                    array_push($responseData, $tempAray);
                                }
                            }else{
                                $tempAray[$item->itemtype] = $linkedText;
                                array_push($responseData, $tempAray);
                            }
                            break;
                    }
                }
            }
        }
        $course = Course::getById($courseId);
        $student = Student::getByCId($courseId);
        $user = $this->getAuthenticatedUser();
        $message = Message::getByCourseIdAndUserId($courseId, $user->id);
        $isReadArray = array(AppConstant::NUMERIC_ZERO, AppConstant::NUMERIC_FOUR, AppConstant::NUMERIC_EIGHT, AppConstant::NUMERIC_TWELVE);
        $msgList = array();
        if ($message) {
            foreach ($message as $singleMessage) {
                if (in_array($singleMessage->isread, $isReadArray))
                    array_push($msgList, $singleMessage);
            }
        }

        $this->includeCSS(['fullcalendar.min.css', 'calendar.css', 'jquery-ui.css', 'course/course.css']);
        $this->includeJS(['moment.min.js', 'fullcalendar.min.js', 'student.js', 'latePass.js']);
        $returnData = array('calendarData' => $calendarCount, 'courseDetail' => $responseData, 'course' => $course, 'students' => $student, 'assessmentSession' => $assessmentSession, 'messageList' => $msgList, 'exception' => $exception, 'topbar1' => $topbar[0], 'topbar2' => $topbar[2], 'previewshift' => $previewshift, 'useviewbuttons' => $useviewbuttons, 'exceptionDataCount' => $exceptionDataCount, 'latePassHrs' => $latePassHrs);
        return $this->render('index', $returnData);
    }
    /**
     * Display assessment details
     */
    public function actionShowAssessment()
    {
        $this->guestUserHandler();
        $id = $this->getParamVal('id');
        $courseId = $this->getParamVal('cid');
        $assessment = Assessments::getByAssessmentId($id);
        $assessmentSession = AssessmentSession::getAssessmentSession($this->getUserId(), $id);
        $questionRecords = Questions::getByAssessmentId($id);
        $questionSet = QuestionSet::getByQuesSetId($id);
        $course = Course::getById($courseId);
        $this->saveAssessmentSession($assessment, $id);
        $this->includeCSS(['mathtest.css', 'default.css', 'showAssessment.css']);
        $this->includeJS(['timer.js']);
        $returnData = array('cid' => $course, 'assessments' => $assessment, 'questions' => $questionRecords, 'questionSets' => $questionSet, 'assessmentSession' => $assessmentSession, 'now' => time());
        return $this->render('ShowAssessment', $returnData);
    }
    /**
     * Show late passes of assessment.
     */
    public function actionLatePass()
    {
        $this->guestUserHandler();
        $assessmentId = $this->getParamVal('id');
        $courseId = $this->getParamVal('cid');
        $studentId = $this->getUserId();
        $exception = Exceptions::getByAssessmentId($assessmentId);
        $assessment = Assessments::getByAssessmentId($assessmentId);
        $student = Student::getByCourseId($courseId, $studentId);
        $course = Course::getById($courseId);
        $addTime = $course->latepasshrs * AppConstant::SECONDS * AppConstant::SECONDS;
        $currentTime = AppUtility::parsedatetime(date('m/d/Y'), date('h:i a'));
        $usedLatepasses = round(($assessment->allowlate - $assessment->enddate) / ($course->latepasshrs * AppConstant::MINUTES));
        $startDate = $assessment->startdate;
        $endDate = $assessment->enddate + $addTime;
        $wave = AppConstant::NUMERIC_ZERO;
        $param['assessmentid'] = $assessmentId;
        $param['userid'] = $studentId;
        $param['startdate'] = $startDate;
        $param['enddate'] = $endDate;
        $param['waivereqscore'] = $wave;
        $param['islatepass'] = AppConstant::NUMERIC_ONE;
        if (count($exception)) {
            if ((($assessment->allowlate % AppConstant::NUMERIC_TEN) == AppConstant::NUMERIC_ONE || ($assessment->allowlate % AppConstant::NUMERIC_TEN) - AppConstant::NUMERIC_ONE > $usedLatepasses) && ($currentTime < $exception->enddate || ($assessment->allowlate > AppConstant::NUMERIC_TEN && ($currentTime - $exception->enddate) < $course->latepasshrs * AppConstant::MINUTES))) {
                $latepass = $student->latepass;
                $student->latepass = $latepass - AppConstant::NUMERIC_ONE;
                $exception->enddate = $exception->enddate + $addTime;
                $exception->islatepass = $exception->islatepass + AppConstant::NUMERIC_ONE;
            }
            $exception->attributes = $param;
            $exception->save();
            $student->save();
        }
        $this->redirect(AppUtility::getURLFromHome('course', 'course/index?id=' . $assessmentId . '&cid=' . $courseId));
    }
    /**
     * Create new course at admin side
     */
    public function actionAddNewCourse()
    {
        $this->guestUserHandler();
        
        $model = new CourseSettingForm();

        if ($model->load($this->isPostMethod())) {
            $isSuccess = false;
            $bodyParams = $this->getRequestParams();
            $user = $this->getAuthenticatedUser();
            $course = new Course();
            $courseId = $course->create($user, $bodyParams, 1);
            if ($courseId) {
                $teacher = new Teacher();
                $teacherId = $teacher->create($user->id, $courseId);
                $gbScheme = new GbScheme();
                $gbSchemeId = $gbScheme->create($courseId);
                if ($teacherId && $gbSchemeId) {
                    $this->setSuccessFlash('Course added successfully. Course id: ' . $courseId . ' and Enrollment key: ' . $bodyParams['CourseSettingForm']['enrollmentKey']);
                    $this->redirect(AppUtility::getURLFromHome('admin', 'admin/index'));
                    $model = new CourseSettingForm();
                    $isSuccess = true;
                }
            }
            if (!$isSuccess) {
                $this->setErrorFlash(AppConstant::SOMETHING_WENT_WRONG);
            }
        }
        $this->includeCSS(["courseSetting.css"]);
        $this->includeJS(["courseSetting.js"]);
        $returnData = array('model' => $model);
        return $this->renderWithData('addNewCourse', $returnData);
    }
    /**
     * Setting in created course.
     */
    public function actionCourseSetting()
    {
        $this->guestUserHandler();
        $this->layout = 'master';
        $courseId = $this->getParamVal('cid');
        $course = Course::getById($courseId);
        if ($course) {
            $model = new CourseSettingForm();
            if ($model->load($this->isPostMethod())) {
                $courseData = $this->getRequestParams();
                $params = $courseData['CourseSettingForm'];
                $courseSetting['name'] = $params['courseName'];
                $courseSetting['enrollkey'] = $params['enrollmentKey'];
                $available = $this->getSanitizedValue($params['available'], AppConstant::AVAILABLE_NOT_CHECKED_VALUE);
                $courseSetting['available'] = AppUtility::makeAvailable($available);
                $courseSetting['copyrights'] = $params['copycourse'];
                $courseSetting['msgset'] = $params['messageSystem'];
                $toolSet = $this->getSanitizedValue($params['navigationLink'], AppConstant::NAVIGATION_NOT_CHECKED_VALUE);
                $courseSetting['toolset'] = AppUtility::makeToolset($toolSet);
                $courseSetting['deflatepass'] = $params['latePasses'];
                $courseSetting['theme'] = $params['theme'];
                $courseSetting['deftime'] = AppUtility::calculateTimeDefference($courseData['start_time'], $courseData['end_time']);
                $courseSetting['end_time'] = $courseData['end_time'];
                $courseSetting = AppUtility::removeEmptyAttributes($courseSetting);
                $course->attributes = $courseSetting;
                $course->save();
            } else {
                $selectionList = AppUtility::prepareSelectedItemOfCourseSetting($course);
                $this->includeCSS(["courseSetting.css"]);
                $returnData = array('model' => $model, 'course' => $course, 'selectionList' => $selectionList);
                return $this->renderWithData('courseSetting', $returnData);
            }
        }
        return $this->redirect(AppUtility::getURLFromHome('admin', 'admin/index'));
    }
    /**
     * To delete existing course.
     */
    public function actionDeleteCourse()
    {
        $model = new DeleteCourseForm();
        $courseId = $this->getParamVal('cid');
        $course = Course::getById($courseId);
        if ($course) {
            $status = Course::deleteCourse($course->id);
            if ($status) {
                $this->setSuccessFlash(AppConstant::DELETED_SUCCESSFULLY);
            } else {
                $this->setErrorFlash(AppConstant::SOMETHING_WENT_WRONG);
            }
        } else {
            $this->setErrorFlash(AppConstant::SOMETHING_WENT_WRONG);
        }
        $this->redirect(AppUtility::getURLFromHome('admin', 'admin/index'));
    }
    /**
     * @return string
     */
    public function actionTransferCourse()
    {
        $this->guestUserHandler();
        $courseId = $this->getParamVal('cid');
        $sortBy = AppConstant::FIRST_NAME;
        $order = AppConstant::ASCENDING;
        $users = User::findAllUsers($sortBy, $order);
        $course = Course::getById($courseId);
        $this->includeCSS(['dashboard.css']);
        $this->includeJS(['course/transferCourse.js']);
        $returnData = array('users' => $users, 'course' => $course);
        return $this->renderWithData('transferCourse', $returnData);
    }

    public function actionUpdateOwner()
    {
        if ($this->isPostMethod()) {
            $params = $this->getRequestParams();
            $user = $this->getAuthenticatedUser();
            if ($user->rights < AppConstant::LIMITED_COURSE_CREATOR_RIGHT) {
                $this->setErrorFlash(AppConstant::NO_ACCESS_RIGHTS);
            }
            $exec = false;
            $row = Course::setOwner($params, $user);
            if ($user->rights == AppConstant::GROUP_ADMIN_RIGHT) {
                $courseitem = Course::getByCourseAndGroupId($params['cid'], $user['groupid']);
                if ($courseitem > AppConstant::NUMERIC_ZERO) {
                    $row = Course::setOwner($params, $user);
                    $exec = true;
                }
            } else {
                $exec = true;
            }
            if ($exec && $row > AppConstant::NUMERIC_ZERO) {
                $teacher = Teacher::getByUserId($user->id, $params['cid']);
                if ($teacher == AppConstant::NUMERIC_ZERO) {
                    $newTeacher = new Teacher();
                    $newTeacher->create($params['newOwner'], $params['cid']);
                }
                Teacher::removeTeacher($user->id, $params['cid']);
            }
            return $this->successResponse();
        }
    }

    public function actionAddRemoveCourse()
    {
        $this->guestUserHandler();
        $this->layout = 'master';
        $cid = $this->getParamVal('cid');
        $this->includeJS(['course/addremovecourse.js']);
        $returnData = array('cid' => $cid);
        return $this->renderWithData('addRemoveCourse', $returnData);
    }

    public function actionGetTeachers()
    {
        $this->guestUserHandler();
        $params = $this->getRequestParams();
        $courseId = $params['cid'];
        $sortBy = AppConstant::FIRST_NAME;
        $order = AppConstant::ASCENDING;
        $users = User::findAllTeachers($sortBy, $order);
        $teachers = Teacher::getAllTeachers($courseId);
        $countTeach = count($teachers);
        $nonTeacher = array();
        $teacherIds = array();
        $teacherList = array();
        if ($teachers) {
            foreach ($teachers as $teacher) {
                $teacherIds[$teacher['userid']] = true;
            }
        }
        if ($users) {
            foreach ($users as $user) {
                if (isset($teacherIds[$user['id']])) {
                    array_push($teacherList, $user);
                } else {
                    array_push($nonTeacher, $user);
                }
            }
        }
        return $this->successResponse(array('teachers' => $teacherList, 'nonTeachers' => $nonTeacher,'countTeach' =>$countTeach));
    }

    public function actionAddTeacherAjax()
    {
        if ($this->isPostMethod()) {
            $params = $this->getRequestParams();
            $teacher = new Teacher();
            if ($params['userId'] != null && $params['cid'] != null) {
                $teacher->create($params['userId'], $params['cid']);
            }
            return $this->successResponse();
        }
    }

    public function actionRemoveTeacherAjax()
    {
        if ($this->isPostMethod()) {
            $params = $this->getRequestParams();
            $teacher = new Teacher();
            if ($params['userId'] != null && $params['cid'] != null) {
                $teacher->removeTeacher($params['userId'], $params['cid']);
            }
            return $this->successResponse();
        }
    }

    public function actionAddAllAsTeacherAjax()
    {
        if ($this->isPostMethod()) {
            $params = $this->getRequestParams();
            $courseId = $params['cid'];
            $usersIds = json_decode($params['usersId']);
            for ($i = AppConstant::NUMERIC_ZERO; $i < count($usersIds); $i++) {
                $teacher = new Teacher();
                $teacher->create($usersIds[$i], $courseId);
            }
            return $this->successResponse();
        }
    }

    public function actionRemoveAllAsTeacherAjax()
    {
        if ($this->isPostMethod()) {
            $params = $this->getRequestParams();
            $courseId = $params['cid'];
            $usersIds = json_decode($params['usersId']);
            $teachers = Teacher::getTeachersById($courseId);
            if (count($teachers) == count($usersIds)) {
                $this->setWarningFlash('You can not remove all Teachers, atleast one teacher is required for the course');
            }else {
                for ($i = AppConstant::NUMERIC_ZERO; $i < count($usersIds); $i++) {
                    $teacher = new Teacher();
                    $teacher->removeTeacher($usersIds[$i], $courseId);
                }
            }
            return $this->successResponse();

        }
    }

    /**
     * @param $assessment
     * @param $param
     * @param $id
     */
    public function saveAssessmentSession($assessment, $id)
    {
        list($qList, $seedList, $reviewSeedList, $scoreList, $attemptsList, $laList) = AppUtility::generateAssessmentData($assessment->itemorder, $assessment->shuffle, $assessment->id);
        $bestscorelist = $scoreList . ';' . $scoreList . ';' . $scoreList;
        $scoreList = $scoreList . ';' . $scoreList;
        $bestAttemptsList = $attemptsList;
        $bestSeedsList = $seedList;
        $bestLaList = $laList;
        $startTime = time();
        $defFeedbackText = $assessment->deffeedbacktext;
        $ltiSourcedId = '';
        $param['questions'] = $qList;
        $param['seeds'] = $seedList;
        $param['userid'] = $id;
        $param['assessmentid'] = $id;
        $param['attempts'] = $attemptsList;
        $param['lastanswers'] = $laList;
        $param['reviewscores'] = $scoreList;
        $param['reviewseeds'] = $reviewSeedList;
        $param['bestscores'] = $bestscorelist;
        $param['scores'] = $scoreList;
        $param['bestattempts'] = $bestAttemptsList;
        $param['bestseeds'] = $bestSeedsList;
        $param['bestlastanswers'] = $bestLaList;
        $param['starttime'] = $startTime;
        $param['feedback'] = $defFeedbackText;
        $param['lti_sourcedid'] = $ltiSourcedId;
        $assessmentSession = new AssessmentSession();
        $assessmentSession->attributes = $param;
        $assessmentSession->save();
    }
    /**
     * Display linked text on course page
     */
    public function actionShowLinkedText()
    {
        $this->layout = 'master';
        $user = $this->getAuthenticatedUser();
        $courseId = $this->getParamVal('cid');
        $id = $this->getParamVal('id');
        $course = Course::getById($courseId);
        $link = Links::getById($id);
        $this->includeCSS(['course/items.css']);
        $returnData = array('course' => $course, 'links' => $link, 'user' => $user);
        return $this->renderWithData('showLinkedText', $returnData);
    }
    /**
     * To handle event on calendar.
     */
    public function actionGetAssessmentDataAjax()
    {
        $this->guestUserHandler();
        $user = $this->getAuthenticatedUser();
        $params = $this->getRequestParams();
        $cid = $params['cid'];
        $currentDate = AppUtility::parsedatetime(date('m/d/Y'), date('h:i a'));
        $assessments = Assessments::getByCourseId($cid);
        $calendarItems = CalItem::getByCourseId($cid);
        $CalendarLinkItems = Links::getByCourseId($cid);
        $calendarInlineTextItems = InlineText::getByCourseId($cid);
        $assessmentArray = array();
        foreach ($assessments as $assessment)
        {
            $assessmentArray[] = array(
                'startDate' => AppUtility::getFormattedDate($assessment['startdate']),
                'endDate' => AppUtility::getFormattedDate($assessment['enddate']),
                'dueTime' => AppUtility::tzdate("g:i a", $assessment['enddate']),
                'reviewDate' => AppUtility::getFormattedDate($assessment['reviewdate']),
                'name' => ucfirst($assessment['name']),
                'startDateString' => $assessment['startdate'],
                'endDateString' => $assessment['enddate'],
                'reviewDateString' => $assessment['reviewdate'],
                'now' => AppUtility::parsedatetime(date('m/d/Y'), date('h:i a')),
                'assessmentId' => $assessment['id'],
                'courseId' => $assessment['courseid']
            );
        }
        $calendarArray = array();
        foreach ($calendarItems as $calendarItem) {
            $calendarArray[] = array(
                'courseId' => $calendarItem['courseid'],
                'date' => AppUtility::getFormattedDate($calendarItem['date']),
                'dueTime' => AppUtility::parsetime($calendarItem['date']),
                'title' => ucfirst($calendarItem['title']),
                'tag' => ucfirst($calendarItem['tag'])
            );
        }
        $calendarLinkArray = array();
        foreach ($CalendarLinkItems as $CalendarLinkItem) {
            $calendarLinkArray[] = array(
                'courseId' => $CalendarLinkItem['courseid'],
                'title' => ucfirst($CalendarLinkItem['title']),
                'startDate' => AppUtility::getFormattedDate($CalendarLinkItem['startdate']),
                'endDate' => AppUtility::getFormattedDate($CalendarLinkItem['enddate']),
                'dueTime' => AppUtility::getFormattedTime($CalendarLinkItem['enddate']),
                'now' => AppUtility::parsedatetime(date('m/d/Y'), date('h:i a')),
                'startDateString' => $CalendarLinkItem['startdate'],
                'endDateString' => $CalendarLinkItem['enddate'],
                'linkedId' => $CalendarLinkItem['id'],
                'calTag' => ucfirst($CalendarLinkItem['caltag'])
            );
        }
        $calendarInlineTextArray = array();
        foreach ($calendarInlineTextItems as $calendarInlineTextItem) {
            $calendarInlineTextArray[] = array(
                'courseId' => $calendarInlineTextItem['courseid'],
                'endDate' => AppUtility::getFormattedDate($calendarInlineTextItem['enddate']),
                'dueTime' => AppUtility::getFormattedTime($calendarInlineTextItem['enddate']),
                'now' => AppUtility::parsedatetime(date('m/d/Y'), date('h:i a')),
                'startDateString' => $calendarInlineTextItem['startdate'],
                'endDateString' => $calendarInlineTextItem['enddate'],
                'calTag' => ucfirst($calendarInlineTextItem['caltag'])
            );
        }
        $responseData = array('user' => $user,'assessmentArray' => $assessmentArray, 'calendarArray' => $calendarArray, 'calendarLinkArray' => $calendarLinkArray, 'calendarInlineTextArray' => $calendarInlineTextArray, 'currentDate' => $currentDate);
        return $this->successResponse($responseData);
    }

    public function actionBlockIsolate()
    {
        $this->guestUserHandler();
        $user = $this->getAuthenticatedUser();
        $this->layout = 'master';
        $courseId = $this->getParamVal('cid');
        $responseData = array();
        $calendarCount = array();
        $course = Course::getById($courseId);
        if ($course) {
            $itemOrders = unserialize($course->itemorder);

            if (count($itemOrders)) {
                foreach ($itemOrders as $key => $itemOrder)
                {
                $id = $this->getParamVal('blockId');
                if($itemOrder['id'] == $id)
                {
                    $tempAray = array();
                    if (is_array($itemOrder)) {
                        $tempAray['Block'] = $itemOrder;
                        $blockItems = $itemOrder['items'];
                        $tempItemList = array();

                        if (count($blockItems)) {
                            foreach ($blockItems as $blockKey => $blockItem) {
                                $tempItem = array();
                                $item = Items::getById($blockItem);
                                switch ($item->itemtype) {
                                    case 'Assessment':
                                        $assessment = Assessments::getByAssessmentId($item->typeid);
                                        $tempItem[$item->itemtype] = $assessment;
                                        $calendarCount[$item['id']]=  $assessment;
                                        break;
                                    case 'Calendar':
                                        $tempItem[$item->itemtype] = AppConstant::NUMERIC_ONE;
                                        break;
                                    case 'Forum':
                                        $form = Forums::getById($item->typeid);
                                        $tempItem[$item->itemtype] = $form;
                                        break;
                                    case 'Wiki':
                                        $wiki = Wiki::getById($item->typeid);
                                        $tempItem[$item->itemtype] = $wiki;
                                        break;
                                    case 'LinkedText':
                                        $linkedText = Links::getById($item->typeid);
                                        $tempItem[$item->itemtype] = $linkedText;
                                        break;
                                    case 'InlineText':
                                        $inlineText = InlineText::getById($item->typeid);
                                        $tempItem[$item->itemtype] = $inlineText;
                                        break;
                                }
                                array_push($tempItemList, $tempItem);
                            }
                        }
                        $tempAray['itemList'] = $tempItemList;
                        array_push($responseData, $tempAray);
                    }
                }
                }
            }
        }

        $message = Message::getByCourseIdAndUserId($courseId, $user->id);
        $isReadArray = array(AppConstant::NUMERIC_ZERO, AppConstant::NUMERIC_FOUR, AppConstant::NUMERIC_EIGHT, AppConstant::NUMERIC_TWELVE);
        $msgList = array();
        if ($message) {
            foreach ($message as $singleMessage) {
                if (in_array($singleMessage->isread, $isReadArray))
                    array_push($msgList, $singleMessage);
            }
        }
        $this->includeCSS(['fullcalendar.min.css', 'calendar.css', 'jquery-ui.css','course/course.css']);
        $this->includeJS(['moment.min.js','fullcalendar.min.js', 'student.js', 'latePass.js','course.js','course/instructor.js','course/addItem.js']);
        $returnData = array('course' => $course, 'messageList' => $msgList, 'courseDetail' => $responseData, 'user' => $user);

        return $this->render('blockIsolate', $returnData);
    }

/*
 *   Display calendar on click of menuBars
 */
    public function actionCalendar()
    {
        $this->layout = "master";
        $this->guestUserHandler();
        $user = $this->getAuthenticatedUser();
        $courseId = $this->getParamVal('cid');
        $countPost = $this->getNotificationDataForum($courseId,$user);
        $msgList = $this->getNotificationDataMessage($courseId,$user);
        $this->setSessionData('messageCount',$msgList);
        $this->setSessionData('postCount',$countPost);
        $course = Course::getById($courseId);
        $this->includeCSS(['fullcalendar.min.css', 'calendar.css', 'jquery-ui.css', 'course/course.css']);
        $this->includeJS(['moment.min.js', 'fullcalendar.min.js', 'student.js']);
        $responseData = array('course' => $course, 'user' => $user);
        return $this->render('calendar', $responseData);
    }
    /*
     * Modify inline text: Teacher
     */
    public function actionModifyInlineText()
    {
        global $outcomes;
        $this->guestUserHandler();
        $user = $this->getAuthenticatedUser();
        $this->layout = 'master';
        $userId = $user['id'];
        $params = $this->getRequestParams();
        $cid = $params['cid'];
        $teacherId = $this->isTeacher($user['id'], $cid);
        $this->noValidRights($teacherId);
        $block = $this->getParamVal('block');
        $inlineId = $params['id'];
        $course = Course::getById($cid);
        $inlineText = InlineText::getById($inlineId);
        $teacherId = $this->isTeacher($userId,$cid);
        $tutorId = $this->isTutor($userId, $cid);
        $tb = $this->getParamVal('tb');
        $block = $this->getParamVal('block');
        $moveFile = $this->getParamVal('movefile');
        if (isset($params['tb'])) {
            $filter = $params['tb'];
        } else {
            $filter = 'b';
        }
        if (!(isset($teacherId))) { // loaded by a NON-teacher
            $overWriteBody = AppConstant::NUMERIC_ONE;
            $body = "You need to log in as a teacher to access this page";
        } elseif (!($cid)) {
            $overWriteBody = AppConstant::NUMERIC_ONE;
            $body = "You need to access this page from the course page menu";
        }  else { // PERMISSIONS ARE OK, PROCEED WITH PROCESSING
            $page_formActionTag = "modify-inline-text?block=$block&cid=$cid&folder=" . $params['folder'];
            $page_formActionTag .= "&tb=$filter";

            $calTag = $params['caltag'];
            if ($params['title'] != null || $params['text'] != null || $params['sdate'] != null)
            { //if the form has been submitted
                if ($params['avail'] == AppConstant::NUMERIC_ONE) {
                    if ($params['sdatetype'] == '0')
                    {
                        $startDate = AppConstant::NUMERIC_ZERO;
                    } else {
                        $startDate = AppUtility::parsedatetime($params['sdate'], $params['stime']);
                    }
                    if ($params['edatetype'] == '2000000000') {
                        $endDate = AppConstant::ALWAYS_TIME;
                    } else {
                        $endDate = AppUtility::parsedatetime($params['edate'], $params['etime']);
                    }
                    $oncal = $params['oncal'];
                } else if ($params['avail'] == AppConstant::NUMERIC_TWO)
                {
                    if ($params['altoncal'] == AppConstant::NUMERIC_ZERO)
                    {
                        $startDate = AppConstant::NUMERIC_ZERO;
                        $oncal = AppConstant::NUMERIC_ZERO;
                    } else {
                        $startDate = AppUtility::parsedatetime($params['cdate'], "12:00 pm");
                        $oncal = AppConstant::NUMERIC_ONE;
                        $calTag = $params['altcaltag'];
                    }
                    $endDate =  AppConstant::ALWAYS_TIME;
                }else {
                    $startDate = AppConstant::NUMERIC_ZERO;
                    $endDate = AppConstant::ALWAYS_TIME;
                    $oncal = AppConstant::NUMERIC_ZERO;
                }
                if (isset($params['hidetitle'])) {
                    $params['title'] = '##hidden##';
                }
                if (isset($params['isplaylist'])) {
                    $isplaylist = AppConstant::NUMERIC_ONE;
                } else {
                    $isplaylist = AppConstant::NUMERIC_ZERO;
                }

                $params['title'] = addslashes(htmlentities(stripslashes($params['title'])));

                $params['text'] = addslashes(stripslashes($_POST['text']));
                $outcomes = array();
                if (isset($params['outcomes'])) {
                    foreach ($params['outcomes'] as $o) {
                        if (is_numeric($o) && $o>0) {
                            $outcomes[] = intval($o);
                        }
                    }
                }
                $outcomes = implode(',', $outcomes);

                $filestoremove = array();

                if (isset($params['id'])) {  //already have id; update
                    $tempArray = array();
                    $tempArray['startdate'] = $startDate;
                    $tempArray['courseid'] = $cid;
                    $tempArray['enddate'] = $endDate;
                    $tempArray['caltag'] = $calTag;
                    $tempArray['outcomes'] = $outcomes;
                    $tempArray['isplaylist'] = $isplaylist;
                    $tempArray['oncal'] = $oncal;
                    $tempArray['title'] = $params['title'];
                    $tempArray['text'] = $params['text'];
                    $tempArray['avail'] = $params['avail'];
                    $updateResult = new InlineText();
                    $result = $updateResult->updateChanges($tempArray, $params['id']);

                    //update attached files
                    $resultFile = InstrFiles::getFileName($params['id']);

                   foreach($resultFile as $key => $row) {
                        if (isset($params['delfile-'.$row['id']])) {
                            $filestoremove[] = $row['id'];
                             InstrFiles::deleteByItemId($row['id']);
                            $r2 = InstrFiles::getByIdForFile($row['filename']);
                            if (count($r2) == 0) {
                                //$uploaddir = rtrim(dirname(__FILE__), '/\\') .'/files/';
                                //unlink($uploaddir . $row[2]);
                                filehandler::deletecoursefile($row['filename']);
                            }
                        } else if ($params['filedescr-'.$row['id']] != $row['description']) {
                            $query = InstrFiles::setFileDescription($row['id'], $params['filedescr-'.$row['id']]);
                        }
                    }
                    $newtextid = $params['id'];
                } else { //add new


                    $tempArray = array();
                    $tempArray['cid'] = $cid;
                    $tempArray['startdate'] = $startDate;
                    $tempArray['enddate'] = $endDate;
                    $tempArray['caltag'] = $calTag;
                    $tempArray['outcomes'] = $outcomes;
                    $tempArray['isplaylist'] = $isplaylist;
                    $tempArray['oncal'] = $params['oncal'];
                    $tempArray['title'] = $params['title'];
                    $tempArray['text'] = $params['text'];
                    $tempArray['avail'] = $params['avail'];

                    $newInline = new InlineText();
                    $newtextid = $newInline->saveInlineText($tempArray);
                    $itemType = 'InlineText';
                    $itemId = new Items();
                    $itemid = $itemId->saveItems($cid, $newtextid, $itemType);
                    $courseItemOrder = Course::getItemOrder($cid);
                    $itemOrder = $courseItemOrder->itemorder;
                    $items = unserialize($itemOrder);
                    $blockTree = explode('-',$block);
                    $sub =& $items;
                    for ($i=1; $i<count($blockTree); $i++)
                    {
                        $sub =& $sub[$blockTree[$i]-1]['items']; //-1 to adjust for 1-indexing
                    }

                    if ($filter=='b') {
                        $sub[] = $itemid;
                    } else if ($filter=='t') {
                        array_unshift($sub,$itemid);
                    }

                    $itemOrder = serialize($items);
                    $saveItemOrderIntoCourse = new Course();
                    $saveItemOrderIntoCourse->setItemOrder($itemOrder, $cid);
                }
                if ($_FILES['userfile']['name']!='') {
                    $uploaddir = rtrim(dirname(__FILE__), '/\\') .'/files/';
                    $userfilename = preg_replace('/[^\w\.]/','',basename($_FILES['userfile']['name']));
                    $filename = $userfilename;
                    $extension = strtolower(strrchr($userfilename,"."));
                    $badextensions = array(".php",".php3",".php4",".php5",".bat",".com",".pl",".p");
                    if (in_array($extension,$badextensions)) {
                        $overWriteBody = 1;
                        $body = "<p>File type is not allowed</p>";
                    } else {
                        if (($filename = filehandler::storeuploadedcoursefile('userfile',$cid.'/'.$filename)) !== false) {
                            if (trim($params['newfiledescr'])=='') {
                                $params['newfiledescr'] = $filename;
                            }
                            $addedfileOne = new InstrFiles();
                            $addedfile = $addedfileOne->saveFile($params,$filename, $newtextid);
                            $params['id'] = $newtextid;
                        } else {
                            $overWriteBody = 1;
                            $body = "<p>Error uploading file!</p>\n";
                        }
                    }
                }
            }
            if (isset($addedfile) || count($filestoremove) > 0 || isset($params['movefile'])) {
                $fileorder = InlineText::getFileOrder($params['id']);

                if ($fileorder['fileorder'] == '') {
                    $fileorder = array();
                }
                if (isset($addedfile)) {
                    $fileorder[] = $addedfile;
                }
                if (count($filestoremove) > 0) {
                    for ($i=0; $i<count($filestoremove); $i++) {
                        $k = array_search($filestoremove[$i],$fileorder);
                        if ($k!==FALSE) {
                            array_splice($fileorder,$k,1);
                        }
                    }
                }
                if (isset($params['movefile'])) {
                    $from = $params['movefile'];
                    $to = $params['movefileto'];
                    $itemtomove = $fileorder[$from-1];  //-1 to adjust for 0 indexing vs 1 indexing
                    array_splice($fileorder,$from-1,1);
                    array_splice($fileorder,$to-1,0,$itemtomove);
                }
                $fileorder = implode(',',$fileorder);
                 InlineText::setFileOrder($params['id'],$fileorder);
            }
            if ($params['submitbtn'] == 'Submit') {
                return $this->redirect(AppUtility::getURLFromHome('course', 'course/course?cid=' .$cid));
            }

            if (isset($params['id'])) {
                $line = InlineText::getById($params['id']);
                if ($line['title']=='##hidden##') {
                    $hidetitle = true;
                    $line['title']='';
                }
                $startDate = $line['startdate'];
                $endDate = $line['enddate'];
                $fileorder = explode(',',$line['fileorder']);
                if ($line['avail']== 2 && $startDate > 0) {
                    $altoncal = 1;
                } else {
                    $altoncal = 0;
                }
                if ($line['outcomes']!='') {
                    $gradeoutcomes = explode(',',$line['outcomes']);
                } else {
                    $gradeoutcomes = array();
                }
                $savetitle = "Save Changes";
                $pageTitle = 'Modify Inline Text';
            } else {
                //set defaults
                $line['title'] = "Enter title here";
                $line['text'] = "<p>Enter text here</p>";
                $line['avail'] = 1;
                $line['oncal'] = 0;
                $line['caltag'] = '!';
                $altoncal = 0;
                $startDate = time();
                $endDate = time() + 7*24*60*60;
                $pageTitle = AppConstant::ADD_INLINE_TEXT;
                $hidetitle = false;
                $fileorder = array();
                $gradeoutcomes = array();
                $savetitle = "Create Item";
            }

            if ($startDate!=0) {
                $sdate = AppUtility::tzdate("m/d/Y",$startDate);
                $stime = AppUtility::tzdate("g:i a",$startDate);
            } else {
                $sdate = AppUtility::tzdate("m/d/Y",time());
                $stime = AppUtility::tzdate("g:i a",time());
            }
            if ($endDate!=2000000000) {
                $edate = AppUtility::tzdate("m/d/Y",$endDate);
                $etime = AppUtility::tzdate("g:i a",$endDate);
            } else {
                $edate = AppUtility::tzdate("m/d/Y",time()+7*24*60*60);
                $etime = AppUtility::tzdate("g:i a",time()+7*24*60*60);
            }

            if (isset($params['id'])) {
                $result = InstrFiles::getFileName($inlineId);
                $page_fileorderCount = count($fileorder);
                $i = 0;
                $page_FileLinks = array();
                if (count($result) > 0) {
                    foreach($result as $key => $row) {
                        $filedescr[$row['id']] = $row['description'];
                        $filenames[$row['id']] = rawurlencode($row['filename']);
                    }
                    foreach ($fileorder as $k=>$fid) {
                        $page_FileLinks[$k]['link'] = $filenames[$fid];
                        $page_FileLinks[$k]['desc'] = $filedescr[$fid];
                        $page_FileLinks[$k]['fid'] = $fid;

                    }
                }
            } else {
                $stime = AppUtility::tzdate("g:i a",time());
                $etime = AppUtility::tzdate("g:i a",time()+7*24*60*60);
            }

            $resultOutCome = Outcomes::getByCourseId($cid);

            $outcomenames = array();
           foreach($resultOutCome as $key => $row) {
                $outcomenames[$row['id']] = $row['name'];
            }
            $result = Course::getOutComeByCourseId($cid);
            $row = $result;
            if ($row['outcomes']=='') {
                $outcomearr = array();
            } else
            {
                $outcomearr = unserialize($row['outcomes']);
            }
            $outcomes = array();
            if($outcomearr)
            {
                $this->flattenarr($outcomearr);
            }
            $page_formActionTag .= (isset($params['id'])) ? "&id=" . $params['id'] : "";
        }
        $this->includeJS(["course/inlineText.js", "editor/tiny_mce.js", "editor/tiny_mce_src.js", "general.js","editor.js"]);
        $this->includeCSS(['course/items.css']);
        $responseData = array('page_formActionTag' => $page_formActionTag, 'filter' => $filter,'savetitle' => $savetitle, 'line' => $line, 'startDate' => $startDate, 'endDate' => $endDate, 'sdate' => $sdate, 'stime' => $stime, 'edate' => $edate, 'etime' => $etime, 'outcome' => $outcomes, 'page_fileorderCount' => $page_fileorderCount, 'page_FileLinks' => $page_FileLinks, 'params' => $params, 'hidetitle' => $hidetitle, 'caltag' => $calTag, 'inlineId' => $inlineId, 'course' => $course, 'pageTitle' => $pageTitle, 'outcomenames' => $outcomenames, 'gradeoutcomes' => $gradeoutcomes, 'block' => $block);
        return $this->renderWithData('modifyInlineText', $responseData);
    }

    public function actionAddLink()
    {
        $params = $this->getRequestParams();
        $user = $this->getAuthenticatedUser();
        $this->layout = 'master';
        $courseId = $params['cid'];
        $course = Course::getById($courseId);
        $modifyLinkId = $params['id'];
        $block = $this->getParamVal('block');
        $groupNames = StuGroupSet::getByCourseId($courseId);
        $model = new ThreadForm();
        $teacherId = $this->isTeacher($user['id'], $courseId);
        $this->noValidRights($teacherId);
        $query = Outcomes::getByCourse($courseId);
        $key = AppConstant::NUMERIC_ONE;
        $pageOutcomes = array();
        if (isset($params['tb'])) {
            $filter = $params['tb'];
        } else {
            $filter = 'b';
        }
        if ($query) {
            foreach ($query as $singleData) {
                $pageOutcomes[$singleData['id']] = $singleData['name'];
                $key++;
            }
        }
        $pageOutcomesList = array();
        if ($key > AppConstant::NUMERIC_ZERO) {//there were outcomes
            $query = $course['outcomes'];
            $outcomeArray = unserialize($query);
            global $outcomesList;
             $this->flatArray($outcomeArray);
            if ($outcomesList) {
                foreach ($outcomesList as $singlePage) {
                    array_push($pageOutcomesList, $singlePage);
                }
            }
        }
        $key = AppConstant::NUMERIC_ZERO;
        $gbCatsData = GbCats::getByCourseId($courseId);
        foreach ($gbCatsData as $group) {
            $gbCatsId[$key] = $group['id'];
            $gbCatsLabel[$key] = $group['name'];
            $key++;
        }
        $toolsData = ExternalTools::externalToolsDataForLink($courseId,$user['groupid']);
        $toolVals = array();
        $toolVals[0] = AppConstant::NUMERIC_ZERO;
        $key = AppConstant::NUMERIC_ONE;
        foreach ($toolsData as $tool) {
            $toolVals[$key++] = $tool['id'];
        }
        $toolLabels[0] = AppConstant::SELECT_TOOL;
        $key = AppConstant::NUMERIC_ONE;
        foreach ($toolsData as $tool)
        {
            $toolLabels[$key++] = $tool['name'];
        }

        if ($params['id']) {
            $linkData = LinkedText::getById($params['id']);
            if ($linkData['avail'] == AppConstant::NUMERIC_TWO && $linkData['startdate'] > AppConstant::NUMERIC_ZERO) {
                $altOnCal = AppConstant::NUMERIC_ONE;
            } else {
                $altOnCal = AppConstant::NUMERIC_ZERO;
            }
            if (substr($linkData['text'], AppConstant::NUMERIC_ZERO, AppConstant::NUMERIC_FOUR) == 'http') {
                $type = 'web';
                $webaddr = $linkData['text'];
                $linkData['text'] = "<p>Enter text here</p>";
            } else if (substr($linkData['text'], AppConstant::NUMERIC_ZERO, AppConstant::NUMERIC_FIVE) == 'file:') {
                $type = 'file';
                $fileInitialCount = AppConstant::NUMERIC_SIX + strlen($courseId);
                $filename = substr($linkData['text'], $fileInitialCount);
            } else if (substr($linkData['text'], AppConstant::NUMERIC_ZERO, AppConstant::NUMERIC_EIGHT) == 'exttool:') {
                $type = 'tool';
                $points = $linkData['points'];
                $toolParts = explode('~~', substr($linkData['text'], AppConstant::NUMERIC_EIGHT));
                $selectedTool = $toolParts[0];
                $toolCustom = $toolParts[1];
                if (isset($toolParts[2])) {
                    $toolCustomUrl = $toolParts[2];
                } else {
                    $toolCustomUrl = '';
                }
                if (isset($toolParts[3])) {
                    $gbCat = $toolParts[3];
                    $cntInGb = $toolParts[4];
                    $tutorEdit = $toolParts[5];
                    $gradeSecret = $toolParts[6];
                }
            } else {
                $type = 'text';
            }
            if ($linkData['outcomes'] != '') {
                $gradeOutcomes = explode(',', $linkData['outcomes']);
            } else {
                $gradeOutcomes = array();
            }
            if ($linkData['summary'] == '') {
                $line['summary'] = "<p>Enter summary here (displays on course page)</p>";
            }
            $startDate = $linkData['startdate'];
            $endDate = $linkData['enddate'];
            if ($startDate != AppConstant::NUMERIC_ZERO) {
                $sDate = AppUtility::tzdate("m/d/Y", $startDate);
                $sTime = AppUtility::tzdate("g:i a", $startDate);
                $startDate =AppConstant::NUMERIC_ONE;
            } else {
                $sDate = date('m/d/Y');
                $sTime = time();
            }
            if ($endDate != AppConstant::ALWAYS_TIME) {
                $eDate = AppUtility::tzdate("m/d/Y", $endDate);
                $eTime = AppUtility::tzdate("g:i a", $endDate);
                $endDate = AppConstant::NUMERIC_ONE;
            } else {
                $eDate = date("m/d/Y",strtotime("+1 week"));
                $eTime = time();
            }
            $saveTitle = "Modify Link";
            $saveButtonTitle = "Save Changes";
            $gradeSecret = uniqid();
            $defaultValues = array(
                'title' => $linkData['title'],
                'summary' => $linkData['summary'],
                'text' => $linkData['text'],
                'startDate' => $startDate,
                'gradeoutcomes' => $gradeOutcomes,
                'endDate' => $endDate,
                'sDate' => $sDate,
                'sTime' =>$sTime,
                'eDate' => $eDate,
                'eTime' => $eTime,
                'webaddr' => $webaddr,
                'filename' => $filename,
                'altoncal' => $altOnCal,
                'type' => $type,
                'toolparts' => $toolParts,
                'cntingb' => $cntInGb,
                'gbcat' => $gbCat,
                'tutoredit' => $tutorEdit,
                'gradesecret' => $gradeSecret,
                'saveButtonTitle' => $saveButtonTitle,
                'saveTitle' => $saveTitle,
                'points' => $points,
                'selectedtool' => $selectedTool,
                'toolcustom' => $toolCustom,
                'toolcustomurl' => $toolCustomUrl,
                'calendar' => $linkData['oncal'],
                'avail' => $linkData['avail'],
                'open-page-in' => $linkData['target'],
                'caltag' => $linkData['caltag'],
            );
        } else {
            $defaultValues = array(
                'saveButtonTitle' => AppConstant::CREATE_LINK,
                'saveTitle' => AppConstant::ADD_LINK,
                'title' => AppConstant::ENTER_TITLE,
                'summary' => AppConstant::ENTER_SUMMARY,
                'text' => "Enter text here",
                'gradeoutcomes' => array(),
                'type' => 'text',
                'points' => AppConstant::NUMERIC_ZERO,
                'sDate' => date("m/d/Y"),
                'sTime' => time(),
                'eDate' => date("m/d/Y",strtotime("+1 week")),
                'eTime' => time(),
                'calendar' => AppConstant::NUMERIC_ZERO,
                'avail' => AppConstant::NUMERIC_ONE,
                'open-page-in' => AppConstant::NUMERIC_ZERO,
                'cntingb' => AppConstant::NUMERIC_ONE,
                'tutoredit' => AppConstant::NUMERIC_ZERO,
                'filename' => ' ',
                'selectedtool' => AppConstant::NUMERIC_ZERO,
                'endDate' => AppConstant::NUMERIC_ONE,
                'startDate' => AppConstant::NUMERIC_ONE,
                'gradesecret' => uniqid(),
                'altoncal' => AppConstant::NUMERIC_ZERO,
                'caltag' => '!'
            );
        }
        $page_formActionTag = "add-link?block=$block&cid=$courseId&folder=" . $params['folder'];
        $page_formActionTag .= (isset($_GET['id'])) ? "&id=" . $_GET['id'] : "";
        $page_formActionTag .= "&tb=$filter";
        if ($this->isPostMethod()) { //after modify done, save into database
            $outcomes = array();
            if (isset($params['outcomes'])) {
                foreach ($params['outcomes'] as $o) {
                    if (is_numeric($o) && $o> AppConstant::NUMERIC_ZERO) {
                        $outcomes[] = intval($o);
                    }
                }
            }
            $outcomes = implode(',',$outcomes);
            if ($params['linktype'] == 'text') {
                /*
                 * To add htmllawed to link text
                 */
            } else if ($params['linktype'] == 'file') {
                $model->file = UploadedFile::getInstance($model, 'file');
                $path = AppConstant::UPLOAD_DIRECTORY .$courseId.'/';
                if ( ! is_dir($path)) {
                    mkdir($path);
                }
                if ($model->file) {
                    $filename = $path.$model->file->name;
                    $model->file->saveAs($filename);
                }
                $params['text'] = 	'file:'.$courseId.'/'.$model->file->name;
            } else if ($params['linktype'] == 'web') {
                $params['text'] = trim(strip_tags($params['web']));
                if (substr($params['text'], AppConstant::NUMERIC_ZERO, AppConstant::NUMERIC_FOUR) != 'http') {
                    $this->setSuccessFlash('Web link should start with http://');
                    return $this->redirect(AppUtility::getURLFromHome('course', 'course/add-link?cid=' . $course->id));
                }
            } else if ($params['linktype'] == 'tool') {
                if ($params['tool'] == AppConstant::NUMERIC_ZERO) {
                    $this->setSuccessFlash('Select external Tool');
                    return $this->redirect(AppUtility::getURLFromHome('course', 'course/add-link?cid=' . $course->id));
                } else {

                    $params['text'] = 'exttool:' . $params['tool'] . '~~' . $params['toolcustom'] . '~~' . $params['toolcustomurl'];
                    if ($params['usegbscore'] == AppConstant::NUMERIC_ZERO || $params['points'] == AppConstant::NUMERIC_ZERO) {
                        $params['points'] = AppConstant::NUMERIC_ZERO;
                    } else {
                        $params['text'] .= '~~' . $params['gbcat'] . '~~' . $params['cntingb'] . '~~' . $params['tutoredit'] . '~~' . $params['gradesecret'];
                        $params['points'] = intval($params['points']);
                    }
                }
            }
            if ($params['linktype'] == 'tool') {
                $externalToolsData = new ExternalTools();
                $externalToolsData->updateExternalToolsData($params);
            }
            $calTag = $params['tag'];
            if ($params['avail']== AppConstant::NUMERIC_ONE) {
                if ($params['available-after']== AppConstant::NUMERIC_ZERO)
                {
                    $startDate = AppConstant::NUMERIC_ZERO;
                } else if ($params['available-after']=='now') {
                    $startDate = time();
                } else {
                    $startDate = AppUtility::parsedatetime($params['sdate'], $params['stime']);
                }

                if ($params['available-until']== AppConstant::ALWAYS_TIME) {
                    $endDate = AppConstant::ALWAYS_TIME;
                } else {
                    $endDate = AppUtility::parsedatetime($params['edate'], $params['etime']);
                }
                $onCal = $params['oncal'];
            } else if ($params['avail']== AppConstant::NUMERIC_TWO) {
                if ($params['altoncal']== AppConstant::NUMERIC_ZERO) {
                    $startDate = AppConstant::NUMERIC_ZERO;
                    $onCal = AppConstant::NUMERIC_ZERO;
                } else {
                    $startDate = AppUtility::parsedatetime($params['cdate'],"12:00 pm");
                    $onCal = AppConstant::NUMERIC_ONE;
                    $calTag = $params['tag-always'];
                }
                $endDate =  AppConstant::ALWAYS_TIME;
            } else {
                $startDate = AppConstant::NUMERIC_ONE;
                $endDate = AppConstant::ALWAYS_TIME;
                $onCal = AppConstant::NUMERIC_ZERO;
            }
            $finalArray['courseid'] = $params['cid'];
            $finalArray['title'] = $params['name'];
            $str = AppConstant::ENTER_SUMMARY;
            if ($params['summary']== $str) {
                $finalArray['summary'] = ' ';
            } else {
                /*
                 * Apply html lawed here
                 */
                $finalArray['summary'] = $params['summary'];
            }
            $finalArray['text'] = $params['text'];
            $finalArray['avail'] = $params['avail'];
            $finalArray['oncal'] = $onCal;
            $finalArray['caltag'] = $calTag;
            $finalArray['target'] = $params['target'];
            $finalArray['points'] = $params['points'];
            $finalArray['target'] = $params['open-page-in'];
            $finalArray['startdate'] = $startDate;
            $finalArray['enddate'] = $endDate;
            $finalArray['outcomes'] = $outcomes;
            if ($modifyLinkId) {
                $finalArray['id'] = $params['id'];
                $link = new LinkedText();
                $link->updateLinkData($finalArray);
            } else {
                $linkText = new LinkedText();
                $linkTextId = $linkText->AddLinkedText($finalArray);
                $itemType = AppConstant::LINK;
                $itemId = new Items();
                $lastItemId = $itemId->saveItems($courseId, $linkTextId, $itemType);
                $courseItemOrder = Course::getItemOrder($courseId);
                $itemOrder = $courseItemOrder->itemorder;
                $items = unserialize($itemOrder);
                $blockTree = explode('-',$block);
                $sub =& $items;
                for ($i = AppConstant::NUMERIC_ONE; $i < count($blockTree); $i++) {
                    $sub =& $sub[$blockTree[$i] - AppConstant::NUMERIC_ONE]['items']; //-1 to adjust for 1-indexing
                }
                if ($filter=='b') {
                    $sub[] = $lastItemId;
                } else if ($filter=='t') {
                    array_unshift($sub,$lastItemId);
                }
                $itemOrder = (serialize($items));
                $saveItemOrderIntoCourse = new Course();
                $saveItemOrderIntoCourse->setItemOrder($itemOrder, $courseId);
            }
            $this->includeJS(["editor/tiny_mce.js", "general.js"]);
            return $this->redirect(AppUtility::getURLFromHome('course', 'course/course?cid=' . $course->id));
        }
        $this->includeCSS(["course/items.css"]);
        $this->includeJS(["editor/tiny_mce.js", "course/addlink.js", "general.js"]);
        $responseData = array('model' => $model, 'course' => $course, 'groupNames' => $groupNames,'pageOutcomesList' => $pageOutcomesList, 'linkData' => $linkData,
            'pageOutcomes' => $pageOutcomes, 'toolvals' => $toolVals, 'gbcatsLabel' => $gbCatsLabel, 'gbcatsId' => $gbCatsId, 'toollabels' => $toolLabels, 'defaultValues' => $defaultValues,'block' => $block, 'page_formActionTag' => $page_formActionTag);
        return $this->renderWithData('addLink', $responseData);
    }

    function mkdir_recursive($pathname, $mode = AppConstant::TRIPLE_SEVEN)
    {
        is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }

    function doesfileexist($type, $key)
    {
        if ($type == 'cfile') {
            if ($GLOBALS['filehandertypecfiles'] == 's3') {
                $s3 = new S3($GLOBALS['AWSkey'], $GLOBALS['AWSsecret']);
                return $s3->getObjectInfo($GLOBALS['AWSbucket'], 'cfiles/' . $key, false);
            } else {
                $base = rtrim(dirname(dirname(__FILE__)), '/\\') . '/course/files/';
                return file_exists($base . $key);
            }
        } else {
            if ($GLOBALS['filehandertype'] == 's3') {
                $s3 = new S3($GLOBALS['AWSkey'], $GLOBALS['AWSsecret']);
                return $s3->getObjectInfo($GLOBALS['AWSbucket'], $key, false);
            } else {
                $base = rtrim(dirname(dirname(__FILE__)), '/\\') . '/filestore/';
                return file_exists($base . $key);
            }
        }
    }

    public function flatArray($outcomesData)
    {
        global $outcomesList;
        if ($outcomesData) {
            foreach ($outcomesData as $singleData) {
                if (is_array($singleData)) { //outcome group
                    $outcomesList[] = array($singleData['name'], AppConstant::NUMERIC_ONE);
                    $this->flatArray($singleData['outcomes']);
                } else {
                    $outcomesList[] = array($singleData, AppConstant::NUMERIC_ZERO);
                }
            }
        }
        return $outcomesList;
    }

    public function flattenarr($ar) {
        global $outcomes;
        foreach ($ar as $v)
        {
            if (is_array($v)) { //outcome group
                $outcomes[] = array($v['name'], 1);
                $this->flattenarr($v['outcomes']);
            } else {
                $outcomes[] = array($v, 0);
            }
        }
    }

    public function actionCourse()
    {
        global $teacherId,$isTutor,$isStudent,$courseId,$imasroot,$userId,$openBlocks,$firstLoad,$sessionData,$previewShift,$myRights,
               $hideIcons,$exceptions,$latePasses,$graphicalIcons,$isPublic,
               $studentInfo,$newPostCnts,$CFG,$latePassHrs,$hasStats,$toolSet,$readLinkedItems, $haveCalcedViewedAssess, $viewedAssess,
               $topBar, $msgSet, $newMsgs, $quickView, $courseNewFlag,$useViewButtons,$previewshift, $useviewButtons;
        $user = $this->getAuthenticatedUser();
        $this->layout = 'master';
        $myRights = $user['rights'];
        $userId = $user['id'];
        $overwriteBody = AppConstant::NUMERIC_ZERO;
        $courseId = $this->getParamVal('cid');
        $teacherId = $this->isTeacher($userId, $courseId);
        $isStudent = $this->isStudent($userId, $courseId);
        $stuView = $this->getParamVal('stuview');
        $params = $this->getRequestParams();
        $sessionId = $this->getSessionId();
        $sessionData = $this->getSessionData($sessionId);
        $teacherData = Teacher::getByUserId($userId,$courseId);
        $type = $this->getParamVal('type');
        if($myRights > AppConstant::STUDENT_RIGHT)
        {
            switch ($type) {
                case 'assessment':
                    return $this->redirect(AppUtility::getURLFromHome('assessment','assessment/add-assessment?cid='.$courseId));
                    break;
                case 'inlinetext':
                    return $this->redirect(AppUtility::getURLFromHome('course','course/modify-inline-text?courseId=' .$courseId));
                    break;
                case 'linkedtext':
                    return $this->redirect(AppUtility::getURLFromHome('course','course/add-link?cid='.$courseId));
                    break;
                case 'forum':
                    return $this->redirect(AppUtility::getURLFromHome('forum','forum/add-forum?cid='.$courseId));
                    break;
                case 'wiki':
                    return $this->redirect(AppUtility::getURLFromHome('wiki','wiki/add-wiki?courseId='.$courseId));
                    break;
                case 'block':
                    return $this->redirect(AppUtility::getURLFromHome('block','block/add-block?courseId='.$courseId.'&block=0&tb=t'));
                    break;
                case 'calendar':
                    break;
                case '':
                    break;
            }
        }
        if ($teacherData != null) {
            if ($myRights>AppConstant::STUDENT_RIGHT) {
                $teacherId = $teacherData['id'];
                if (isset($params['stuview'])) {
                    $sessionData['stuview'] = $params['stuview'];
                    $this->writesessiondata($sessionData,$sessionId);
                }
                if (isset($params['teachview'])) {
                    unset($sessionData['stuview']);
                    $this->writesessiondata($sessionData,$sessionId);
                }
                if (isset($sessionData['stuview'])) {
                    $previewShift = $sessionData['stuview'];
                    unset($teacherId);

                    $isStudent = $teacherData['id'];
                }
            } else {
                $isTutor = $teacherData['id'];
            }
        }
        $isTutor = $this->isTutor($userId, $courseId);
        $body = "";
        $from = $this->getParamVal('from');
        $to = $this->getParamVal('to');
        $toggleNewFlag = $this->getParamVal('togglenewflag');
        $quickView = $this->getParamVal('quickview');
        $folder = $this->getParamVal('folder');
        $course = Course::getById($courseId);
        $courseNewFlag = $course['newflag'];
        $courseName = $course['name'];
        $parent = AppConstant::NUMERIC_ZERO;
        $previewShift = -1;
        $previewshift = $this->getParamVal('stuview');
        $useviewButtons = false;
        $student = Student::getByCId($courseId);
        $line  = Student::getStudentData($userId, $courseId);
        if ($line != null) {
            $studentId = $line['id'];
            $studentInfo['timelimitmult'] = $line['timelimitmult'];
            $studentInfo['section'] = $line['section'];
        }
        if (!isset($teacherId) && !isset($isTutor) && !isset($isStudent))
        {

            /*
             * loaded by a NON-teacher
             */
            $overwriteBody = AppConstant::NUMERIC_ONE;
            $body = _("You are not enrolled in this course.  Please return to the <a href=\"#\">Home Page</a> and enroll\n");
        } else {
            /*
             * PERMISSIONS ARE OK, PROCEED WITH PROCESSING
             */
            if (($teacherId) && ($sessionData['sessiontestid']) && !($sessionData['actas']))
            {
                /*
                 * clean up coming out of an assessment
                 */
                filehandler::deleteasidfilesbyquery2('id',$sessionData['sessiontestid'],null,1);
                $sessionTestId = $sessionData['sessiontestid'];
                AssessmentSession::deleteId($sessionTestId);
            }

            if (($teacherId) && ($from) && ($to)) {
                $block = $this->getParamVal('block');
                $result = $course->itemorder;
                $items = unserialize($result);
                $blockTree = explode('-',$block);
                $sub =& $items;

                for($i = 1; $i < count($blockTree)-1; $i++)
                {
                    /*
                     * -1 to adjust for 1-indexing
                     */
                    $sub =& $sub[$blockTree[$i]-1]['items'];
                }
                if (count($blockTree) > 1)
                {
                    $curBlock =& $sub[$blockTree[$i]-1]['items'];
                    $blockLoc = $blockTree[$i]-1;
                } else {
                    $curBlock =& $sub;
                }

                $blockLoc = $blockTree[count($blockTree)-1]-1;

                if (strpos($to,'-')!==false)
                {
                    /*
                     * in or out of block
                     */
                    if ($to[0]=='O')
                    {
                        /*
                         * out of block
                         * +3 to adjust for other block params
                         */
                        $itemToMove = $curBlock[$from-1];
                        array_splice($curBlock, $from-1, 1);
                        if (is_array($itemToMove)) {
                            array_splice($sub,$blockLoc+1, 0, array($itemToMove));
                        } else {
                            array_splice($sub,$blockLoc+1, 0, $itemToMove);
                        }
                    } else {
                        /*
                         * in to block
                         * -1 to adjust for 0 indexing vs 1 indexing
                         */
                        $itemToMove = $curBlock[$from-1];
                        array_splice($curBlock,$from-1, 1);
                        $to = substr($to, 2);
                        if ($from<$to)
                        {
                            $adj = AppConstant::NUMERIC_ONE;
                        } else {
                            $adj = AppConstant::NUMERIC_ZERO;
                        }
                        array_push($curBlock[$to-1-$adj]['items'],$itemToMove);
                    }
                } else {
                    /*
                     * move inside block
                     * -1 to adjust for 0 indexing vs 1 indexing
                     */
                    $itemToMove = $curBlock[$from-1];
                    array_splice($curBlock, $from-1, 1);
                    if (is_array($itemToMove)) {
                        array_splice($curBlock, $to-1, 0, array($itemToMove));
                    } else {
                        array_splice($curBlock, $to-1, 0, $itemToMove);
                    }
                }
                $itemList = addslashes(serialize($items));
                Course::setItemOrder($itemList, $courseId);
                return $this->redirect('course?cid='.$courseId);
            }

            $line = Course::getCourseDataById($courseId);
            if ($line == null) {
                $overwriteBody = AppConstant::NUMERIC_ONE;
                $body = _("Course does not exist.  <a hre=\"../index.php\">Return to main page</a>") . "</body></html>\n";
            }

            $allowUnEnroll = $line['allowunenroll'];
            $hideIcons = $line['hideicons'];
            $graphicalIcons = ($line['picicons']==1);
            $pageTitle = $line['name'];
            $items = unserialize($line['itemorder']);
            $msgSet = $line['msgset']%5;
            $toolSet = $line['toolset'];
            $chatSet = $line['chatset'];
            $latePassHrs = $line['latepasshrs'];
            $useLeftBar = (($line['cploc']&1)==1);
            $useLeftStuBar = (($line['cploc']&2)==2);
            $useViewButtons = (($line['cploc']&4)==4);
            $topBar = explode('|',$line['topbar']);
            $topBar[0] = explode(',',$topBar[0]);
            $topBar[1] = explode(',',$topBar[1]);
            if (!($topBar[2]))
            {
                $topBar[2] = AppConstant::NUMERIC_ZERO;
            }
            if ($topBar[0][0] == null)
            {
                unset($topBar[0][0]);
            }
            if ($topBar[1][0] == null)
            {
                unset($topBar[1][0]);
            }

            if (($teacherId) && ($toggleNewFlag))
            {
                /*
                 * handle toggle of NewFlag
                 */
                $sub =& $items;
                $blockTree = explode('-',$toggleNewFlag);
                if (count($blockTree) > 1) {
                    for ($i = 1; $i < count($blockTree)-1; $i++)
                    {
                        /*
                         * -1 to adjust for 1-indexing
                         */
                        $sub =& $sub[$blockTree[$i]-1]['items'];
                    }
                }
                $sub =& $sub[$blockTree[$i]-1];
                if (!($sub['newflag']) || $sub['newflag'] == 0)
                {
                    $sub['newflag'] = AppConstant::NUMERIC_ONE;
                } else {
                    $sub['newflag'] = AppConstant::NUMERIC_ZERO;
                }
                $itemList = addslashes(serialize($items));
                Course::setItemOrder($itemList, $courseId);
            }

            /*
             * enable teacher guest access
             */

            if ((!isset($folder) || $folder == '') && !isset($sessionData['folder'.$courseId])) {
                $folder = '0';
                $sessionData['folder'.$courseId] = '0';
            } else if ((isset($folder) && $folder != '') && (!isset($sessionData['folder'.$courseId]) || $sessionData['folder'.$courseId] != $folder)) {
                $sessionData['folder'.$courseId] = $folder;
            } else if ((!isset($folder) || $folder == '') && isset($sessionData['folder'.$courseId])) {
                $folder = $sessionData['folder'.$courseId];
            }

            if (!($quickView) && !($sessionData['quickview'.$courseId])) {
                $quickView = false;
            } else if ($quickView) {
                $quickView = ($quickView);
                $sessionData['quickview'.$courseId] = $quickView;
            } else if (($sessionData['quickview'.$courseId])) {
                $quickView = $sessionData['quickview'.$courseId];
            }
            if ($quickView == "on") {
                $folder = '0';
            }
            if (($sessionData['ltiitemtype']) && $sessionData['ltiitemtype'] == 3)
            {
                if ($sessionData['lti_keytype'] != 'cc-of') {
                    $useLeftBar = false;
                    $useLeftStuBar = false;
                }
                $noCourseNav = true;
                $usernameInHeader = false;
            }
            /*
             * get exceptions
             */
            $now = time() + $previewShift;
            $exceptions = array();
            if (!($teacherId) && !($isTutor)) {
                $result = Exceptions::getItemData($userId);

                foreach($result as $key => $line){
                    $exceptions[$line['id']] = array($line['startdate'],$line['enddate'],$line['islatepass'],$line['waivereqscore']);
                }
            }
            /*
             * update block start/end dates to show blocks containing items with exceptions
             */
//            if (count($exceptions) > 0) {
//                upsendexceptions($items);
//            }

            if ($folder != '0')
            {
                $now = time() + $previewShift;
                $blockTree = explode('-',$folder);
                $backTrack = array();
                for ($i = 1; $i < count($blockTree); $i++)
                {
                    $backTrack[] = array($items[$blockTree[$i]-1]['name'],implode('-',array_slice($blockTree, 0, $i+1)));
                    if (!($teacherId) && !($isTutor) && $items[$blockTree[$i]-1]['avail'] < 2 && $items[$blockTree[$i]-1]['SH'][0]!='S' &&($now < $items[$blockTree[$i]-1]['startdate'] || $now > $items[$blockTree[$i]-1]['enddate'] || $items[$blockTree[$i]-1]['avail'] == '0'))
                    {
                        $folder = AppConstant::NUMERIC_ZERO;
                        $items = unserialize($line['itemorder']);
                        unset($backTrack);
                        unset($blockTree);
                        break;
                    }
                    if (($items[$blockTree[$i]-1]['grouplimit']) && count($items[$blockTree[$i]-1]['grouplimit']) > 0 && !($teacherId) && !($isTutor))
                    {
                        if (!in_array('s-'.$studentInfo['section'],$items[$blockTree[$i]-1]['grouplimit'])) {
                            echo 'Not authorized';
                            exit;
                        }
                    }
                    /*
                     * -1 to adjust for 1-indexing
                     */
                    $items = $items[$blockTree[$i]-1]['items'];
                }
            }
            //DEFAULT DISPLAY PROCESSING
            $jsAddress1 = AppUtility::getURLFromHome('course','course/course?cid=' .$courseId);
            $jsAddress2 = AppUtility::getHomeURL();

            $openBlocks = Array(0);
            $prevLoadedbLocks = array(0);
            if (isset($_COOKIE['openblocks-'.$courseId]) && $_COOKIE['openblocks-'.$courseId]!='')
            {
                $openBlocks = explode(',',$_COOKIE['openblocks-'.$courseId]);
                $firstLoad = false;
            } else
            {
                $firstLoad = true;
            }
            if (($_COOKIE['prevloadedblocks-'.$courseId]) && $_COOKIE['prevloadedblocks-'.$courseId]!='')
            {
                $prevLoadedbLocks = explode(',',$_COOKIE['prevloadedblocks-'.$courseId]);
            }
            $plbList = implode(',',$prevLoadedbLocks);
            $obList = implode(',',$openBlocks);

            $curBreadcrumb = $courseName;
            if (($backTrack) && count($backTrack) > 0)
            {
                if (($sessionData['ltiitemtype']) && $sessionData['ltiitemtype'] == 3)
                {
                    $curBreadcrumb = '';
                    $sendcrumb = '';
                    $depth = substr_count($sessionData['ltiitemid'][1],'-');
                    for ($i = $depth-1; $i < count($backTrack); $i++)
                    {
                        if ($i > $depth-1)
                        {
                            $curBreadcrumb .= " &gt; ";
                            $sendcrumb .= " &gt; ";
                        }
                        if ($i != count($backTrack)-1)
                        {
                            $curBreadcrumb .= "<a href=\"course?cid=$courseId&folder={$backTrack[$i][1]}\">";
                        }
                        $sendcrumb .= "<a href=\"course?cid=$courseId&folder={$backTrack[$i][1]}\">".stripslashes($backTrack[$i][0]).'</a>';
                        $curBreadcrumb .= stripslashes($backTrack[$i][0]);
                        if ($i != count($backTrack)-1)
                        {
                            $curBreadcrumb .= "</a>";
                        }
                    }
                    $curName = $backTrack[count($backTrack)-1][0];

                    if (count($backTrack) > $depth)
                    {
                        $backLink = "<span class=right><a href=\"course?cid=$courseId&folder=".$backTrack[count($backTrack)-2][1]."\">" . _('Back') . "</a></span><br class=\"form\" />";
                    }
                    $_SESSION['backtrack'] = array($sendcrumb,$backTrack[count($backTrack)-1][1]);
                } else {
                    $curBreadcrumb .= "<a href=\"course?cid=$courseId&folder=0\">$courseName</a> ";
                    for ($i = 0; $i < count($backTrack); $i++)
                    {
                        $curBreadcrumb .= " &gt; ";
                        if ($i!=count($backTrack)-1)
                        {
                            $curBreadcrumb .= "<a href=\"course?cid=$courseId&folder={$backTrack[$i][1]}\">";
                        }
                        $curBreadcrumb .= stripslashes($backTrack[$i][0]);
                        if ($i != count($backTrack)-1)
                        {
                            $curBreadcrumb .= "</a>";
                        }
                    }
                    $curName = $backTrack[count($backTrack)-1][0];
                    if (count($backTrack) == 1)
                    {
                        $backLink =  "<span class=right><a href=\"course?cid=$courseId&folder=0\">" . _('Back') . "</a></span><br class=\"form\" />";
                    } else {
                        $backLink = "<span class=right><a href=\"course?cid=$courseId&folder=".$backTrack[count($backTrack)-2][1]."\">" . _('Back') . "</a></span><br class=\"form\" />";
                    }
                }
//                print_r($curBreadcrumb); die;
            } else {
                $curBreadcrumb .= $courseName;
                $curName = ucfirst($courseName);
            }

            if ($msgSet < 4)
            {
                $result = Message::getCountOfId($userId, $courseId);
                $msgCnt = $result[0]['id'];
                if ($msgCnt > 0)
                {
                    $newMsgs = " <a href=\"$imasroot/msgs/newmsglist.php?cid=$courseId\" style=\"color:red\">" . sprintf(_('New (%d)'), $msgCnt) . "</a>";
                } else {
                    $newMsgs = '';
                }
            }
            $now = time();
            $result = ForumThread::getDataByUserId($teacherId, $courseId, $userId, $now);
            $newPostCnts = array();
            foreach($result as $key => $row) {
                $newPostCnts[$row['forumid']] = $row['COUNT(imas_forum_threads.id)'];
            }
            if (array_sum($newPostCnts) > 0)
            {
                $newPostsCnt = " <a href=\"$imasroot/forums/newthreads.php?cid=$courseId\" style=\"color:red\">" . sprintf(_('New (%d)'), array_sum($newPostCnts)) . "</a>";
            } else {
                $newPostsCnt = '';
            }

            /**
             *  get items with content views, for enabling stats link
             */
            if (($teacherId) || ($isTutor)) {
                $hasStats = array();

                $result = ContentTrack::getStatsData($courseId);
                foreach($result as $key => $row)
                {
                    $hasStats[$row['typeid']] = true;
                }
            }

            /*
             * get latepasses
             */
            if (!($teacherId) && !($isTutor) && $previewShift == -1)
            {
                $result = Student::getLatePassById($userId, $courseId);
                $latePasses = $result[0]['latepass'];
            } else {
                $latePasses = AppConstant::NUMERIC_ZERO;
            }
        }
        $this->includeCSS(['fullcalendar.min.css', 'calendar.css', 'jquery-ui.css','course/course.css', 'instructor.css']);
        $this->includeJS(['moment.min.js','fullcalendar.min.js','course.js','student.js', 'general.js', 'question/addquestions.js','course/instructor.js','course/addItem.js']);
        $responseData = array('teacherId' => $teacherId, 'course' => $course,'courseId' => $courseId, 'usernameInHeader' => $usernameInHeader, 'useLeftBar' => $useLeftBar, 'newMsgs' => $newMsgs, 'newPostCnts' => $newPostCnts, 'useViewButtons' => $useViewButtons, 'useLeftStuBar' => $useLeftStuBar, 'toolSet' => $toolSet, 'sessionData' => $sessionData, 'allowUnEnroll' => $allowUnEnroll, 'quickView' => $quickView, 'noCourseNav' => $noCourseNav, 'overwriteBody' => $overwriteBody, 'body' => $body, 'myRights' => $myRights,
        'items' => $items, 'folder' => $folder, 'parent' => $parent, 'firstLoad' => $firstLoad, 'jsAddress1' => $jsAddress1, 'jsAddress2' => $jsAddress2, 'curName' => $curName, 'curBreadcrumb' => $curBreadcrumb, 'isStudent' => $isStudent, 'students' => $student, 'newPostsCnt' => $newPostsCnt, 'backLink' => $backLink, 'type' => $type);
        return $this->renderWithData('course', $responseData);
    }

    /**
     * Get block items
     */
    public function actionGetBlockItems()
    {
        global $teacherId,$isTutor,$isStudent,$courseId,$imasroot,$userId,$openBlocks,$firstLoad,$sessionData,$previewShift,$myRights,
               $hideIcons,$exceptions,$latePasses,$graphicalIcons,$isPublic,
               $studentInfo,$newPostCnts,$CFG,$latePassHrs,$hasStats,$toolSet,$readLinkedItems, $haveCalcedViewedAssess, $viewedAssess;
        $user = $this->getAuthenticatedUser();
        $userId = $user['id'];
        $courseId = $this->getParamVal('cid');
        $teacherId = $this->isTeacher($userId, $courseId);
        $isTutor = $this->isTutor($userId, $courseId);
        $isStudent = $this->isStudent($userId, $courseId);
        $sessionId = $this->getSessionId();
        $sessionData = $this->getSessionData($sessionId);
        $folder = $this->getParamVal('folder');
        $previewShift = -1;

        if (!($teacherId) && !($isTutor) && !($isStudent)) {
            echo "You are not enrolled in this course.  Please return to the <a href=\"#\">Home Page</a> and enroll\n";
            exit;
        }

        $line = Course::getCourseDataById($courseId);
        if ($line == null) {
            echo "Course does not exist.  <a href=\"#\">Return to main page</a></body></html>\n";
            exit;
        }

        $allowUnEnroll = $line['allowunenroll'];
        $hideIcons = $line['hideicons'];
        $graphicalIcons = ($line['picicons']==1);
        $pageTitle = $line['name'];
        $items = unserialize($line['itemorder']);
        $msgSet = $line['msgset']%5;
        $latePassHrs = $line['latepasshrs'];
        $useLeftBar = ($line['cploc']==1);
        $topBar = explode('|',$line['topbar']);
        $toolSet = $line['toolset'];
        $topBar[0] = explode(',',$topBar[0]);
        $topBar[1] = explode(',',$topBar[1]);
        if ($topBar[0][0] == null) {unset($topBar[0][0]);}
        if ($topBar[1][0] == null) {unset($topBar[1][0]);}

        $now = time() + $previewShift;
        $exceptions = array();

        if (!($teacherId) && !($isTutor)) {
            $result = Exceptions::getExceptionDataLatePass($userId);
            foreach($result as $key => $line)
            {
               $exceptions[$line['id']] = array($line['startdate'],$line['enddate'],$line['islatepass'],$line['waivereqscore']);
            }
        }
            if (count($exceptions)>0) {
//                upsendexceptions($items);
            }

            if (strpos($folder,'-') !== false)
            {
                $now = time() + $previewShift;
                $blockTree = explode('-',$folder);
                $backTrack = array();
                for ($i = 1; $i < count($blockTree); $i++)
                {
                    $backTrack[] = array($items[$blockTree[$i]-1]['name'],implode('-',array_slice($blockTree,0,$i+1)));

                    if (!($teacherId) && !($isTutor) && $items[$blockTree[$i]-1]['avail'] < 2 && $items[$blockTree[$i]-1]['SH'][0] != 'S' &&($now<$items[$blockTree[$i]-1]['startdate'] || $now>$items[$blockTree[$i]-1]['enddate'] || $items[$blockTree[$i]-1]['avail']=='0'))
                    {
                        $folder = 0;
                        $items = unserialize($line['itemorder']);
                        unset($backTrack);
                        unset($blockTree);
                        break;
                    }
                    $items = $items[$blockTree[$i]-1]['items']; //-1 to adjust for 1-indexing
                }
            }

            $openBlocks = Array(0);
//            if (isset($_COOKIE['openblocks-'.$courseId]) && $_COOKIE['openblocks-'.$courseId]!='')
//            {
//                $openBlocks = explode(',',$_COOKIE['openblocks-'.$courseId]);}
//            if (isset($_COOKIE['prevloadedblocks-'.$courseId]) && $_COOKIE['prevloadedblocks-'.$courseId]!='')
//            {
//                $prevLoadedBlocks = explode(',',$_COOKIE['prevloadedblocks-'.$courseId]);
//            } else {
//                $prevLoadedBlocks = array();
//            }
//            if (in_array($folder,$prevLoadedBlocks))
//            {
//                $firstLoad = false;
//            } else
//            {
//                $firstLoad = true;
//            }

            if (!($teacherId) && !($isTutor) && $previewShift == -1)
            {
                $result = Student::getLatePassById($userId, $courseId);
                $latePasses = $result[0]['latepass'];
            } else {
                $latePasses = 0;
            }
            /*
             * get new forum posts info
             */
            $result = ForumThread::getDataByUserId($teacherId, $courseId, $userId, $now);
            $newPostCnts = array();
            foreach($result as $key => $row) {
                $newPostCnts[$row['forumid']] = $row['id'];
            }
            /*
             * get items with content views, for enabling stats link
             */
            if (($teacherId) || ($$isTutor)) {
                $hasStats = array();
                $result = ContentTrack::getStatsData($courseId);
                foreach($result as $key => $row) {
                    $hasStats[$row['typeid']] = true;
                }
            }

            if (count($items) > 0) {
                /*
                 * update block start/end dates to show blocks containing items with exceptions
                 */
                $showItems = new ShowItemCourse();
                $showItems->showItems($items,$folder);
            } else if ($teacherId) {
                echo ShowItemCourse::generateAddItem($folder,'b');
         }

//            if ($firstLoad) {
//                echo "<script>document.cookie = 'openblocks-$courseId=' + oblist;</script>\n";
//            }
            if (($isTutor) && ($sessionData['ltiitemtype']) && $sessionData['ltiitemtype']==3)
            {
                echo '<script type="text/javascript">$(".instrdates").hide();</script>';
            }

            $this->includeJS(['course.js']);
        }

    /*
     * Ajax method to copy course items
     */
    public function actionCopyItemsAjax()
    {
        $params = $this->getRequestParams();
        $courseId = $params['courseId'];
        $block = $params['block'];
        $itemType = $params['itemType'];
        $copyItemId = $params['copyid'];
        if (isset($params['noappend'])) {
            $params['append'] = "";
        } else {
            $params['append'] = AppConstant::COPY;
        }
        $params['ctc'] = $courseId;
        $gradeBookCategory = array();
        $gradeBookData =  GbCats::getByCourseId($courseId);
        if ($gradeBookData){
            foreach ($gradeBookData as $singleRecord){
                $gradeBookCategory[$singleRecord['id']] = $singleRecord['id'];
            }
        }
        global $outComes;
        $outComes = array();
        $outComesData = Outcomes::getByCourseId($courseId);
        if ($outComesData){
            foreach ($outComesData as $singleRecord){
                $outComes[$singleRecord['id']] = $singleRecord['id'];
            }
        }
        $courseData = Course::getById($courseId);
        $blockCount = $courseData['blockcnt'];
        $items = unserialize($courseData['itemorder']);
        $connection = $this->getDatabase();
        $transaction = $connection->beginTransaction();
        try{
            $notImportant = array();
            $this->copyCourseItems($items, AppConstant::NUMERIC_ZERO, false, $notImportant, $copyItemId, $blockCount, $gradeBookCategory, $params);
            CopyItemsUtility::copyrubrics();
            $itemOrder = serialize($items);
            Course::setBlockCount($itemOrder,$blockCount,$courseId);
            $transaction->commit();
        }catch (Exception $e){
            $transaction->rollBack();
            return false;
        }
        return $this->successResponse();
    }

    public function copyCourseItems(&$items, $parent, $copyInside, &$addToArray, $copyItemId, $blockCount, $gradeBookCategory, $params) {
        foreach ($items as $k => $item) {
            if (is_array($item)) {
                if (($parent.'-'.($k+AppConstant::NUMERIC_ONE)==$copyItemId) || $copyInside) { //copy block
                    $newBlock = array();
                    $newBlock['name'] = $item['name'].stripslashes($params['append']);
                    $newBlock['id'] = $blockCount;
                    $blockCount++;
                    $newBlock['startdate'] = $item['startdate'];
                    $newBlock['enddate'] = $item['enddate'];
                    $newBlock['avail'] = $item['avail'];
                    $newBlock['SH'] = $item['SH'];
                    $newBlock['colors'] = $item['colors'];
                    $newBlock['fixedheight'] = $item['fixedheight'];
                    $newBlock['grouplimit'] = $item['grouplimit'];
                    $newBlock['items'] = array();
                    if (count($item['items'])>AppConstant::NUMERIC_ZERO) {
                        $this->copyCourseItems($items[$k]['items'], $parent.'-'.($k+AppConstant::NUMERIC_ONE), true, $newBlock['items'], $copyItemId, $blockCount, $gradeBookCategory, $params);
                    }
                    if (!$copyInside) {
                        array_splice($items,$k+AppConstant::NUMERIC_ONE,AppConstant::NUMERIC_ZERO,array($newBlock));
                        return AppConstant::NUMERIC_ZERO;
                    } else {
                        $addToArray[] = $newBlock;
                    }
                } else {
                    if (count($item['items'])>AppConstant::NUMERIC_ZERO) {
                        $emptyArray = array();
                        $this->copyCourseItems($items[$k]['items'],$parent.'-'.($k+AppConstant::NUMERIC_ONE),false,$emptyArray,$copyItemId,$blockCount,$gradeBookCategory,$params);
                    }
                }
            } else {
                if ($item==$copyItemId || $copyInside) {
                    $newItem = CopyItemsUtility::copyitem($item,$gradeBookCategory,$params);
                    if (!$copyInside) {
                        array_splice($items,$k+AppConstant::NUMERIC_ONE,AppConstant::NUMERIC_ZERO,intval($newItem));
                        return AppConstant::NUMERIC_ZERO;
                    } else {
                        $addToArray[] = intval($newItem);
                    }
                }
            }
        }
    }

    /*
     * Ajax method to delete course items
     */
    public function actionDeleteItemsAjax()
    {
        $params = $this->getRequestParams();
        $user = $this->getAuthenticatedUser();
        $courseId = $params['courseId'];
        $block = $params['block'];
        $itemType = $params['itemType'];
        $itemId = $params['id'];
        $connection = $this->getDatabase();
        $transaction = $connection->beginTransaction();
        try{
            switch($itemType){
                case AppConstant::FORUM:
                    $itemDeletedId = Items::deleteByTypeIdName($itemId,$itemType);
                    AppUtility::UpdateitemOrdering($courseId,$block,$itemDeletedId);
                    Forums::deleteForum($itemId);
                    ForumSubscriptions::deleteSubscriptionsEntry($itemId,$user['id']);
                    $postId = ForumPosts::getForumPostByFile($itemId);
                    $threadIdArray = ForumThread::findThreadCount($itemId);
                    foreach($threadIdArray as $singleThread){
                        ForumView::deleteByForumIdThreadId($singleThread['id']);
                    }
                    ForumPosts::deleteForumPost($itemId);
                    Thread::deleteThreadByForumId($itemId);
                    break;
                case AppConstant::ASSESSMENT:
                    AssessmentSession::deleteByAssessmentId($itemId);
                    Questions::deleteByAssessmentId($itemId);
                    $itemDeletedId = Items::deleteByTypeIdName($itemId,$itemType);
                    Assessments::deleteAssessmentById($itemId);
                    AppUtility::UpdateitemOrdering($courseId,$block,$itemDeletedId);
                    break;
                case AppConstant::CALENDAR:
                    $itemDeletedId = Items::deletedCalendar($itemId,$itemType);
                    AppUtility::UpdateitemOrdering($courseId,$block,$itemDeletedId);
                    break;
                case AppConstant::INLINE_TEXT:
                    $itemDeletedId = Items::deleteByTypeIdName($itemId,$itemType);
                    InlineText::deleteInlineTextId($itemId);

                    InstrFiles::deleteById($itemId);
                    AppUtility::UpdateitemOrdering($courseId,$block,$itemDeletedId);
                    break;
                case AppConstant::WIKI:
                    $itemDeletedId = Items::deleteByTypeIdName($itemId,$itemType);
                    Wiki::deleteById($itemId);
                    WikiRevision::deleteByWikiId($itemId);
                    WikiView::deleteByWikiId($itemId);
                    AppUtility::UpdateitemOrdering($courseId,$block,$itemDeletedId);
                    break;
                case AppConstant::LINK:
                    $itemDeletedId = Items::deleteByTypeIdName($itemId,$itemType);
                    $linkData = Links::getById($itemId);
                    $points = $linkData['points'];
                    if($points > AppConstant::NUMERIC_ZERO){
                        Grades::deleteByGradeTypeId($itemId);
                    }
                    Links::deleteById($itemId);
                    AppUtility::UpdateitemOrdering($courseId,$block,$itemDeletedId);
                    break;
                case AppConstant::BLOCK:
                    $course = Course::getById($courseId);
                    $blockData = unserialize($course['itemorder']);
                    $blockTree = explode('-',$itemId);
                    $blockCnt='';
                    $blockId = array_pop($blockTree) - AppConstant::NUMERIC_ONE;
                    $sub =& $blockData;
                    if (count($blockTree)>AppConstant::NUMERIC_ONE)
                    {
                        for ($i=AppConstant::NUMERIC_ONE;$i<count($blockTree);$i++)
                        {
                            $sub =& $sub[$blockTree[$i]-AppConstant::NUMERIC_ONE]['items'];
                        }
                    }
                    $itemList =(serialize($blockData));
                    Course::setBlockCount($itemList,$blockCnt=null,$courseId);
            }
            $transaction->commit();
        }catch (Exception $e){
            $transaction->rollBack();
            return false;
        }
        return $this->successResponse();
    }
}