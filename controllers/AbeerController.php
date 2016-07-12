<?php

namespace app\controllers;
use app\controllers\AppController;
use app\models\Questions;
use app\models\QuestionSet;
use app\components\displayQuestion;



class AbeerController extends AppController
{  
  public function actionIndex()
  {    
    $params = $this->getRequestParams();
    var_dump($params);
    $qid = $params['qid'];
     if($qid){
       $question = new QuestionSet();
       $rq = $question::getByQuesSetId($qid);
      //  var_dump($rq);
       $author  = $rq['author'];
       $answer  = $rq['control'];
       $QuestionSet  = $rq['qtext'];
      //  displayq($qn, $qid, $seeds[$qn], 2, false, $attempts[$qn], false, false, false, $colors);
       return  $QuestionSet;
     }
  }
}