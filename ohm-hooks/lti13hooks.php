<?php

use IMSGlobal\LTI\Database;
use IMSGlobal\LTI\LTI_Deep_Link_Resource;
use IMSGlobal\LTI\LTI_Localcourse;
use IMSGlobal\LTI\LTI_Message_Launch;
use IMSGlobal\LTI\LTI_Placement;

/**
 * Define custom placement types
 * 
 * for each placementtype supported by the hooks, associate 
 * it with a unique number.  The numbers should be
 * > 50 to avoid conflicts with the core system types
 * 
 * The keys should be the same placementtype strings you 
 * use below in lti_handle_launch
 */
function lti_get_types_as_num(): array {
    return [
        'desmos' => 38
    ];
}

/**
 * Determine if the hooks can handle a launch of this URI
 *
 * MOM hooks now look for the same function under two different names
 * with no obvious change in functionality. This function just calls
 * the previous function and acts as a pass-through.
 *
 * @param string  $targetlink  The target_link_uri for the launch
 * @return bool true if hooks can handle this launch uri
 */
function lti_can_ext_handle_launch(string $targetlink): bool
{
    return lti_can_handle_launch($targetlink);
}

/**
 * Determine if the hooks can handle a launch of this URI
 * 
 * @param string  $targetlink  The target_link_uri for the launch
 * @return bool true if hooks can handle this launch uri
 */
function lti_can_handle_launch(string $targetlink): bool {
    parse_str(parse_url($targetlink, PHP_URL_QUERY), $param);
    return (!empty($param['custom_item_type']) && 
        $param['custom_item_type'] == 'DesmosItem' &&
        !empty($param['custom_item_id']));
}

/**
 * Determine if the hooks can redirect to a placement type.
 *
 * @param string $placementtype The placementtype for the launch.
 * @return bool true if hooks can handle this placement type.
 */
function lti_can_handle_redirect(string $placementtype): bool {
    $placementTypes = array_keys(lti_get_types_as_num());
    return in_array($placementtype, $placementTypes);
}

/**
 * Parses a target_link_uri for the launch.  
 * 
 * @param string  $targetlink  The target_link_uri for the launch
 * @return array  empty if not parsable.  Otherwise an array with keys:
 *   'refcid'   (required) The courseid the target item is in
 *   'type'     (required) A short string name for the item type
 *   'refid'    The type id for the target item
 * 
 */
function lti_parse_target_link(string $targetlink): array {
    global $DBH;

    parse_str(parse_url($targetlink, PHP_URL_QUERY), $param);
    if (!empty($param['custom_item_type']) && 
        $param['custom_item_type'] == 'DesmosItem' &&
        !empty($param['custom_item_id'])
    ) {
        $refid = intval($param['custom_item_id']);
        $stm = $DBH->prepare('SELECT courseid FROM desmos_items WHERE id=?');
        $stm->execute([$refid]);
        $courseid = $stm->fetchColumn(0);
        if ($courseid !== false) {
            return ['refcid' => $courseid, 'type' => 'desmos', 'refid' => $refid];
        }
    }
    return [];
}

/**
 * Get courses we could associate with.  These should be courses the user
 * is a teacher of and contains a copy of the target item.
 * 
 * This function should double-check the $targetinfo['type']
 * 
 * @param array $targetinfo    The output of lti_parse_target_link
 * @return array of courseid=>name.  Return an empty array if target
 *               isn't a supported type.
 */
function lti_get_othercourses(array $targetinfo, int $userid): array {
    global $DBH;
    $othercourses = array();
    if ($targetinfo['type'] == 'desmos') {
        $query = "SELECT DISTINCT ic.id,ic.name FROM imas_courses AS ic JOIN imas_teachers AS imt ON ic.id=imt.courseid ";
        $query .= "AND imt.userid=:userid JOIN desmos_items AS di ON ic.id=di.courseid ";
        $query .= "WHERE ic.available<4 AND ic.ancestors REGEXP :cregex AND di.itemid_chain REGEXP :dregex ORDER BY ic.name";
        $stm = $DBH->prepare($query);
        $stm->execute(array(
            ':userid' => $userid,
            ':cregex' => MYSQL_LEFT_WRDBND . $target['refcid'] . MYSQL_RIGHT_WRDBND,
            ':dregex' => MYSQL_LEFT_WRDBND . $target['refid'] . MYSQL_RIGHT_WRDBND));
        while ($row = $stm->fetch(PDO::FETCH_NUM)) {
            $othercourses[$row[0]] = $row[1];
        }
    }
    return $othercourses;
}

/**
 * Handles a launch when an existing placement doesn't exist yet 
 * 
 * @param LTI_Message_launch $launch    The launch object
 * @param LTI_Localcourse $localcourse  Course info object
 * @param int $localuserid              imas_users.id 
 * @param Database $db                  An instance of Database 
 * @return LTI_Placement
 */
function lti_handle_launch(
    LTI_Message_Launch $launch,  
    LTI_Localcourse $localcourse,
    int $localuserid,
    Database $db
): LTI_Placement  {
    // info from the launch you may need:
    $targetlink = $launch->get_target_link(); // the launched URI

    $role = standardize_role($launch->get_roles());
    $contextid = $launch->get_platform_context_id();
    $platform_id = $launch->get_platform_id();
    $resource_link = $launch->get_resource_link();

    // get the destination course ID
    $destcid = $localcourse->get_courseid();

    /*
    after parsing the $targetlink, you'll want to check if the 
    source course ID matches the $destcid, and if not handle
    determing the right thing to associate or copy in.

    Once you have the right item, you'll want to store the 
    placement.  You will need to define
    $placementtype: A short string (max 10 char) for the type of linked item
    $typeid:  An id (INT) for the linked item
    */

    // DESMOS STUFF

    $target = lti_parse_target_link($targetlink);
    $sourceaid = $target['refid'];
    $destcid = $localcourse->get_courseid();
    // is an assessment launch
    if ($target['refcid'] == $destcid) {
      // see if aid is in the current course, we just use it
      $link = $db->make_link_assoc($sourceaid,'desmos',$resource_link['id'],$contextid,$platform_id);
    } else {
      // need to find the assessment
      $destaid = false;
      if ($target['refcid'] == $localcourse->get_copiedfrom()) {
        // aid is in the originally copied course - find our copy of it
        $destaid = find_desmos_by_immediate_ancestor($sourceaid, $destcid);
      }
      if ($destaid === false) {
        // try looking further back
        $destaid = find_desmos_by_ancestor_walkback(
          $sourceaid,
          $target['refcid'],
          $localcourse->get_copiedfrom(),
          $destcid);
      }
      if ($destaid === false) {
        // can't find item - copy it
        $item = new \Desmos\Models\DesmosItem($cid);
		$item->copyItem($typeid, $_POST['append'], $sethidden);
		$destaid  = $item->typeid;
      }
      if ($destaid !== false) {
        $link = $db->make_link_assoc($destaid,'desmos',$resource_link['id'],$contextid,$platform_id);
      } else {
        echo 'Error - unable to establish link';
        exit;
      }
    }

    // END DESMOS STUFF


    /*
    If the item has a grade record associated, you'll need to 
    set the lineitem.  If you have no grades, skip this step.
    */
    // Not needed for Desmos
    // $iteminfo = lti_get_item_info($link);
    // $db->set_or_create_lineitem($launch, $link, $iteminfo, $localcourse);

    return $link;
}

function lti_redirect_launch(LTI_Placement $link): void
{
    global $DBH;

    if (empty($link->get_placementtype()) || empty($link->get_typeid())) {
        echo 'Error: LTI_Placement is missing placement type or type id.';
        exit;
    }

    // Only handle Desmos items
    if ('desmos' != $link->get_placementtype()) {
        printf("Error: Unknown placement type: %s (expected 'desmos')",
            $link->get_placementtype());
        exit;
    }

    /*
     * The following is based on code found in /desmos/bltilaunch.php.
     */

    $itemid = $link->get_typeid();
    $item = new Desmos\Models\DesmosItem();
    if (!$item->findItem($itemid)) {
        $diaginfo = "(Debug info: 30-".$itemid.")";
        echo "This item does not appear to exist anymore. $diaginfo";
        exit;
    }

    $userid = $GLOBALS['userid'];
    if (empty($userid) && !empty($_SESSION['userid'])) {
        $userid = $_SESSION['userid'];
    }

    if ($_SESSION['ltirole'] == 'learner' && !empty($userid)) {
        $stm = $DBH->prepare('INSERT INTO imas_content_track (userid,courseid,type,typeid,viewtime,info) VALUES (:userid,:courseid,\'itemlti\',:typeid,:viewtime,\'\')');
        $stm->execute(array(':userid' => $userid, ':courseid' => $item->courseid, ':typeid' => $item->itemid, ':viewtime' => time()));
    }
    header('Location: ' . $GLOBALS['basesiteurl'] . "/course/itemview.php"
        ."?type=".$link->get_placementtype()
        ."&cid=".$item->courseid
        ."&id=".$item->typeid
        ."&lms=true"
    );
}

function find_desmos_by_immediate_ancestor(int $idtolookfor, int $destcid)
{
    global $DBH;

    $anregex = '^([0-9]+:)?' . $idtolookfor . MYSQL_RIGHT_WRDBND;
    $stm = $DBH->prepare("SELECT id FROM desmos_items WHERE itemid_chain REGEXP :ancestors AND courseid=:destcid");
    $stm->execute(array(':ancestors' => $anregex, ':destcid' => $destcid));
    return $stm->fetchColumn(0);
}

function find_desmos_by_ancestor_walkback(int $sourceaid, int $aidsourcecid,
    int $copiedfrom, int $destcid
) {
    global $DBH;

    $stm = $DBH->prepare("SELECT ancestors FROM imas_courses WHERE id=?");
    $stm->execute(array($destcid));
    $ancestors = explode(',', $stm->fetchColumn(0));
    $ciddepth = array_search($aidsourcecid, $ancestors); //so if we're looking for 23, "20,24,23,26" would give 2 here.
    if ($ciddepth !== false) {
        // Walkback through course ancestors looking for copy chain
        array_unshift($ancestors, $destcid); //add current course to front
        $foundsubaid = true;
        $aidtolookfor = $sourceaid;
        for ($i = $ciddepth; $i >= 0; $i--) { //starts one course back from aidsourcecid because of the unshift
            $stm = $DBH->prepare("SELECT id FROM desmos_items WHERE itemid_chain REGEXP :ancestors AND courseid=:cid");
            $stm->execute(array(':ancestors' => '^([0-9]+:)?' . $aidtolookfor . MYSQL_RIGHT_WRDBND, ':cid' => $ancestors[$i]));
            if ($stm->rowCount() > 0) {
                $aidtolookfor = $stm->fetchColumn(0);
            } else {
                $foundsubaid = false;
                break;
            }
        }
        if ($foundsubaid) { // tracked it back all the way
            return $aidtolookfor;
        }

        // ok, still didn't work, so item wasn't copied through the whole
        // history.  So let's see if we have a copy in our course with the item
        // anywhere in the ancestry.
        $anregex = MYSQL_LEFT_WRDBND . $sourceaid . MYSQL_RIGHT_WRDBND;
        $stm = $DBH->prepare("SELECT id,title,itemid_chain FROM desmos_items WHERE itemid_chain REGEXP :ancestors AND courseid=:destcid");
        $stm->execute(array(':ancestors' => $anregex, ':destcid' => $destcid));
        $res = $stm->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) == 1) { //only one result - we found it
            return $res[0]['id'];
        }
        $stm = $DBH->prepare("SELECT title FROM desmos_items WHERE id=?");
        $stm->execute(array($sourceaid));
        $aidsourcename = $stm->fetchColumn(0);
        if (count($res) > 1) { //multiple results - look for the identical name
            foreach ($res as $k => $row) {
                $res[$k]['loc'] = strpos($row['itemid_chain'], (string) $aidtolookfor);
                if ($row['name'] == $aidsourcename) {
                    return $row['id'];
                }
            }
            //no name match. pick the one with the assessment closest to the start
            usort($res, function ($a, $b) {return $a['loc'] - $b['loc'];});
            return $res[0]['id'];
        }

        // still haven't found it, so nothing in our current course has the
        // desired assessment as an ancestor.  Try finding something just with
        // the right name maybe?
        $stm = $DBH->prepare("SELECT id FROM desmos_items WHERE title=:name AND courseid=:courseid");
        $stm->execute(array(':name' => $aidsourcename, ':courseid' => $destcid));
        if ($stm->rowCount() > 0) {
            return $stm->fetchColumn(0);
        }
    }
    return false;
}


/**
 * Returns info about the link 
 * 
 * @param LTI_Placement $link   The placed item
 * @return array with indices:
 *   'name'      // the item name 
 *   'ptsposs'   // the points possible
 *   'startdate' // optionally, start date to assoc with lineitem
 *   'enddate'   // optionally, end date to assoc with lineitem
 */
function lti_get_item_info(LTI_Placement $link): array {
    // Not needed - only needed for lineitem creation
}

/**
 * For an existing placement, determines whether these hooks 
 * can handle a submissionReview launch to look over submissions
 * 
 * @param string $placementtype   placementtype, as set in make_link_assoc call
 * @return bool  true if this placementtype can handle a submissionReview launch
 */
function lti_is_reviewable(string $placementtype): bool {
    // no grades so no need for reviewable
    return false;
}

/**
 * Do the actual redirect for a submissionReview launch an item. 
 * 
 * @param LTI_Placement $link    The placement info 
 * @return void
 */
function lti_redirect_submissionreview(LTI_Placement $link): void {
    // no need to implement - not using for Desmos
}

/**
 * Gets item link options for the deeplinking dialog 
 *
 * This function should directly echo <option> strings for 
 *  the deep linking select.  The value should have form "type-typeid"
 *  to distinguish it from the assessment options.
 * 
 * @param LTI_Localcourse $localcourse  Course info object
 * @return void
 */
function lti_deeplink_options(LTI_Localcourse $localcourse) {
    global $DBH;
    $stm = $DBH->prepare('SELECT id,title FROM desmos_items WHERE courseid=? ORDER BY title');
    $stm->execute(array($localcourse->get_courseid()));
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="desmos-'.$row['id'].'">' . Sanitize::encodeStringForDisplay($row['title']) . '</option>';
    }
}

/** 
 * Checks if hooks can handle generating a deep link response for a type 
 *
 * @param string type  The "type" from the deeplink options generated above 
 * @return bool
 */
 function lti_can_handle_deeplink(string $type) {
    return ($type == 'desmos');
 }

/**
 * Generates deep link response
 *
 * @param string type  The "type" from the deeplink options generated above
 * @param string typeid  The "typeid" from the deeplink options generated above
 * @return LTI_Deep_Link_Resource
 */
 function lti_get_deeplink_resource(string $type, string $typeid) {
     global $DBH;
     /*
      If this type generates grades, be sure to generate and set a lineitem 
      on the LTI_Deep_Link_Resource, and if it supports submission review, 
      attach a submission review to the lineitem
     */
    if ($type == 'desmos') {
        $stm = $DBH->prepare('SELECT title,courseid FROM desmos_items WHERE id=?');
        $stm->execute([$typeid]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $resource = LTI_Deep_Link_Resource::new()
            ->set_url($basesiteurl . '/lti/launch.php?custom_item_type=DesmosItem&custom_item_id='.intval($typeid).'&refcid='.$row['courseid'])
            ->set_title($row['title']);
        return $resource;
    }
 }
 

/** 
 * Display the LTI Home page for an item 
 *
 * @param LTI_Placement $link
 * @param LTI_Message_launch $launch
 * @param LTI_Localcourse $localcourse
 * @param Database $db
 * @return void
 */
function lti_ltihome(
    LTI_Placement $link, 
    LTI_Message_launch $launch, 
    LTI_Localcourse $localcourse, 
    Database $db
):void {
    global $DBH;

    $item = lti_parse_target_link($launch->get_target_link());
    if ($item['type'] == 'desmos') {
        $stm = $DBH->prepare('SELECT * FROM desmos_items WHERE id=?');
        $stm->execute([$item['refid']]);
        $iteminfo = $stm->fetch(PDO::FETCH_ASSOC);
        echo "<h2>LTI Placement of " . Sanitize::encodeStringForDisplay($iteminfo['title']) . "</h2>";
        $now = time();
        echo '<p>';
        if ($iteminfo['avail']==0) {
            echo 'Currently unavailable to students.';
        } else if ($iteminfo['avail']==1 && $iteminfo['startdate'] < $now && $iteminfo['enddate'] > $now) { //regular show
            echo "Currently available to students.  ";
            echo "Available until " . formatdate($iteminfo['enddate']);
        } else {
            echo 'Currently unavailable to students. Available '.formatdate($iteminfo['startdate']).' until '.formatdate($iteminfo['enddate']);
        }
        echo '</p>';
        if ($role == 'teacher') {
            echo '<p><a href="course/itemview.php?type=DesmosItem&id='.$iteminfo['id'].'&cid='.$iteminfo['courseid'] .'">Preview Item</a> | ';
            echo '<a href="course/itemadd.php?type=DesmosItem&id='.$iteminfo['id'].'&cid='.$iteminfo['courseid'] .'">Modify Item</a>';
            if ($myrights == 100) {
                echo ' | <a href="course/contentstats.php?cid=' . $iteminfo['courseid'] . "&type=E&id=" . $iteminfo['id'] . '">View Stats</a>';
            }
            echo "</p>";
           
            echo '<p>&nbsp;</p><p class=small>This item is housed in course ID '.Sanitize::courseId($iteminfo['courseid']).'</p>';
        }
    }
}



