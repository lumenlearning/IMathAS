<?php
namespace Desmos\Models;
use OHM\Includes\CourseItems;
class DesmosInteractive extends CourseItems
{
    protected $typename = "desmos";
    protected $table = "desmos_interactive";
    protected $valid_fields = [
        'title','summary','startdate','enddate','avail','outcomes','courseid'
    ];
    protected $statusletter = "D";
    protected $showstats = false;
    protected $viewUrl = "../desmos/viewdesmos.php";
    protected $addUrl = "../desmos/adddesmos.php";
    protected $deleteUrl = "../desmos/deletedesmos.php";
    public function updateItem(int $itemid, array $fields) {
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
    public function addItem($fields) {
        $invalid = array_diff(array_keys($fields), $this->valid_fields);
        if ($invalid) {
            echo __CLASS__ . " invalid fields: " . implode(', ', $invalid);
            return false;
        }
        $query = "INSERT INTO desmos_interactives (" . implode(',',array_keys($fields)) . ") "
            . " VALUES (:" . implode(',:',array_keys($fields)) . ")";
        $stm = $this->dbh->prepare($query);
        foreach ($fields as $key=>&$value) {
            $stm->bindValue(":$key", $fields[$key]);
        }
        $stm->execute();
        $newtextid = $this->dbh->lastInsertId();
        $itemid = $this->addCourseItems($fields['courseid'], 'DesmosInteractive', $newtextid);
        echo $this->setItemOrder($fields['courseid'], $itemid);
        return $newtextid;
    }
    public function getItem($desmosid = null) {
        $query = "SELECT * FROM desmos_interactives WHERE id=:id";
        $stm = $this->dbh->prepare($query);
        $stm->execute(array(':id' => $desmosid));
        return $stm->fetch(\PDO::FETCH_ASSOC);
    }
    public function getAllItems($bind = null) {
        $query = "SELECT * FROM desmos_interactives";
        if ($bind) {
            $query .= " WHERE courseid=:courseid";
            $bind[':courseid'] = $bind;
        }
        $stm = $this->dbh->prepare($query);
        $stm->execute($bind);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }
}