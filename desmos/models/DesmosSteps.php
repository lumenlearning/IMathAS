<?php
/**
 * Repo OHM: Desmos Steps
 */

namespace Desmos\Models;
use PDO;

/**
 * Class DesmosSteps
 *
 * @package Desmos\Models
 * @author  Alena Holligan <alena@lumenlearning.com>
 */
class DesmosSteps
{

    /**
     * Find desmos steps
     *
     * @param int $desmosid desmos_items.id which is also item->typeid
     *
     * @return array
     */
    public static function findSteps(int $desmosid)
    {
        $query = "SELECT * FROM desmos_steps WHERE desmosid = :desmosid";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->bindValue(':desmosid', $desmosid);
        $stm->execute();
        $steps = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($steps as $step) {
            $stepOrder[$step['id']] = $step;
        }
        $stepOrder ??= [];
        return $stepOrder;
    }

    /**
     * Find desmos steps
     *
     * @param int   $stepid desmos_steps.id
     * @param array $fields data to update
     *
     * @return array
     */
    public static function updateStep(int $stepid, array $fields)
    {
        $query = "UPDATE desmos_steps SET "
            . implode('=?, ', array_keys($fields))
            . "=? WHERE id=?";
        $stm = $GLOBALS['DBH']->prepare($query);
        $key = 1;
        foreach ($fields as $value) {
            $stm->bindValue($key, $value);
            $key++;
        }
        $stm->bindValue($key, $stepid);
        $stm->execute();
        return $stm->rowCount();
    }

    /**
     * Insert Item into desmos_steps
     *
     * @param array $fields data to insert
     *
     * @return int
     */
    public static function insertStep(array $fields)
    {
        $query = "INSERT INTO desmos_steps ("
            . implode(',', array_keys($fields)) . ") "
            . " VALUES (:" . implode(',:', array_keys($fields)) . ")";
        $stm = $GLOBALS['DBH']->prepare($query);
        foreach ($fields as $key=>$value) {
            $stm->bindValue(":$key", $value);
        }
        $stm->execute();
        return $GLOBALS['DBH']->lastInsertId();
    }

    /**
     * Delete individual step from desmos_steps table by unique id
     *
     * @param int $id unique step identifier
     *
     * @return int number of rows affected
     */
    public static function deleteStep(int $id)
    {
        $stm = $GLOBALS['DBH']->prepare("DELETE FROM desmos_steps WHERE id=:id");
        $stm->execute(array(':id'=>$id));
        return $stm->rowCount();
    }

    /**
     * Delete steps from desmos_steps table by desmosid
     *
     * @param int $id desmos_items.id same as item typeid
     *
     * @return int number of rows affected
     */
    public static function deleteSteps(int $id)
    {
        $stm = $GLOBALS['DBH']->prepare("DELETE FROM desmos_steps WHERE desmosid=:id");
        $stm->execute(array(':id'=>$id));
        return $stm->rowCount();
    }
}