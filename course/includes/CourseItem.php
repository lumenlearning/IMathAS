<?php
/**
 * Repo iMathAS: View Item Page
 */

namespace Course\Includes;
use PDO;

/**
 * Class CourseItem
 *
 * @package Course\Includes
 * @author  Alena Holligan <alena@lumenlearning.com>
 */
abstract class CourseItem
{
    // global properties eventually using dependency injection
    protected $dbh;
    protected $imasroot;
    protected $itemicon;
    protected $miniicon;

    // constructor properties
    protected $courseid;
    protected $block;
    protected $totb;

    // required item specific properties
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
     * @param string $totb
     */
    public function __construct($courseid, $block = 0, $totb = 'b')
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
    }

    /**
     * Add Course Item to database
     *
     * @param string $itemtype Individual Item Types
     * @param int    $typeid   linked to the items table
     *
     * @return int identifier from imas_items table
     */
    public function addCourseItems($itemtype, $typeid)
    {
        $query = "INSERT INTO imas_items (courseid,itemtype,typeid) VALUES "
            . "(:courseid, :itemtype, :typeid);";
        $stm = $this->dbh->prepare($query);
        $stm->execute(
            array(
                ':courseid'=>$this->courseid,
                ':itemtype'=>$itemtype,
                ':typeid'=>$typeid
            )
        );
        return $this->dbh->lastInsertId();
    }

    /**
     * Set the order and hierarchy for the course items in imas_courses.itemorder
     *
     * @param int $itemid identifier from imas_items table
     *
     * @return bool|string
     */
    public function setItemOrder($itemid)
    {
        $order = $this->getItemOrder($this->courseid);
        $blocktree = explode('-', $this->block);
        $sub =& $order;
        for ($i=1;$i<count($blocktree);$i++) {
            $sub =& $sub[$blocktree[$i]-1]['items']; //-1 to adjust for 1-indexing
        }
        if ($this->totb=='b') {
            $sub[] = $itemid;
        } else if ($this->totb=='t') {
            array_unshift($sub, $itemid);
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
    public function getItemOrder()
    {
        $stm = $this->dbh->prepare(
            "SELECT itemorder FROM imas_courses WHERE id=:id"
        );
        $stm->execute(array(':id'=>$this->courseid));
        $json = $stm->fetch(PDO::FETCH_ASSOC);
        return unserialize($json['itemorder']);
    }

    /**
     * @param $itemid
     * @param $parent
     * @param $now
     * @param string $view
     * @param bool $viewall
     * @param bool $canedit
     * @param bool $showlinks
     * @param bool $showdates
     * @param string $duedates
     *
     * @return string
     */
    function getCourseItem($itemid, $parent, $now,
        $view = 'quick', $viewall = false, $canedit = false,
        $showlinks = false, $showdates = false, $duedates = ''
    ) {
        $out = '';
        if ($this->startdate==0) {
            $startdate = _('Always');
        } else {
            $startdate = formatdate($this->startdate);
        }
        if ($this->enddate==2000000000) {
            $enddate = _('Always');
        } else {
            $enddate =formatdate($this->enddate);
        }
        if ($this->avail==2) {
            $color = '#0f0';
        } else if ($this->avail==0) {
            $color = '#ccc';
        } else {
            $color = makecolor2($this->startdate, $this->enddate, $now);
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
                $color = makecolor2($this->startdate, $this->enddate, $now);
            } else if ($this->avail == 0) {
                $show = _('Hidden');
            } else if ($viewall) {
                $show = sprintf(_('Showing %1$s until %2$s'), $startdate, $enddate);
            }
        }
        if ($view == 'quick') {
            $out .= '<li id="' . \Sanitize::encodeStringForDisplay($this->typeid) . '">';
            if (!isset($this->miniicon)) {
                $out .= '<span class=icon style="background-color:'.$color.'">';
                $out .= '!</span>';
            } else {
                $out .= '<img alt="text" src="'.$this->imasroot
                    . '/img/'.$this->miniicon.'" class="mida icon" /> ';
            }
            if ($this->avail==1
                && $this->startdate < $now
                && $this->enddate > $now
            ) {
                $out .= '<b><span id="L' . \Sanitize::encodeStringForDisplay($this->typeid)
                    . '" onclick="editinplace(this)">'
                    . \Sanitize::encodeStringForDisplay($this->name)
                    . "</span></b>";
                if ($showdates) {
                    printf(
                        _(' showing until %s'),
                        \Sanitize::encodeStringForDisplay($enddate)
                    );
                }
            } else {
                $out .= '<i><b><span id="L'
                    . \Sanitize::encodeStringForDisplay($this->typeid)
                    . '" onclick="editinplace(this)">'
                    . \Sanitize::encodeStringForDisplay($this->name)
                    . "</span></b></i>";
                if ($showdates) {
                    printf(
                        _(' showing %1$s until %2$s'),
                        \Sanitize::encodeStringForDisplay($startdate),
                        \Sanitize::encodeStringForDisplay($enddate)
                    );
                }
            }
            if ($showlinks) {
                $out .= '<span class="links">';
                $out .= " | <a href=\"itemadd.php?type=$this->typename&id=$this->typeid"
                    . "&block=$parent&cid=$this->courseid\">"
                    . _('Modify') . "</a>\n";
                /*
                $out .= " | <a href=\"itemdelete?type=$this->typename&id=$this->typeid"
                    . "&block=$parent&cid=$this->courseid&remove=ask\">"
                    . _('Delete') . "</a>\n";
                $out .= " | <a href=\"copyoneitem.php?cid=" . $this->courseid
                    . "&copyid=" . \Sanitize::encodeUrlParam($itemid) . "\">"
                    . _('Copy') . "</a>";
                $out .= '</span>';
                /**/
            }
            $out .= '</li>';
        } else {
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
                if ($faded) {
                    $class = "item itemgrey";
                } else {
                    $class = "item";
                }
                if ($itemid != '') {
                    $out .= "<div class=\"$class\" id=\"$itemid\">\n";
                } else {
                    $out .= "<div class=\"$class\">\n";
                }
                $out .= '<div class="itemhdr">';
                $out .= $this->getItemIcon($faded);
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
                    . \Sanitize::encodeStringForDisplay($this->name)
                    . "</a></b>\n";
                if ($viewall) {
                    $out .= '<span class="instrdates">';
                    $out .= "<br/>$show ";
                    $out .= '</span>';
                }
                if ($duedates != '') {
                    $out .= "<br/>$duedates";
                }
                $out .= '</div>'; //title
                if ($canedit) {
                    $out .= '<div class="itemhdrdd dropdown">';
                    $out .= '<a tabindex=0 class="dropdown-toggle" id="dropdownMenu'
                        . $itemid . '" data-toggle="dropdown" aria-haspopup="true" '
                        . 'aria-expanded="false">';
                    $out .= '<img src="../img/gearsdd.png" '
                        . 'alt="Options" class="mida"/>';
                    $out .= '</a>';
                    $out .= '<ul class="dropdown-menu dropdown-menu-right"'
                        . 'role="menu" '
                        . 'aria-labelledby="dropdownMenu' . $itemid . '">';
                    $out .= "<li><a href=\""
                        . $this->imasroot . "/course/itemadd.php?type="
                        . $this->typename . "&id=$this->typeid&block=$parent&cid="
                        . $this->courseid . "\">" . _('Modify') . "</a></li>";
                    /*
                    $out .= "<li><a href=\"#\" "
                        . "onclick=\"return moveDialog('$parent','$itemid');\">"
                        . _('Move') . '</a></li>';
                    $out .= "<li><a href=\"". $this->imasroot
                        . "/course/itemdelete.php?type="
                        . $this->typename . "&id=".$this->typeid."&block=$parent&cid="
                        . $this->courseid
                        . "&remove=ask\">" . _('Delete') . "</a></li>";
                    $out .= " <li><a href=\"copyoneitem.php?cid=" . $this->courseid
                        . "&copyid=$itemid&backref=".$this->typename."{$this->typeid}\">"
                        . _('Copy') . "</a></li>";
                    */
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
    function getItemIcon($faded = false, $status=-1, $scoremsg='')
    {
        $out = '<div class="itemhdricon"';
        if ($scoremsg != '') {
            $out .= ' data-tip="'. \Sanitize::encodeStringForDisplay($scoremsg) .'"';
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
            $out .= '<img alt="' . \Sanitize::encodeStringForDisplay($scoremsg)
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
        return $this->$name;
    }

    /**
     * Set the item properties, including required properties
     *
     * @param array $items database field name and values
     *
     * @return $this
     */
    protected function setItem(array $items)
    {
        foreach ($items as $key=>$value) {
            $this->$key = $value;
        }
        $this->setId();
        $this->setName();
        $this->setStartDate();
        $this->setEndDate();
        $this->setAvail();
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

}