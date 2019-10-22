<?php
/**
 * Repo OHM: Desmos Item Page
 */

namespace Desmos\Models;
use Course\Includes\CourseItem;
use PDO;

/**
 * Class DesmosItem
 *
 * @package Desmos\Models
 * @author  Alena Holligan <alena@lumenlearning.com>
 */
class DesmosItem extends CourseItem
{
    protected $typename = "desmos";
    //imas_items.itemtype with spaces
    protected $display_name = "Desmos Interactive";
    protected $miniicon = "../ohm/img/desmos_tiny.php";
    protected $itemicon = "../ohm/img/desmos.php";
    protected $valid_fields = [
        'title','summary','startdate','enddate','avail','outcomes','courseid'
    ];
    protected $statusletter = "E";
    protected $showstats = false;

    /**
     * Update course item data
     *
     * @param int   $typeid desmos_interactives.id
     * @param array $fields fields to update
     *
     * @return int
     */
    public function updateItem(int $typeid, array $fields)
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
        $stm->bindValue($key, $typeid);
        $stm->execute();
        return $stm->rowCount();
    }

    /**
     * Duplicate Item
     *
     * @param int    $typeid    desmos_interactive.id
     * @param string $append    add to title
     * @param bool   $sethidden set avail
     *
     * @return $this|CourseItem
     */
    public function copyItem(int $typeid, $append = " (Copy)", $sethidden = false)
    {
        $field_values = $this->valid_fields;
        if ($sethidden) {
            $key = array_search("avail", $field_values);
            $field_values[$key] = 0;
        }
        $key = array_search("title", $field_values);
        $field_values[$key] = "CONCAT($field_values[$key], '$append')";
        $query = "INSERT INTO desmos_interactives ("
            . implode(', ', $this->valid_fields) . ") "
            . " SELECT " . implode(', ', $field_values)
            . " FROM desmos_interactives"
            . " WHERE id=:id";
        $stm = $this->dbh->prepare($query);
        $stm->bindValue(":id", $typeid);
        $stm->execute();
        $newtypeid = $this->dbh->lastInsertId();
        return $this->addCourseItems($newtypeid);
    }

    /**
     * Insert Item into desmos_interactives
     *
     * @param array $fields data to insert
     *
     * @return int
     */
    public function insertItem(array $fields)
    {
        $query = "INSERT INTO desmos_interactives ("
            . implode(',', array_keys($fields)) . ") "
            . " VALUES (:" . implode(',:', array_keys($fields)) . ")";
        $stm = $this->dbh->prepare($query);
        foreach ($fields as $key=>$value) {
            $stm->bindValue(":$key", $value);
        }
        $stm->execute();
        return $this->dbh->lastInsertId();
    }

    /**
     * Delete item from desmos_interactives table
     *
     * @return $this|CourseItem
     */
    public function deleteItem()
    {
        $stm = $this->dbh->prepare("DELETE FROM desmos_interactives WHERE id=:id");
        $stm->execute(array(':id'=>$this->typeid));
        return $this;
    }

    /**
     * Find desmos_interactives item by id
     *
     * @param int $typeid desmos_interactives.id
     *
     * @return DesmosItem $this
     */
    public function findItem(int $typeid)
    {
        $query = "SELECT * FROM desmos_interactives WHERE id=:id";
        $stm = $this->dbh->prepare($query);
        $stm->execute(array(':id' => $typeid));
        $item = $stm->fetch(PDO::FETCH_ASSOC);

        $this->setItem($item);
        return $this;
    }

    /**
     * Set required parameter
     *
     * @param null $value or use $this->title
     *
     * @return $this|CourseItem
     */
    public function setName($value = null)
    {
        if ($value) {
            $this->name = $value;
        } else {
            $this->name = $this->title;
        }
        return $this;
    }

    /**
     * Set required parameter
     *
     * @param null $value timstamp()
     *
     * @return $this|CourseItem
     */
    public function setStartDate($value = null)
    {
        if ($value) {
            $this->startdate = $value;
        }
        return $this;
    }

    /**
     * Set required parameter
     *
     * @param null $value timstamp()
     *
     * @return $this|CourseItem
     */
    public function setEndDate($value = null)
    {
        if ($value) {
            $this->enddate = $value;
        }
        return $this;
    }

    /**
     * Set required parameter
     *
     * @param null $value timestamp()
     *
     * @return $this|CourseItem
     */
    public function setAvail($value = null)
    {
        if ($value) {
            $this->avail = $value;
        }
        return $this;
    }
}