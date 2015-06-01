<?php

namespace app\controllers\assessment;

use app\components\AppUtility;
use app\controllers\AppController;
use app\models\Assessments;
use app\models\AssessmentSession;
use app\models\Course;
use app\models\Exceptions;
use app\models\Questions;
use app\models\QuestionSet;
use app\models\Student;
use app\components\AppConstant;
use app\models\AppModel;
use app\models\Blocks;
use app\models\forms\CourseSettingForm;
use app\models\forms\SetPassword;
use app\models\Links;
use app\models\Forums;
use app\models\GbScheme;
use app\models\Items;
use app\models\Teacher;
use app\models\InlineText;
use app\models\Wiki;
use app\models\User;
use Yii;
use app\models\forms\DeleteCourseForm;
use yii\db\Exception;
use yii\helpers\Html;

class AssessmentController extends AppController
{

//    public function actionShowAssessment()
//    {
//        $this->guestUserHandler();
//
//        $id = \Yii::$app->request->get('id');
//        $assessment = Assessments::getByAssessmentId($id);
//        $assessmentSession = AssessmentSession::getById($id);
//        $questionRecords = Questions::getByAssessmentId($id);
//        $questionSet = QuestionSet::getByQuesSetId($id);
//        $assessmentclosed = false;
//
//        if ($assessment->avail == 0) {
//            $assessmentclosed = true;
//        }
//
//        list($qlist,$seedlist,$reviewseedlist,$scorelist,$attemptslist,$lalist) =AppUtility::generateAssessmentData($assessment->itemorder,$assessment->shuffle, $assessment->id);
//
//        $bestscorelist = $scorelist.';'.$scorelist.';'.$scorelist;
//        $scorelist = $scorelist.';'.$scorelist;
//        $bestattemptslist = $attemptslist;
//        $bestseedslist = $seedlist;
//        $bestlalist = $lalist;
//        $starttime = time();
//        $deffeedbacktext = addslashes($assessment->deffeedbacktext);
//        $ltisourcedid = '';
//
//        $param['questions'] = $qlist;
//        $param['seeds'] = $seedlist;
//        $param['userid'] = $id;
//        $param['assessmentid'] = $id;
//        $param['attempts'] = $attemptslist;
//        $param['lastanswers'] = $lalist;
//        $param['reviewscores'] = $scorelist;
//        $param['reviewseeds'] = $reviewseedlist;
//        $param['bestscores'] = $bestscorelist;
//        $param['scores'] = $scorelist;
//        $param['bestattempts'] = $bestattemptslist;
//        $param['bestseeds'] = $bestseedslist;
//        $param['bestlastanswers'] = $bestlalist;
//        $param['starttime'] = $starttime;
//        $param['feedback'] = $deffeedbacktext;
//        $param['lti_sourcedid'] = $ltisourcedid;
//
//        $assessmentSession = new AssessmentSession();
//        $assessmentSession->attributes = $param;
//        $assessmentSession->save();
//
//        $this->includeCSS(['../css/mathtest.css']);
//        $this->includeCSS(['../css/default.css']);
//        $this->includeCSS(['../css/showAssessment.css']);
//        $this->includeJS(['../js/timer.js']);
//        return $this->render('ShowAssessment', ['assessments' => $assessment, 'questions' => $questionRecords, 'questionSets' => $questionSet]);
//    }
    public function actionIndex()
    {
        $this->guestUserHandler();

        $cid = $this->getParamVal('cid');
        $id = Yii::$app->request->get('id');
        $uid = Yii::$app->user->identity->getId();
        $assessmentSession = AssessmentSession::getAssessmentSession(Yii::$app->user->identity->id, $id);
        $cid = Yii::$app->request->get('cid');
        $responseData = array();
        $course = Course::getById($cid);
        if ($course) {
            $itemOrders = unserialize($course->itemorder);
            if (count($itemOrders)) {
                foreach ($itemOrders as $key => $itemOrder) {
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
                                        break;
                                    case 'Calendar':
                                        $tempItem[$item->itemtype] = 1;
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
                    } else {
                        $item = Items::getById($itemOrder);
                        switch ($item->itemtype) {
                            case 'Assessment':
                                $assessment = Assessments::getByAssessmentId($item->typeid);
                                $tempAray[$item->itemtype] = $assessment;
                                array_push($responseData, $tempAray);
                                break;
                            case 'Calendar':
                                $tempAray[$item->itemtype] = 1;
                                array_push($responseData, $tempAray);
                                break;
                            case 'Forum':
                                $form = Forums::getById($item->typeid);
                                $tempAray[$item->itemtype] = $form;
                                array_push($responseData, $tempAray);
                                break;
                            case 'Wiki':
                                $wiki = Wiki::getById($item->typeid);
                                $tempAray[$item->itemtype] = $wiki;
                                array_push($responseData, $tempAray);
                                break;
                            case 'InlineText':
                                $inlineText = InlineText::getById($item->typeid);
                                $tempAray[$item->itemtype] = $inlineText;
                                array_push($responseData, $tempAray);
                                break;
                            case 'LinkedText':
                                $linkedText = Links::getById($item->typeid);
                                $tempAray[$item->itemtype] = $linkedText;
                                array_push($responseData, $tempAray);
                                break;
                        }
                    }
                }
            }

        } else {
// @TODO Need to add logic here
        }

        $course = Course::getById($cid);
        $student = Student::getByCId($cid);
        $this->includeCSS(['../css/fullcalendar.min.css', '../css/calendar.css', '../css/jquery-ui.css']);
        $this->includeJS(['../js/moment.min.js', '../js/fullcalendar.min.js']);
        $this->includeJS(['../js/student.js']);
        return $this->render('index', ['courseDetail' => $responseData, 'course' => $course, 'students' => $student,'assessmentSession' => $assessmentSession]);

    }


    public function actionShowAssessment()
    {
        $this->guestUserHandler();

        $id = Yii::$app->request->get('id');
        $courseId = Yii::$app->request->get('cid');
        $assessment = Assessments::getByAssessmentId($id);
        $assessmentSession = AssessmentSession::getAssessmentSession(Yii::$app->user->identity->id, $id);
        $questionRecords = Questions::getByAssessmentId($id);
        $questionSet = QuestionSet::getByQuesSetId($id);
        $course = Course::getById($courseId);
        $assessmentclosed = false;

        if ($assessment->avail == 0) {
            $assessmentclosed = true;
        }

        $this->saveAssessmentSession($assessment, $id);


        $this->includeCSS(['../css/mathtest.css', '../css/default.css', '../css/showAssessment.css']);
        $this->includeJS(['../js/timer.js']);
        return $this->render('ShowAssessment', ['cid'=> $course, 'assessments' => $assessment, 'questions' => $questionRecords, 'questionSets' => $questionSet,'assessmentSession' => $assessmentSession,'now' => time()]);
    }

    public function saveAssessmentSession($assessment, $id)
    {
        list($qlist, $seedlist, $reviewseedlist, $scorelist, $attemptslist, $lalist) = AppUtility::generateAssessmentData($assessment->itemorder, $assessment->shuffle, $assessment->id);

        $bestscorelist = $scorelist . ';' . $scorelist . ';' . $scorelist;
        $scorelist = $scorelist . ';' . $scorelist;
        $bestattemptslist = $attemptslist;
        $bestseedslist = $seedlist;
        $bestlalist = $lalist;
        $starttime = time();
        $deffeedbacktext = addslashes($assessment->deffeedbacktext);
        $ltisourcedid = '';

        $param['questions'] = $qlist;
        $param['seeds'] = $seedlist;
        $param['userid'] = $id;
        $param['assessmentid'] = $id;
        $param['attempts'] = $attemptslist;
        $param['lastanswers'] = $lalist;
        $param['reviewscores'] = $scorelist;
        $param['reviewseeds'] = $reviewseedlist;
        $param['bestscores'] = $bestscorelist;
        $param['scores'] = $scorelist;
        $param['bestattempts'] = $bestattemptslist;
        $param['bestseeds'] = $bestseedslist;
        $param['bestlastanswers'] = $bestlalist;
        $param['starttime'] = $starttime;
        $param['feedback'] = $deffeedbacktext;
        $param['lti_sourcedid'] = $ltisourcedid;

        $assessmentSession = new AssessmentSession();
        $assessmentSession->attributes = $param;
        $assessmentSession->save();
    }


    public function actionQuestionSet()
    {
        $tpQuestion = QuestionSet::getById('1');

        $this->renderWithData('question-set');
    }

    public function actionLatePass()
    {
        $this->guestUserHandler();
        $assessmentId = \Yii::$app->request->get('id');
        $courseId = \Yii::$app->request->get('cid');
        $studentId = \Yii::$app->user->identity->id;
        $exception = Exceptions::getByAssessmentId($assessmentId);

        $assessment = Assessments::getByAssessmentId($assessmentId);
        $student = Student::getByCourseId($courseId, $studentId);
        $course = Course::getById($courseId);

        $addtime = $course->latepasshrs * 60 * 60;
        $hasexception = true;
        $currentTime = AppUtility::parsedatetime(date('m/d/Y'), date('h:i a'));
        $usedlatepasses = round(($assessment->allowlate - $assessment->enddate)/($course->latepasshrs * 3600));
        $startdate = $assessment->startdate;
        $enddate = $assessment->enddate + $addtime;
        $wave = 0;

        $param['assessmentid'] = $assessmentId;
        $param['userid'] = $studentId;
        $param['startdate'] = $startdate;
        $param['enddate'] = $enddate;
        $param['waivereqscore'] = $wave;
        $param['islatepass'] = 1;

        if(count($exception))
        {

            if($assessment->allowlate != 0 && $assessment->enddate != 0 && $assessment->startdate != 0)
            {

                $hasException = true;
            }


            if ((($assessment->allowlate % 10) == 1 || ($assessment->allowlate % 10) - 1 > $usedlatepasses) && ($currentTime < $exception->enddate || ($assessment->allowlate > 10 && ($currentTime - $exception->enddate)< $course->latepasshrs * 3600)))
            {
                $latepass = $student->latepass;
                $student->latepass = $latepass - 1;
                $exception->enddate = $exception->enddate + $addtime;
                $exception->islatepass = $exception->islatepass + 1;
            }

            if($exception->islatepass != 0)
            {
                echo "<p>Un-use late-pass</p>";

                if ($currentTime > $assessment->enddate && $exception->enddate < $currentTime + $course->latepasshrs * 60 * 60)
                {

                    echo '<p>Too late to un-use this LatePass</p>';
                }
                else {
                    if ($currentTime < $assessment->enddate)
                    {
                        $exception->islatepass = $exception->islatepass - 1;
                    }
                    else {
                        //figure how many are unused
                        $n = floor(($exception->enddate - $currentTime)/($course->latepasshrs * 60 * 60));
                        $newend = $exception->enddate - $n * $course->latepasshrs * 60 * 60;
                        if ($exception->islatepass > $n)
                        {
                            $exception->islatepass = $exception->islatepass - $n;
                            $exception->enddate=$newend;
                        } else {
                            //dnt push anything into db.
                        }
                    }
                    echo "<p>Returning $n LatePass".($n > 1 ? "es":"")."</p>";
                    $student->latepass = $student->latepass + $n;
                }
            }
            else
            {
                echo '<p>Invalid</p>';
            }
            $exception->attributes = $param;
            $exception->save();
            $student->save();
        }
        $this->includeJS(['../js/latePass.js']);
        $this->redirect(AppUtility::getURLFromHome('course','course/index?id='.$assessmentId.'&cid='.$courseId));
    }

    public function actionQuestion()
    {
        $this->guestUserHandler();
        $questionId = $this->getParamVal('to');
        $pq = AppUtility::basicShowQuestions($questionId);


//        $this->redirect(AppUtility::getURLFromHome('course','course/show-assessment?id='.$questionId.'&q='.json_encode($pq)));


    }

} 