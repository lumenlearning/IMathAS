<?php
/**
 * Repo iMathAS: Course Item Object
 */

namespace Course\Includes;
use PDO;
use Sanitize;

/**
 * Class CourseItem
 *
 * @package Course\Includes
 * @author  Alena Holligan <alena@lumenlearning.com>
 */
abstract class CourseItem
{
    // global properties eventually using dependency injection
    /* @var PDO  */
    protected $dbh;
    protected $imasroot;
    protected $itemicon;
    protected $miniicon;

    // constructor properties
    protected $courseid;
    protected $block;
    protected $totb;

    // required item specific properties
    protected $itemid;
    protected $typeid;
    protected $name;
    protected $startdate;
    protected $enddate;
    protected $avail;

    /**
     * CourseItem constructor.
     *
     * @param int    $courseid course to which this item is tied
     * @param int    $block    parental hierarchy of course items
     * @param string $totb     "to Top or Bottom" of course"
     */
    public function __construct($courseid = null, $block = 0, $totb = 'b')
    {
        $this->courseid = $courseid;
        $this->block = $block;
        $this->totb = $totb;
        if (isset($GLOBALS['DBH'])) {
            $this->dbh = $GLOBALS['DBH'];
        }
        if (isset($GLOBALS['imasroot'])) {
            $this->imasroot = $GLOBALS['imasroot'];
        }
        if (isset($GLOBALS['CFG']['CPS']['itemicons'][$this->typename])) {
            $this->itemicon = $GLOBALS['CFG']['CPS']['itemicons'][$this->typename];
        }
        if (isset($GLOBALS['CFG']['CPS']['miniicons'])) {
            $this->miniicon = $GLOBALS['CFG']['CPS']['miniicons'][$this->typename];
        }
        //if ($this->trackview === true || $this->trackedit === true)
    }

    public function track($track_type, $info = '')
    {
        if ($track_type === 'view') {
            if ($this->trackview === true) {
                ContentTracker::addTracking($this, $this->typename . 'view', $info);
            }
        } else if ($this->trackedit === true) {
            ContentTracker::addTracking($this, $this->typename . $track_type, $info);
        }
    }

    /**
     * Add item and related data
     *
     * @param array $fields data for item
     *
     * @return $this|bool
     */
    public function addItem(array $fields)
    {
        $invalid = array_diff(array_keys($fields), $this->valid_fields);
        if ($invalid) {
            echo __CLASS__ . " invalid fields: " . implode(', ', $invalid);
            return false;
        }
        $newtypeid = $this->insertItem($fields);
        if ($newtypeid) {
            $fields['id'] = $newtypeid;
            $this->setItem($fields);
            $this->addCourseItems($newtypeid);
            $this->track('add');
            $this->setItemOrder();
        }
        return $this;
    }
    /**
     * Update course item data
     *
     * @param int   $typeid original desmos_items.id
     * @param array $fields fields to update
     *
     * @return CourseItem $this
     */
    public function updateItem(int $typeid, array $fields)
    {
        if ( $this->updateItemType($typeid, $fields) ) {
            $this->track('edit');
        }
        return $this;
    }

    /**
     * Add Course Item to database
     * copyoneitem.php adds the imas_courses.itemorder
     *
     * @param int    $typeid    linked to the items table
     * @param string $append    Individual Item Types
     * @param bool   $sethidden Set avail=0
     *
     * @return CourseItem $this
     */
    public function copyItem(int $typeid, $append = " (Copy)", $sethidden = false)
    {
        $this->findItem($typeid);
        if ($sethidden == true) {
            $this->avail = 0;
        }
        $fields = array();
        foreach ($this->valid_fields as $key) {
            $fields[$key] = $this->$key;
            if ($key == 'title' || $key == 'name') {
                $fields[$key] = $this->$key.$append;
            }
        }
        $newtypeid = $this->insertItem($fields);
        if ($newtypeid) {
            $fields['id'] = $newtypeid;
            $this->setItem($fields);
            $this->addCourseItems($newtypeid);
            $this->track('copy', $typeid);
        }
        return $this;
    }

    /**
     * Add Course Item to database
     *
     * @param int $typeid linked to the items table
     *
     * @return CourseItem $this
     */
    public function addCourseItems($typeid)
    {
        $query = "INSERT INTO imas_items (courseid,itemtype,typeid) VALUES "
            . "(:courseid, :itemtype, :typeid)";
        $stm = $this->dbh->prepare($query);
        $stm->execute(
            array(
                ':courseid'=>$this->courseid,
                ':itemtype'=>str_replace(' ', '', $this->display_name),
                ':typeid'=>$typeid
            )
        );
        $this->itemid = $this->dbh->lastInsertId();
        return $this;
    }

    /**
     * ID from imas_items table
     *
     * @return CourseItem $this
     */
    public function findCourseItemId()
    {
        $query = "SELECT id FROM imas_items "
            . " WHERE typeid=:typeid AND itemtype=:itemtype AND courseid=:courseid";
        $stm = $this->dbh->prepare($query);
        $stm->bindValue(':typeid', $this->typeid);
        $stm->bindValue(':itemtype', str_replace(' ', '', $this->display_name));
        $stm->bindValue(':courseid', $this->courseid);
        $stm->execute();
        if ($stm->rowCount()>0) {
            $this->itemid = $stm->fetchColumn(0);
        }
        return $this;
    }

    /**
     * Delete and Course Item and it's associated data
     *
     * @param int $typeid id from specific item type table
     *
     * @return CourseItem $this
     */
    public function deleteItemData(int $typeid)
    {
        $this->findItem($typeid);
        $this->dbh->beginTransaction();
        $this->_deleteCourseItem();
        if ($this->points > 0) {
            $this->_deleteCourseGrade();
        }
        $this->deleteItem();
        $this->setItemOrder(true);
        $this->dbh->commit();
        return $this;
    }

    /**
     * Remove grades when deleting an item
     *
     * @return $this
     */
    private function _deleteCourseGrade()
    {
        $stm = $this->dbh->prepare(
            "DELETE FROM imas_grades "
            . "WHERE gradetypeid=:gradetypeid AND gradetype='exttool'"
        );
        $stm->execute(array(':gradetypeid'=>$this->typeid));
        return $this;
    }

    /**
     * Delete Course Item from imas_items table
     *
     * @return $this
     */
    private function _deleteCourseItem()
    {
        $stm = $this->dbh->prepare("DELETE FROM imas_items WHERE id=:id");
        $stm->execute(array(':id'=>$this->itemid));
        return $this;
    }

    /**
     * Set the order and hierarchy for the course items in imas_courses.itemorder
     *
     * @param bool $delete if removing from imas_items table and updating order
     *
     * @return bool|string
     */
    public function setItemOrder($delete = false)
    {
        $order = $this->findItemOrder();
        $blocktree = explode('-', $this->block);
        $sub =& $order;
        for ($i=1;$i<count($blocktree);$i++) {
            $sub =& $sub[$blocktree[$i]-1]['items']; //-1 to adjust for 1-indexing
        }
        if ($delete) {
            $key = array_search($this->itemid, $sub);
            if ($key!==false) {
                array_splice($sub, $key, 1);
            }
        } elseif ($this->totb=='b') {
                $sub[] = $this->itemid;
        } else if ($this->totb=='t') {
            array_unshift($sub, $this->itemid);
        }
        $itemorder = serialize($order);
        $stm = $this->dbh->prepare(
            "UPDATE imas_courses SET itemorder=:itemorder WHERE id=:id"
        );
        $stm->execute(array(':itemorder'=>$itemorder, ':id'=>$this->courseid));
        if ($stm->rowCount()>0) {
            return $itemorder;
        }
        return false;
    }


    /**
     * Unserialized json data of the order and hierarchy of course items
     *
     * @return array
     */
    public function findItemOrder()
    {
        $stm = $this->dbh->prepare(
            "SELECT itemorder FROM imas_courses WHERE id=:id"
        );
        $stm->bindValue(':id', $this->courseid);
        $stm->execute();
        $json = $stm->fetch(PDO::FETCH_ASSOC);
        return unserialize($json['itemorder']);
    }

    /**
     * Web safe color
     *
     * @param int $etime enddate as timestamp
     * @param int $now   time()
     *
     * @return string
     */
    function makecolor($etime, $now)
    {
        if (!$GLOBALS['colorshift']) {
            return "#ff0";
        }
        //$now = time();
        if ($etime<$now) {
            $color = "#ccc";
        } else if ($etime-$now < 605800) {  //due within a week
            $color = "#f".dechex(floor(16*($etime-$now)/605801))."0";
        } else if ($etime-$now < 1211600) { //due within two weeks
            $color = "#". dechex(floor(16*(1-($etime-$now-605800)/605801))) . "f0";
        } else {
            $color = "#0f0";
        }
        return $color;
    }

    /**
     * Web safe color
     *
     * @param int $stime startdate as timestamp
     * @param int $etime enddate as timestamp
     * @param int $now   time()
     *
     * @return string
     */
    public function makecolor2($stime, $etime, $now)
    {
        if (!$GLOBALS['colorshift']) {
            return "#ff0";
        }
        if ($etime==2000000000 && $now >= $stime) {
            return '#0f0';
        } else if ($stime==0) {
            return $this->makecolor($etime, $now);
        }
        if ($etime==$stime) {
            return '#ccc';
        }
        $r = ($etime-$now)/($etime-$stime);
        //0 = etime, 1=stime; 0:#f00, 1:#0f0, .5:#ff0
        if ($etime<$now || $stime>$now) {
            $color = '#ccc';
        } else if ($r<.5) {
            $color = '#f'.dechex(floor(32*$r)).'0';
        } else if ($r<1) {
            $color = '#'.dechex(floor(32*(1-$r))).'f0';
        } else {
            $color = '#0f0';
        }
        return $color;
    }

    /**
     * Item Settings
     *
     * @param int  $now     time()
     * @param bool $viewall show all items
     *
     * @return array
     */
    function itemSettings($now, $viewall = true)
    {
        if ($this->startdate == 0) {
            $startdate = _('Always');
        } else {
            $startdate = formatdate($this->startdate);
        }
        if ($this->enddate == 2000000000) {
            $enddate = _('Always');
        } else {
            $enddate = formatdate($this->enddate);
        }
        if ($this->avail == 2) {
            $color = '#0f0';
        } else if ($this->avail == 0) {
            $color = '#ccc';
        } else {
            $color = $this->makecolor2($this->startdate, $this->enddate, $now);
        }
        $faded = true;
        $show = '';
        if ($viewall || $this->avail == 2
            || ($this->avail == 1
            && $this->startdate < $now
            && $this->enddate > $now)
        ) {
            if ($this->avail == 2) {
                $faded = false;
                $show = _('Showing Always ');
                $color = '#0f0';
            } else if ($this->avail == 1
                && $this->startdate < $now
                && $this->enddate > $now
            ) {
                $faded = false;
                $show = _('Showing until:') . " $enddate";
                $color = $this->makecolor2($this->startdate, $this->enddate, $now);
            } else if ($this->avail == 0) {
                $show = _('Hidden');
            } else if ($viewall) {
                $show = sprintf(_('Showing %1$s until %2$s'), $startdate, $enddate);
            }
        }
        return [
            'startdate' => $startdate,
            'enddate' => $enddate,
            'faded' => $faded,
            'show' => $show,
            'color' => $color,
        ];
    }

    /**
     * Display the quick view to rename and rearrange items
     *
     * @param int  $now       time()
     * @param bool $viewall   show all items
     * @param bool $showlinks show links for item actions
     * @param bool $showdates show start and end dates
     *
     * @return string
     */
    public function courseQuickView(
        $now, $viewall = true,
        $showlinks = false, $showdates = false
    ) {
        $settings = $this->itemSettings($now, $viewall);
        $out = '<li id="' . $this->itemid . '">';
        if (!isset($this->miniicon)) {
            $out .= '<span class=icon style="background-color:'
                . $settings['color'] . '">'
                . '!</span>';
        } else {
            $out .= '<img alt="$this->type" src="' . $this->imasroot
                . '/img/' . $this->miniicon . '" class="mida icon" /> ';
        }
        if ($this->avail == 1
            && $this->startdate < $now
            && $this->enddate > $now
        ) {
            $out .= '<b><span id="' . $this->statusletter
                . Sanitize::encodeStringForDisplay($this->typeid)
                . '" onclick="editinplace(this)">'
                . Sanitize::encodeStringForDisplay($this->name)
                . "</span></b>";
            if ($showdates) {
                printf(
                    _(' showing until %s'),
                    Sanitize::encodeStringForDisplay($settings['enddate'])
                );
            }
        } else {
            $out .= '<i><b><span id="' . $this->statusletter
                . Sanitize::encodeStringForDisplay($this->typeid)
                . '" onclick="editinplace(this)">'
                . Sanitize::encodeStringForDisplay($this->name)
                . "</span></b></i>";
            if ($showdates) {
                printf(
                    _(' showing %1$s until %2$s'),
                    Sanitize::encodeStringForDisplay($settings['startdate']),
                    Sanitize::encodeStringForDisplay($settings['enddate'])
                );
            }
        }
        if ($showlinks) {
            $out .= '<span class="links">';
            $out .= " | <a href=\"itemadd.php?type=$this->typename&id=$this->typeid"
                . "&block=$this->block&cid=$this->courseid\">"
                . _('Modify') . "</a>\n";
            $out .= " | <a href=\"itemdelete.php?type=$this->typename&id=$this->typeid"
                . "&block=$this->block&cid=$this->courseid&remove=ask\">"
                . _('Delete') . "</a>\n";
            $out .= " | <a href=\"copyoneitem.php?cid=" . $this->courseid
                . "&copyid=" . Sanitize::encodeUrlParam($this->itemid) . "\">"
                . _('Copy') . "</a>";
            $out .= '</span>';
        }
        $out .= '</li>';
        return $out;
    }

    /**
     * Display the course syllabus
     *
     * @param int  $now     time()
     * @param bool $viewall show all items
     * @param bool $canedit user can edit the item
     *
     * @return string
     */
    public function courseView(
        $now, $viewall = true, $canedit = false
    ) {
        $settings = $this->itemSettings($now, $viewall);
        $out = '';
        if (strpos($this->summary, '<p') !== 0) {
            $this->summary = '<p>' . $this->summary . '</p>';
            if (preg_match('/^\s*<p[^>]*>\s*<\/p>\s*$/', $this->summary)) {
                $this->summary = '';
            }
        }
        if ($viewall || $this->avail == 2
            || ($this->avail == 1
            && $this->startdate < $now
            && $this->enddate > $now)
        ) {
            if ($settings['faded']) {
                $class = "item itemgrey";
            } else {
                $class = "item";
            }
            if ($this->itemid != '') {
                $out .= "<div class=\"$class\" id=\"$this->itemid\">\n";
            } else {
                $out .= "<div class=\"$class\">\n";
            }
            $out .= '<div class="itemhdr">';
            $out .= $this->findItemIcon($settings['faded']);
            $out .= "<div class=title>";
            if ($this->avail == 2
                || ($this->avail == 1
                && $this->startdate < $now && $this->enddate > $now)
            ) {
                if (isset($studentid) && !isset($sessiondata['stuview'])) {
                    $rec = "data-base=\"". $this->typename . "-$this->typeid\"";
                } else {
                    $rec = '';
                }
            } else if ($viewall) {
                $rec = 'style="font-style: italic;"';
            }
            $out .= "<b><a href=\"". $this->imasroot
                . "/course/itemview.php?type="
                . $this->typename
                . "&cid=" . $this->courseid
                . "&id=". $this->typeid. "\" $rec>"
                . Sanitize::encodeStringForDisplay($this->name)
                . "</a></b>\n";
            if ($viewall) {
                $out .= '<span class="instrdates">';
                $out .= "<br/>" . $settings['show'];
                $out .= '</span>';
            }
            $out .= '</div>'; //title
            if ($canedit) {
                $out .= '<div class="itemhdrdd dropdown">';
                $out .= '<a tabindex=0 class="dropdown-toggle" id="dropdownMenu'
                    .$this->itemid . '" data-toggle="dropdown" aria-haspopup="true" '
                    . 'aria-expanded="false">';
                $out .= '<img src="../img/gearsdd.png" '
                    . 'alt="Options" class="mida"/>';
                $out .= '</a>';
                $out .= '<ul class="dropdown-menu dropdown-menu-right"'
                    . 'role="menu" '
                    . 'aria-labelledby="dropdownMenu' . $this->itemid . '">';
                $out .= "<li><a href=\""
                    . $this->imasroot . "/course/itemadd.php?type="
                    . $this->typename . "&id=$this->typeid&block=$this->block&cid="
                    . $this->courseid . "\">" . _('Modify') . "</a></li>";
                $out .= "<li><a href=\"#\" "
                    ."onclick=\"return moveDialog('$this->block','$this->itemid')\">"
                    . _('Move') . '</a></li>';
                $out .= "<li><a href=\"". $this->imasroot
                    . "/course/itemdelete.php?type="
                    . $this->typename."&id=".$this->typeid."&block=$this->block&cid="
                    . $this->courseid
                    . "&remove=ask\">" . _('Delete') . "</a></li>";
                $out .= " <li><a href=\"copyoneitem.php?cid=" . $this->courseid
                    . "&copyid=$this->itemid&backref="
                    . $this->typename."{$this->typeid}\">"
                    . _('Copy') . "</a></li>";
                if ($this->showstats) {
                    $out .= "<li><a href=\"contentstats.php?cid=".$this->courseid
                        . "&type=" . $this->statusletter
                        . "&id=$this->typeid\">" . _('Stats') . '</a></li>';
                }
                $out .= '</ul>';
                $out .= '</div>';
            }
            $out .= '</div>'; //itemhdr
            $out .= filter("<div class=itemsum>{$this->summary}</div>\n");
            $out .=  '<div class="clear"></div>'
                . "</div>\n";
        }
        return $out;
    }

    /**
     * Shows the item and item progress icons
     *
     * @param bool   $faded    if the item is not accessible
     * @param int    $status   progress status of item
     * @param string $scoremsg text version of progress status
     *
     * @return string
     */
    function findItemIcon($faded = false, $status=-1, $scoremsg='')
    {
        $out = '<div class="itemhdricon"';
        if ($scoremsg != '') {
            $out .= ' data-tip="'. Sanitize::encodeStringForDisplay($scoremsg) .'"';
        }
        $out .= '>';
        $out .= '<img alt="' . $this->itemname . ' icon" ';
        if ($faded) {
            $out .= 'class="faded"';
        }
        $out .= ' src="'
            . $this->imasroot . '/img/' . $this->itemicon . '"/>';
        if ($status>-1) {
            $icon = '';
            switch ($status) {
            case 0:
                $icon = 'emptycircle';
                break;
            case 1:
                $icon = 'halfcircle';
                break;
            case 2:
                $icon = 'fullcircle';
                break;
            }
            $class = $faded?' faded':'';
            $out .= '<img alt="' . Sanitize::encodeStringForDisplay($scoremsg)
                . '" src="' . $this->imasroot . '/img/' . $icon
                . '.png" class="circoverlay'. $class . '" />';
        }
        $out .= '</div>';
        return $out;
    }

    /**
     * If the parameter is not set it will return null
     *
     * @param string $name The name of the parameter
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == 'itemtype') {
            return str_replace(' ', '', $this->display_name);
        }
        return $this->$name;
    }

    /**
     * Set the item properties, including required properties
     *
     * @param array $items database field name and values
     *
     * @return $this
     */
    public function setItem(array $items)
    {
        foreach ($items as $key=>$value) {
            $this->$key = $value;
        }
        $this->setId();
        $this->setName();
        $this->setSummary();
        $this->setStartDate();
        $this->setEndDate();
        $this->setAvail();
        $this->findCourseItemId();
        return $this;
    }

    public function setId($value = null)
    {
        if ($value) {
            $this->typeid = $value;
        } else {
            $this->typeid = $this->id;
        }
        return $this;
    }

    /**
     * Required parameter for all items: maybe in database as name or title
     *
     * @param int|null $value default to this->name
     *
     * @return CourseItem compatible object
     */
    abstract function setName($value = null);

    /**
     * Required parameter for all items: maybe in database as summary, text or description
     *
     * @param int|null $value default to this->summary
     *
     * @return CourseItem compatible object
     */
    abstract function setSummary($value = null);

    /**
     * Required parameter for all items
     *
     * @param int|null $value default to this->startdate
     *
     * @return CourseItem compatible object
     */
    abstract function setStartDate($value = null);

    /**
     * Required parameter for all items
     *
     * @param int|null $value default to this->enddate
     *
     * @return CourseItem compatible object
     */
    abstract function setEndDate($value = null);

    /**
     * Required parameter for all items
     *
     * @param int|null $value default to this->avail
     *
     * @return CourseItem compatible object
     */
    abstract function setAvail($value = null);

    abstract static function deleteCourse(int $courseid);

}