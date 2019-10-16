<?php
namespace OHM\Includes;
use PDO;
abstract class CourseItems
{
    /** @var \PDO */
    protected $dbh;
    protected $courseid;
    protected $block;
    protected $totb;
    protected $itemicon;
    protected $miniicon;
    protected $imasroot;
    /**
     * CourseItems constructor.
     *
     * @param $courseid integer The scourse ID. (MySQL table imas_courses, id column)
     * @param $block integer The course item block
     * @param $totb string The course item order
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
    public function addCourseItems($itemtype, $typeid) {
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
    public function setItemOrder($itemid) {
        $line = $this->getItemOrder($this->courseid);
        $items = unserialize($line['itemorder']);
        $blocktree = explode('-',$this->block);
        $sub =& $items;
        for ($i=1;$i<count($blocktree);$i++) {
            $sub =& $sub[$blocktree[$i]-1]['items']; //-1 to adjust for 1-indexing
        }
        if ($this->totb=='b') {
            $sub[] = $itemid;
        } else if ($this->totb=='t') {
            array_unshift($sub,$itemid);
        }
        $itemorder = serialize($items);
        $stm = $this->dbh->prepare("UPDATE imas_courses SET itemorder=:itemorder WHERE id=:id");
        $stm->execute(array(':itemorder'=>$itemorder, ':id'=>$this->courseid));
        if ($stm->rowCount()>0) {
            return $itemorder;
        }
        return false;
    }
    public function getItemOrder() {
        $stm = $this->dbh->prepare("SELECT itemorder FROM imas_courses WHERE id=:id");
        $stm->execute(array(':id'=>$this->courseid));
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    function getCourseItem($line, $typeid, $itemid, $parent, $now, $view = 'quick', $viewall = false, $canedit = false, $showlinks = false, $showdates = false, $duedates = '') {
        $out = '';
        if ($line['startdate']==0) {
            $startdate = _('Always');
        } else {
            $startdate = formatdate($line['startdate']);
        }
        if ($line['enddate']==2000000000) {
            $enddate = _('Always');
        } else {
            $enddate =formatdate($line['enddate']);
        }
        if ($line['avail']==2) {
            $color = '#0f0';
        } else if ($line['avail']==0) {
            $color = '#ccc';
        } else {
            $color = makecolor2($line['startdate'],$line['enddate'],$now);
        }
        $faded = true;
        if ($viewall || $line['avail'] == 2 || ($line['avail'] == 1 && $line['startdate'] < $now && $line['enddate'] > $now)) {
            if ($line['avail'] == 2) {
                $faded = false;
                $show = _('Showing Always ');
                $color = '#0f0';
            } else if ($line['avail'] == 1 && $line['startdate'] < $now && $line['enddate'] > $now) {
                $faded = false;
                $show = _('Showing until:') . " $enddate";
                $color = makecolor2($line['startdate'], $line['enddate'], $now);
            } else if ($line['avail'] == 0) {
                $show = _('Hidden');
            } else if ($viewall) {
                $show = sprintf(_('Showing %1$s until %2$s'), $startdate, $enddate);
            }
        }
        if ($view == 'quick') {
            if (!isset($this->miniicon)) {
                $icon  = '<span class=icon style="background-color:'.$color.'">!</span>';
            } else {
                $icon = '<img alt="text" src="'.$this->imasroot.'/img/'.$this->miniicon.'" class="mida icon" /> ';
            }
            $out .= '<li id="' . \Sanitize::encodeStringForDisplay($typeid) . '">' . $icon;
            if ($line['avail']==1 && $line['startdate']<$now && $line['enddate']>$now) {
                $out  .= '<b><span id="L' . \Sanitize::encodeStringForDisplay($typeid) . '" onclick="editinplace(this)">'
                    . \Sanitize::encodeStringForDisplay($line['name']). "</span></b>";
                if ($showdates) {
                    printf(_(' showing until %s'), \Sanitize::encodeStringForDisplay($enddate));
                }
            } else {
                $out .= '<i><b><span id="L' . \Sanitize::encodeStringForDisplay($typeid) . '" onclick="editinplace(this)">'
                    . \Sanitize::encodeStringForDisplay($line['name']). "</span></b></i>";
                if ($showdates) {
                    printf(_(' showing %1$s until %2$s'), \Sanitize::encodeStringForDisplay($startdate), \Sanitize::encodeStringForDisplay($enddate));
                }
            }
            if ($showlinks) {
                $out .= '<span class="links">';
                $out .= " | <a href=\"" . $this->addUrl . "?id=" . \Sanitize::onlyInt($typeid) . "&block=$parent&cid=" . $this->courseid . "\">" . _('Modify') . "</a>\n";
                $out .= " | <a href=\"" . $this->deleteUrl . "?id=" . \Sanitize::onlyInt($typeid) . "&block=$parent&cid=" . $this->courseid . "&remove=ask\">" . _('Delete') . "</a>\n";
                $out .= " | <a href=\"copyoneitem.php?cid=" . $this->courseid . "&copyid=" . \Sanitize::encodeUrlParam($itemid) . "\">" . _('Copy') . "</a>";
                $out .= '</span>';
            }
            $out .= '</li>';
        } else {
            if (strpos($line['summary'], '<p') !== 0) {
                $line['summary'] = '<p>' . $line['summary'] . '</p>';
                if (preg_match('/^\s*<p[^>]*>\s*<\/p>\s*$/', $line['summary'])) {
                    $line['summary'] = '';
                }
            }
            if ($viewall || $line['avail'] == 2 || ($line['avail'] == 1 && $line['startdate'] < $now && $line['enddate'] > $now)) {
                $out .= $this->beginItem($itemid); //echo "<div class=item>\n
                $out .= '<div class="itemhdr">';
                $out .= $this->getItemIcon($faded);
                $out .= "<div class=title>";
                if ($line['avail'] == 2 || ($line['avail'] == 1 && $line['startdate'] < $now && $line['enddate'] > $now)) {
                    if (isset($studentid) && !isset($sessiondata['stuview'])) {
                        $rec = "data-base=\"". $this->typename . "-$typeid\"";
                    } else {
                        $rec = '';
                    }
                } else if ($viewall) {
                    $rec = 'style="font-style: italic;"';
                }
                $out .= "<b><a href=\"" . $this->viewUrl . "?cid=" . $this->courseid . "&id={$line['id']}\" $rec>"
                    . \Sanitize::encodeStringForDisplay($line['title']) . "</a></b>\n";
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
                    $out .= $this->getDropDown($typeid, $parent, $itemid);
                }
                $out .= '</div>'; //itemhdr
                $out .= filter("<div class=itemsum>{$line['summary']}</div>\n");
                $out .= $this->endItem($canedit); //echo "</div>\n";
            }
        }
        return $out;
    }
    function getDropDown($typeid, $parent, $itemid) {
        $out = '<div class="itemhdrdd dropdown">';
        $out .= '<a tabindex=0 class="dropdown-toggle" id="dropdownMenu'.$itemid.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $out .= ' <img src="../img/gearsdd.png" alt="Options" class="mida"/>';
        $out .= '</a>';
        $out .= '<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropdownMenu'.$itemid.'">';
        $out .= " <li><a href=\"".$this->addUrl."?id=$typeid&block=$parent&cid=" . $this->courseid . "\">" . _('Modify') . "</a></li>";
        //$out .= " <li><a href=\"#\" onclick=\"return moveDialog('$parent','$itemid');\">" . _('Move') . '</a></li>';
        //$out .= " <li><a href=\"".$this->deleteUrl."?id=$typeid&block=$parent&cid=" . $this->courseid . "&remove=ask\">" . _('Delete') . "</a></li>";
        //$out .= " <li><a href=\"copyoneitem.php?cid=" . $this->courseid . "&copyid=$itemid&backref=" . $this->typename . "{$typeid}\">" . _('Copy') . "</a></li>";
        if ($this->showstats) {
            $out .= " <li><a href=\"contentstats.php?cid=" . $this->courseid . "&type="
                . $this->statusletter
                . "&id=$typeid\">" . _('Stats') . '</a></li>';
        }
        $out .= '</ul>';
        $out .= '</div>';
        return $out;
    }
    function beginItem($aname='',$greyed=false) {
        if ($greyed) {
            $class = "item itemgrey";
        } else {
            $class = "item";
        }
        if ($aname != '') {
            return "<div class=\"$class\" id=\"$aname\">\n";
        } else {
            return "<div class=\"$class\">\n";
        }
    }
    function endItem() {
    }
    function getItemIcon($faded = false, $status=-1, $scoremsg='') {
        $out = '<div class="itemhdricon"';
        if ($scoremsg != '') {
            $out .= ' data-tip="'. Sanitize::encodeStringForDisplay($scoremsg) . '"';
        }
        $out .= '>';
        if ($faded) {
            $class = 'class="faded"';
        }
        $out .= '<img alt="' . $this->itemname . ' icon" ' . $class . ' src="' . $this->imasroot . '/img/' . $this->itemicon . '"/>';
        if ($status>-1) {
            switch ($status) {
                case 0: $icon = 'emptycircle'; break;
                case 1: $icon = 'halfcircle'; break;
                case 2: $icon = 'fullcircle'; break;
            }
            $class = $faded?' faded':'';
            $out .= '<img alt="' . Sanitize::encodeStringForDisplay($scoremsg) . '" ';
            $out .= 'src="' . $this->imasroot . '/img/' . $icon . '.png" class="circoverlay'. $class . '" />';
        }
        $out .= '</div>';
        return $out;
    }
}