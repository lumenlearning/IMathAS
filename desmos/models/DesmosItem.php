<?php
/**
 * Repo OHM: Desmos Item Page
 */

namespace Desmos\Models;
use Course\Includes\ContentTracker;
use Course\Includes\CourseItem;
use DateTime;
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
    protected $itemtype = "DesmosItem";
    protected $itemname = "Desmos Interactive";
    protected $miniicon = "../ohm/img/desmos_tiny.php";
    protected $itemicon = "../ohm/img/desmos.php";
    protected $valid_fields = [
        'title','summary','startdate','enddate','avail','outcomes','tags','steps',
        'courseid','origin_itemid','itemid_chain','itemid_chain_size'
    ];
    protected $statusletter = "E";
    protected $showstats = true;
    protected $tagnames = array();
    protected $trackview = true;
    protected $trackedit = true;
    protected $steps = array();
    protected $origin_itemid;
    protected $itemid_chain;
    protected $itemid_chain_size;

    /**
     * Update course item data
     *
     * @param int   $typeid desmos_items.id
     * @param array $fields fields to update
     *
     * @return int
     */
    public function updateItemType(int $typeid, array $fields)
    {
        $steps = $fields['steps'];
        unset($fields['steps']);
        $query = "UPDATE desmos_items SET "
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
        if ($steps) {
            $this->typeid = $typeid;
            $this->modifySteps($steps);
        }
        return $stm->rowCount();
    }

    /**
     * Insert Item into desmos_items
     *
     * @param array $fields data to insert
     *
     * @return int
     */
    public function insertItem(array $fields)
    {
        $steps = $fields['steps'];
        unset($fields['steps']);
        $query = "INSERT INTO desmos_items ("
            . implode(',', array_keys($fields)) . ") "
            . " VALUES (:" . implode(',:', array_keys($fields)) . ")";
        $stm = $this->dbh->prepare($query);
        foreach ($fields as $key=>$value) {
            $stm->bindValue(":$key", $value);
        }
        $stm->execute();
        $this->typeid = $this->dbh->lastInsertId();
        if ($steps) {
            $this->modifySteps($steps);
        }
        return $this->typeid;
    }

    /**
     * Delete item from desmos_items table
     *
     * @return $this|CourseItem
     */
    public function deleteItem()
    {
        $stm = $this->dbh->prepare("DELETE FROM desmos_items WHERE id=:id");
        $stm->execute(array(':id'=>$this->typeid));
        DesmosSteps::deleteSteps($this->typeid);
        return $this;
    }

    /**
     * Find desmos_items item by id
     *
     * @param int $typeid    desmos_items.id
     * @param int $courseid  null | desmos_item.courseid
     *
     * @return $this|CourseItem
     */
    public function findItem(int $typeid, int $courseid = null)
    {
        $query = "SELECT * FROM desmos_items WHERE id=:typeid";
        if ($courseid != null) {
            $query .= " AND courseid=:courseid";
            $stm = $this->dbh->prepare($query);
            $stm->bindValue(":courseid", $courseid);
        } else {
            $stm = $this->dbh->prepare($query);
        }
        $stm->bindValue(":typeid", $typeid);

        $stm->execute();
        $item = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            return false;
        }

        $this->setItem($item);
        $this->steps = DesmosSteps::findSteps($this->typeid);
        $this->setStepOrder();
        $this->findTags();
        return $this;
    }

    /**
     * Find desmos_items items by id in ancestors
     *
     * @param int $typeid   desmos_items.id
     * @param int $courseid desmos_item.courseid
     * @param string $where desmos_item.ancestors
     *
     * @return array
     */
    public static function findAncestors(int $typeid, int $courseid, string $where = 'start')
    {
        $query = "SELECT id,title,itemid_chain as ancestors FROM desmos_items WHERE itemid_chain REGEXP :typeid AND courseid=:courseid";
        if ($where == 'all') {
            $typeid = MYSQL_LEFT_WRDBND . $typeid . MYSQL_RIGHT_WRDBND;
        } else {
            $typeid = '^([0-9]+:)?' . $typeid . MYSQL_RIGHT_WRDBND;
        }
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->bindValue(":courseid", $courseid);
        $stm->bindValue(":typeid", $typeid);

        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find desmos_items item by title
     *
     * @param string $title desmos_items.title
     *
     * @return $this|CourseItem
     */
    public function findItemByTitle(string $title, int $courseid = null)
    {
        $query = "SELECT * FROM desmos_items WHERE title=:title";
        if ($courseid != null) {
            $query .= " AND courseid=:courseid";
            $stm = $this->dbh->prepare($query);
            $stm->bindValue(":courseid", $courseid);
        } else {
            $stm = $this->dbh->prepare($query);
        }
        $stm->bindValue(":title", $title);

        $stm->execute();
        $item = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            return false;
        }

        $this->setItem($item);
        $this->steps = DesmosSteps::findSteps($this->typeid);
        $this->setStepOrder();
        $this->findTags();
        return $this;
    }
    
    /**
     * Find learning objectives item by id
     * currently using imas_libraries as a hack
     *
     * @return $this|CourseItem
     */
    public function findTags()
    {
        if (empty($this->tags)) {
            return $this;
        }
        $query = "SELECT name FROM imas_libraries WHERE id IN ($this->tags)";
        $stm = $this->dbh->prepare($query);
        $stm->execute();
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $this->tagnames[] = $row['name'];
        }
        $this->tagnames = array_unique($this->tagnames);
        return $this;
    }

    public static function deleteCourse(int $cid)
    {
        $stm = $GLOBALS['DBH']->prepare("DELETE FROM desmos_steps WHERE desmosid IN (SELECT id FROM desmos_items WHERE courseid=:id)");
        $stm->execute(array(':id'=>$cid));
        $stm = $GLOBALS['DBH']->prepare("DELETE FROM desmos_items WHERE courseid=:id");
        $stm->execute(array(':id'=>$cid));
    }

    /**
     * Get the total number of Desmos Items created by all schools.
     *
     * @param DateTime $startTimestamp Limit search by this starting timestamp.
     * @param DateTime $endTimestamp Limit search by this ending timestamp.
     * @param bool $authoredOnly Only count Desmos Items with itemid_chain_size of 1.
     * @param PDO|null $dbhOverride A database connection.
     * @return array An associative array.
     */
    public static function getTotalItemsCreatedByAllGroups(DateTime $startTimestamp,
                                                           DateTime $endTimestamp,
                                                           bool $authoredOnly = false,
                                                           PDO $dbhOverride = null
    ): array
    {
        $authoredOnlySql = '';
        if (true === $authoredOnly) {
            $authoredOnlySql = 'AND itemid_chain_size = 1';
        }

        $dbh = is_null($dbhOverride) ? $GLOBALS['DBH'] : $dbhOverride;
        $stm = $dbh->prepare("SELECT
                g.id AS group_id,
                g.name AS group_name,
                COUNT(di.id) AS total_items
            FROM imas_groups AS g
                JOIN imas_users AS u ON u.groupid = g.id
                JOIN imas_courses AS c ON c.ownerid = u.id
                JOIN desmos_items AS di ON di.courseid = c.id
            WHERE di.created_at >= :startTime AND di.created_at <= :endTime
                $authoredOnlySql
            GROUP BY g.id");
        $stm->execute([
            ':startTime' => self::getSqlTimestamp($startTimestamp),
            ':endTime' => self::getSqlTimestamp($endTimestamp)
        ]);

        $results = [];
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['group_id']]['group_name'] = $row['group_name'];
            $results[$row['group_id']]['total_items'] = $row['total_items'];
        }
        return $results;
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
     * @param string $value or use $this->summary
     *
     * @return $this|CourseItem
     */
    public function setSummary($value = '')
    {
        if ($value) {
            $this->summary = $value;
        } else {
            $this->summary = $this->summary;
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


    /**
     * Modify Desmos Steps
     *
     * @param array $steps must include title
     *
     * @return $this|CourseItem
     */
    public function modifySteps(array $steps)
    {
        //delete any steps that need to be removed
        //before adding new steps so new steps do not get deleted
        $item_steps = DesmosSteps::findSteps($this->typeid);
        $stepIds = array_map(
            function ($num) {
                return $num['id'];
            },
            $steps
        );
        foreach ($item_steps as $id) {
            if (!in_array($id['id'], $stepIds)) {
                //?do we want to just unassociate the desmosid?
                DesmosSteps::deleteStep($id['id']);
            }
        }
        //add steps
        foreach (array_keys($steps) as $key) {
            if (empty($steps[$key]['desmosid']) && isset($steps[$key]['id']) ) {
                //update step
                DesmosSteps::updateStep(
                    $steps[$key]['id'],
                    [
                        'title' => $steps[$key]['title'],
                        'text' => $steps[$key]['text']
                    ]
                );
            } else {
                //add step
                $steps[$key]['id'] = DesmosSteps::insertStep(
                    [
                        'desmosid' => $this->typeid,
                        'title' => $steps[$key]['title'],
                        'text' => $steps[$key]['text']
                    ]
                );
            }
        }
        $this->steps = $steps;
        $this->_updateStepOrder();
        return $this;
    }

    private function _updateStepOrder()
    {
        if (count($this->steps)>0) {
            foreach ($this->steps as $step) {
                $stepOrder[]=$step['id'];
            }
            $this->setStepOrder($stepOrder);
            $this->updateItemType($this->typeid, array('steporder' => implode(',', $stepOrder)));
        }
    }

    /**
     * Update the `steporder` column for this DesmosItem.
     *
     * This is most useful after copying a DesmosItem, because the new copy
     * will not preserve the original order of DesmosStep IDs.
     */
    public function updateStepOrderAfterCopy(array $stepOrder): void
    {
        $stm = $this->dbh->prepare('UPDATE desmos_items SET steporder = :stepOrder WHERE id = :id');
        $stm->execute([
            ':stepOrder' => implode(',', $stepOrder),
            ':id' => $this->typeid,
        ]);
    }

    /**
     * Set required parameter
     *
     * @param null $value or keep $this->setorder
     *
     * @return $this|CourseItem
     */
    public function setStepOrder($value = null)
    {
        if ($value === null) {
            $steporder = $this->steporder;
            if (!is_array($steporder)) {
                $steporder = explode(',', $steporder);
            }
            $keys = array_keys($this->steps);
            $removeOrder = array_filter($steporder, function($s) use ($keys) {
                if (in_array($s,$keys)) {
                    return true;
                }
            });
            $addOrder = array_filter($this->steps, function($step) use ($steporder) {
                if (!in_array($step['id'],$steporder)) {
                    return true;
                }
            });
            $value = array_merge($removeOrder, array_keys($addOrder));
            if ($value != $steporder) {
                $this->updateItemType($this->typeid, array('steporder' => implode(',', $value)));
            }
        }
        if (is_array($value)) {
            $this->steporder = $value;
        } else {
            $this->steporder = explode(',', $value);
        }
        return $this;
    }

    public function asArray($copy=false)
    {
        $data = [
            'title'=>$this->title,
            'summary'=>$this->summary,
            'startdate'=>$this->startdate,
            'enddate'=>$this->enddate,
            'avail'=>$this->avail,
            'outcomes'=>$this->outcomes,
            'tags'=>$this->tags,
            'type'=>ucwords($this->typename).'Item'
        ];
        if ($copy === false) {
            $data['id'] = $this->typeid;
            $data['courseid'] = $this->itemid;
        }
        foreach ($this->steps as $step) {
            if ($copy === true) {
                unset($step['id'], $step['desmosid']);
            }
            $data['steps'][]=$step;
        }
        return $data;
    }

    public function setSteps(array $steps): DesmosItem
    {
        $this->steps = $steps;
        $this->steporder = array_keys($steps);
        return $this;
    }

    /**
     * Set class fields from form data.
     *
     * This is used to re-populate forms when jumping between
     * edit and preview pages in:
     *   - /course/itemadd.php
     *   - /desmos/views/preview.php
     *
     * @param array $formData Associative array of form data.
     * @return DesmosItem
     */
    public function fromFormData(array $formData): DesmosItem
    {
        $this->title = $formData['title'];
        $this->setName($formData['title']);
        $this->setSummary($formData['summary']);
        // Build steps array
        $steps = [];
        foreach ($formData['step_title'] as $key => $title) {
            $steps[$key] = [
                "title" => $title,
                "text" => $formData['step_text'][$key],
            ];
            if (!empty($formData['step'][$key])) {
                $steps[$key]['id'] = $formData['step'][$key];
            }
        }
        $this->setSteps($steps);
        $this->setStartDate(strtotime($formData['sdate']));
        $this->setEndDate(strtotime($formData['edate']));
        return $this;
    }

    /*
     * Format a date for SQL.
     *
     * @param DateTime $dateTime A DateTime object.
     * @return string A date usable in a SQL query. ("0000-00-00 00:00:00")
     */
    protected static function getSqlTimestamp(DateTime $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:s');
    }
    
}
