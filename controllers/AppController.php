<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 13/4/15
 * Time: 8:32 PM
 */

namespace app\controllers;

use app\components\AppConstant;
use app\components\AppUtility;
use yii\web\Controller;
use Yii;

class AppController extends Controller
{

    public $enableCsrfValidation = false;


    function getBodyParams()
    {
        return $_POST;
    }

    function getRequestParams()
    {
        return $_REQUEST;
    }

    function getParam($key)
    {
        return $_REQUEST[$key];
    }

    function setSuccessFlash($message)
    {
        $this->_setFlash('success', $message);
    }

    function setErrorFlash($message)
    {
        $this->_setFlash('danger', $message);
    }

    function setWarningFlash($message)
    {
        $this->_setFlash('warning', $message);
    }

    private function _setFlash($type, $message)
    {
        \Yii::$app->session->setFlash($type, $message);
    }

    function unauthorizedAccessHandler()
    {
        if (!$this->isGuestUser()) {
            return $this->goHome();
            exit;
        }
    }

    function isGuestUser(){
        return \Yii::$app->user->isGuest;
    }

    function guestUserHandler($isAjaxCall = false){
        if(self::isGuestUser())
        {
            if($isAjaxCall)
            {
//                return self::terminateResponse(AppConstant::UNAUTHORIZED_ACCESS);
                return false;
            }else{
                return $this->redirect(AppUtility::getHomeURL().'site/login');
            }
        }
    }

    function getUserId(){
        return $this->getAuthenticatedUser()->getId();
    }

    function getUserTimezone(){
        return AppConstant::DEFAULT_TIME_ZONE;
    }

    function includeCSS($cssFileArray){
        $this->includeAssets($cssFileArray, AppConstant::ASSET_TYPE_CSS);
    }

    function includeJS($jsFileArray){
        $this->includeAssets($jsFileArray, AppConstant::ASSET_TYPE_JS);
    }


    function includeAssets($fileArray, $assetType){
        $cnt = count($fileArray);
        $assetUrl = AppUtility::getAssetURL();
        for($i = 0; $i < $cnt; $i++){
            $fileURL = $assetUrl . $assetType . "/" . $fileArray[$i];
            if($assetType == AppConstant::ASSET_TYPE_CSS){
                $this->getView()->registerCssFile($fileURL."?ver=".AppConstant::VERSION_NUMBER);
            }else{
                $this->getView()->registerJsFile($fileURL."?ver=".AppConstant::VERSION_NUMBER);
            }
        }
    }


    public function renderWithData($viewName, $data = array()){
        return $this->render($viewName, $data);
    }

    function getAuthenticatedUser(){
        return \Yii::$app->user->identity;
    }

    public function isPost(){
        return Yii::$app->request->getMethod() == 'POST';
    }

    public function getPostData()
    {
        return Yii::$app->request->post();
    }

    public function successResponse($data = '')
    {
        return json_encode(array('status' =>AppConstant::RETURN_SUCCESS, 'data' => $data));
    }

    public function terminateResponse($msg)
    {
        return json_encode(array('status' => AppConstant::RETURN_ERROR, 'message' => $msg));
    }

    public function getParamVal($key){
        return Yii::$app->request->get($key);
    }

    public function getSanitizedValue($key, $defaultVal = '')
    {
        isset($key) ? $key : $defaultVal;
    }

    public function isPostMethod(){
        return Yii::$app->request->post();
    }

    public function setReferrer()
    {
        $referrer = Yii::$app->request->getReferrer();
        if ($referrer) {
            Yii::$app->session->set('referrer', $referrer);
        }
    }

}