<?php

namespace app\models;

use Yii;
use yii\base\Model;
class StudentRegisterForm extends Model
{
    public $username;
    public $FirstName;
    public $LastName;
    public $password;
    public $rePassword;
    public $email;
    public $NotifyMeByEmailWhenIReceiveANewMessage= true;
    public $courseID;
    public $EnrollmentKey;
    public $isUserNameExist;
    public $contactNo;
    public $uploadFile;

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [

            [['username', 'password','email'], 'required'],
            ['username', 'match' ,'pattern'=>'/^[A-Za-z0-9_]+$/u','message'=> 'Username can contain only alphanumeric characters and hyphens(-).'],
            ['rePassword', 'compare', 'compareAttribute'=>'password'],
            [['FirstName', 'LastName'], 'string'],
            ['email','email'],
            ['NotifyMeByEmailWhenIReceiveANewMessage', 'boolean'],
            [['courseID', 'EnrollmentKey'], 'string'],
        ];

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'SID' => 'Enter username',
            'password' => 'Choose Password',
            'rePassword'=>'confirm Password',
            'FirstName' => 'FirstName',
            'LastName' => 'LastName',
            'email' => 'Email',
            'CourseId'=>'Course Id',
            'EnrollmentKey'=>'Enrollment Key',
            'NotifyMeByEmailWhenIReceiveANewMessage'=>'$Notify me by email when I receive a new message'
        ];
    }

    public static function Submit()
    {
        $params =  $_POST;
        require("../components/password.php");
        $params = $params['StudentRegisterForm'];
        $params['SID'] = $params['username'];
        $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        $params['hideonpostswidget'] = '0';

        $user = new User();
        $user->attributes = $params;
        $user->save();
    }
}
