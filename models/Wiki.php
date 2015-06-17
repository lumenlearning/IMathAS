<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 29/4/15
 * Time: 4:46 PM
 */

namespace app\models;


use app\models\_base\BaseImasWikis;

class Wiki extends BaseImasWikis
{
    public static function getByCourseId($courseId)
    {
        return static::findAll(['courseid' => $courseId]);
    }

    public static function getById($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function getAllData($wikiId)
    {
        $query =\Yii::$app->db->createCommand("SELECT name,startdate,enddate,editbydate,avail FROM imas_wikis WHERE id='$wikiId'")->queryAll();
        return $query;
    }
} 