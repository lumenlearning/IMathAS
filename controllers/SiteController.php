<?php

namespace app\controllers;

use app\components\AppConstant;
use app\models\_base\BaseImasSessions;
use app\models\ChangeUserInfoForm;
use app\models\CourseSettingForm;
use app\models\DiagnosticForm;
use app\models\ForgetPasswordForm;
use app\models\ForgetUsernameForm;
use app\models\LoginForm;
use app\models\RegistrationForm;
use app\models\StudentEnrollCourseForm;
use app\models\StudentRegisterForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\ContactForm;
use app\components\AppUtility;
use app\models\ChangePasswordForm;
use app\models\MessageForm;

class SiteController extends AppController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        if(Yii::$app->user->isGuest)
            return $this->render('index');
        else
            $this->redirect('site/dashboard');

    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (AppUtility::isOldSiteSupported()) {
                //Set session data
                ini_set('session.gc_maxlifetime', AppConstant::MAX_SESSION_TIME);
                ini_set('auto_detect_line_endings', true);
                $sessionid = session_id();

                $session_data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
                $session_data['ip'] = $_SERVER['REMOTE_ADDR'];
                $session_data['secsalt'] = AppUtility::generateRandomString();

                $session_data['mathdisp'] = 1;
                $session_data['graphdisp'] = 1;
                $session_data['useed'] = AppUtility::checkEditOrOk();
                $enc = base64_encode(serialize($session_data));

                $session = new BaseImasSessions();
                if (isset($_POST['tzname']) && strpos(basename($_SERVER['PHP_SELF']), 'upgrade.php') === false) {
                    //$query = "INSERT INTO imas_sessions (sessionid,userid,time,tzoffset,tzname,sessiondata) VALUES ('$sessionid','$userid',$now,'{$_POST['tzoffset']}','{$_POST['tzname']}','$enc')";

                } else {
                    //$query = "INSERT INTO imas_sessions (sessionid,userid,time,tzoffset,sessiondata) VALUES ('$sessionid','$userid',$now,'{$_POST['tzoffset']}','$enc')";
                    $session->sessionid = $sessionid;
                    $session->userid = Yii::$app->getUser()->id;
                    $session->time = time();
                    $session->tzoffset = '-330';
                    $session->tzname = "Asia/calcutta";
                    $session->sessiondata = $enc;
                }
                $session->save();
                return Yii::$app->getResponse()->redirect(Yii::$app->homeUrl.'IMathAS');
            }
            $this->redirect('dashboard');
        } else {
            $challenge = base64_encode(microtime() . rand(0, 9999));
            $this->getView()->registerCssFile('../css/login.css');
            $this->getView()->registerJsFile('../js/jstz_min.js');
            $this->getView()->registerJsFile('../js/login.js');
            return $this->render('login', [
                'model' => $model, 'challenge' => $challenge,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @return string
     * Controller for about us page
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * @return string
     * Instructor registration controller
     */
    public function actionRegistration()
    {
        $model = new RegistrationForm();
        if ($model->load(Yii::$app->request->post())) {
            require("../components/password.php");
            $params = Yii::$app->request->getBodyParams();
            $params = $params['RegistrationForm'];
            $params['SID'] = $params['username'];
            $params['hideonpostswidget'] = AppConstant::ZERO_VALUE;
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);

            $user = new User();
            $user->attributes = $params;
            $user->save();
        }
        return $this->render('registration', [
            'model' => $model,
        ]);
    }

    public function actionStudentRegister()
    {
        $model = new StudentRegisterForm();
        if ($model->load(Yii::$app->request->post())) {
            StudentRegisterForm::Submit();
        }
        return $this->render('studentRegister', ['model' => $model,]);
    }

    /**
     * @return string
     * Controller for general work progress page
     */
    public function actionWorkInProgress()
    {
        return $this->render('progress');
    }

    public function actionDashboard()
    {
        $user = Yii::$app->user->identity;
        if ($user) {
            $this->getView()->registerCssFile('../css/dashboard.css');
            $this->getView()->registerJsFile('../js/dashboard.js');
            $this->getView()->registerJsFile('../js/ASCIIsvg_min.js?ver=012314');
            $this->getView()->registerJs('var usingASCIISvg = true;');
            $this->getView()->registerJsFile('../js/tablesorter.js');
            if ($user->rights === AppConstant::ADMIN_RIGHT)
                return $this->render('adminDashboard', ['user' => $user]);
            elseif ($user->rights === AppConstant::GUEST_RIGHT)
                return $this->render('adminDashboard', ['user' => $user]);
            elseif ($user->rights === AppConstant::STUDENT_RIGHT)
                return $this->render('studentDashboard', ['user' => $user]);
            elseif ($user->rights === AppConstant::TEACHER_RIGHT)
                return $this->render('instructorDashboard', ['user' => $user]);
            elseif ($user->rights === AppConstant::GROUP_ADMIN_RIGHT)
                return $this->render('adminDashboard', ['user' => $user]);
        }
        Yii::$app->session->setFlash('error', AppConstant::LOGIN_FIRST);
        return $this->redirect('login');
    }

    public function actionChangePassword()
    {
        if( Yii::$app->user->identity)
        {
            $model = new ChangePasswordForm();
            if ($model->load(Yii::$app->request->post()))
            {
                $param = Yii::$app->request->getBodyParams();
                $oldPass=$param['ChangePasswordForm']['oldPassword'];
                $newPass=$param['ChangePasswordForm']['newPassword'];
                require("../components/Password.php");
                if(password_verify($oldPass,Yii::$app->user->identity->password))
                {
                    $user = User::findByUsername(Yii::$app->user->identity->SID);
                    $password = password_hash($newPass, PASSWORD_DEFAULT);
                    $user->password = $password;
                    $user->save();
                }
                $this->redirect(array('site/dashboard'));

            }
            return $this->render('changePassword',['model' => $model]);
        }
       return $this->redirect('login');
    }

    public function actionCourseSetting()
    {
        $model = new CourseSettingForm();
        return $this->render('courseSetting',['model'=>$model]);
    }
    public function actionDiagnostic()
    {
        $model = new DiagnosticForm();
        return $this->render('diagnostic',['model'=>$model]);
    }


    public function actionChangeUserInfo()
    {
        if( Yii::$app->user->identity)
        {
            $tzname = "Asia/Kolkata";

            $user = Yii::$app->user->identity;
            $model = new ChangeUserInfoForm();
            if($model->load(Yii::$app->request->post()))
            {
                $params = Yii::$app->request->getBodyParams();
                $params = $params['ChangeUserInfoForm'];
                User::saveUserRecord($params);
            }
            return $this->render('changeUserinfo',['model'=> $model, 'user' => isset($user->attributes)?$user->attributes:null,'tzname' => $tzname]);
        }
        return $this->redirect('login');
    }

    public function actionMessages()
    {
        if( Yii::$app->user->identity)
        {
            $model = new MessageForm();
            return $this->render('messages', ['model' => $model,]);
        }
       return $this->redirect('login');
    }

    public function actionStudentEnrollCourse()
    {
        if( Yii::$app->user->identity)
        {
            $model = new StudentEnrollCourseForm();
            return $this->render('studentEnrollCourse', ['model' => $model,]);
        }
        return $this->redirect('login');
    }

    public function actionForgetPassword()
    {
        $model = new ForgetPasswordForm();
        if ($model->load(Yii::$app->request->post()))
        {
            $param = Yii::$app->request->getBodyParams();
            $username = $param['ForgetPasswordForm']['username'];

            $user = User::findByUsername($username);
            $code = AppUtility::generateRandomString();
            $user->remoteaccess= $code;
            $user->save();

            $toEmail = $user->email;
            $id = $user->id;

            $message  = "<h4>This is an automated message from OpenMath.  Do not respond to this email</h4>\r\n";
            $message .= "<p>Your username was entered in the Reset Password page.  If you did not do this, you may ignore and delete this message. ";
            $message .= "If you did request a password reset, click the link below, or copy and paste it into your browser's address bar.  You ";
            $message .= "will then be prompted to choose a new password.</p>";
            $message .= "<a href=\"" .AppUtility::urlMode(). $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/actions.php?action=resetpw&id=$id&code=$code\">";
            $message .= AppUtility::urlMode() . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/actions.php?action=resetpw&id=$id&code=$code</a>\r\n";

            $email = Yii::$app->mailer->compose();
            $email->setTo($toEmail)
                ->setSubject(AppConstant::FORGOT_PASS_MAIL_SUBJECT)
                ->setHtmlBody($message)
                ->send();
        }

        return $this->render('forgetPassword',['model'=> $model,]);
    }

    public function actionForgetUsername()
    {
        $model = new ForgetUsernameForm();
        if ($model->load(Yii::$app->request->post()))
        {
            $param = Yii::$app->request->getBodyParams();
            $toEmail = $param['ForgetUsernameForm']['email'];

            $user = User::findByEmail($toEmail);
            if($user)
            {
                $message  = "<h4>This is an automated message from OpenMath.  Do not respond to this email</h4>";
                $message .= "<p>Your email was entered in the Username Lookup page on OpenMath.  If you did not do this, you may ignore and delete this message.  ";
                $message .= "All usernames using this email address are listed below</p><p>";
                $message .= "Username: <b>".$user->SID." </b> <br/>.";

                $email = Yii::$app->mailer->compose();
                $email->setTo($toEmail)
                    ->setSubject(AppConstant::FORGOT_USER_MAIL_SUBJECT)
                    ->setHtmlBody($message)
                    ->send();
            }
            else
            {
                Yii::$app->session->setFlash('error', AppConstant::INVALID_EMAIL);
            }
        }
        return $this->render('forgetUsername',['model'=> $model,]);
    }
}