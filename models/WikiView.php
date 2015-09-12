<?php
/**
 * Created by PhpStorm.
 * User: tudip
 * Date: 22/6/15
 * Time: 8:20 PM
 */

namespace app\models;


use app\models\_base\BaseImasWikiViews;

class WikiView extends BaseImasWikiViews
{
    public static function getWikiViewTotalData($wikiId, $userId)
    {
        return WikiView::findAll(['wikiid' => $wikiId, 'userid' => $userId]);
    }

    public static function deleteByWikiId($wikiId){
        $wikiViewData = WikiRevision::findAll(['wikiid' => $wikiId]);
        if($wikiViewData){
            foreach($wikiViewData as $singleData){
                $singleData->delete();
            }
        }
    }
    public static function deleteWikiRelatedToCourse($wikis, $toUnEnroll)
    {
        $query = WikiView::find()->where(['IN', 'wikiid', $wikis])->andWhere(['IN', 'userid', $toUnEnroll])->all();
        if($query){
            foreach($query as $object){
                $object->delete();
            }
        }
    }

    public static function deleteWikiId($wid)
    {
        $query = WikiView::find()->where(['wikiid'=> $wid])->one();
        if($query)
        {
            $query->delete();
        }
    }
} 