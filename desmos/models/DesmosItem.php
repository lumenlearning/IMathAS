<?php
/**
 * Repo OHM: Desmos Item Page
 */

namespace Desmos\Models;
use Course\Includes\CourseItem;

/**
 * Class DesmosItem
 *
 * @package Desmos\Models
 * @author  Alena Holligan <alena@lumenlearning.com>
 */
class DesmosItem extends CourseItem
{
    protected $typename = "desmos";
    protected $display_name = "Desmos Interactive";
    protected $miniicon = "../ohm/img/desmos_tiny.php";
    protected $itemicon = "../ohm/img/desmos.php";
    protected $valid_fields = [
        'title','summary','startdate','enddate','avail','outcomes','courseid'
    ];
    protected $statusletter = "D";
    protected $showstats = false;

    public function updateItem(int $itemid, array $fields)
    {
        $query = "UPDATE desmos_interactives SET "
            . implode('=?, ', array_keys($fields))
            . "=? WHERE id=?";
        $stm = $this->dbh->prepare($query);
        $key = 1;
        foreach ($fields as $value) {
            $stm->bindValue($key, $value);
            $key++;
        }
        $stm->bindValue($key, $itemid);
        $stm->execute();
        return $stm->rowCount();
    }
    public function addItem(array $fields)
    {
        $invalid = array_diff(array_keys($fields), $this->valid_fields);
        if ($invalid) {
            echo __CLASS__ . " invalid fields: " . implode(', ', $invalid);
            return false;
        }
        $query = "INSERT INTO desmos_interactives ("
            . implode(',', array_keys($fields)) . ") "
            . " VALUES (:" . implode(',:', array_keys($fields)) . ")";
        $stm = $this->dbh->prepare($query);
        foreach ($fields as $key=>&$value) {
            $stm->bindValue(":$key", $fields[$key]);
        }
        $stm->execute();
        $newtextid = $this->dbh->lastInsertId();
        $itemid = $this->addCourseItems(
            'DesmosInteractive',
            $newtextid
        );
        return $newtextid;
    }
    public function getItem(int $itemid)
    {
        $query = "SELECT * FROM desmos_interactives WHERE id=:id";
        $stm = $this->dbh->prepare($query);
        $stm->execute(array(':id' => $itemid));
        $item = $stm->fetch(\PDO::FETCH_ASSOC);

        $this->setItem($item);
        return $this;
    }
    public function setName($value = null)
    {
        if ($value) {
            $this->name = $value;
        } else {
            $this->name = $this->title;
        }
        return $this;
    }
    public function setStartDate($value = null)
    {
        if ($value) {
            $this->startdate = $value;
        }
        return $this;
    }
    public function setEndDate($value = null)
    {
        if ($value) {
            $this->enddate = $value;
        }
        return $this;
    }
    public function setAvail($value = null)
    {
        if ($value) {
            $this->avail = $value;
        }
        return $this;
    }
}