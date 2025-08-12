<?php
/**
 * Example usage of course copy templates
 * This file demonstrates how to use the modular templates
 */

// Example 1: Using the complete refactored file
// Simply replace your existing coursecopylist.php with coursecopylist_refactored.php

// Example 2: Using individual templates in a custom implementation
function displayCustomCourseList($userid, $groupid, $cid) {
    // Define the constant to allow template inclusion
    define('INCLUDED_FROM_COURSECOPY', true);
    
    // Include utility functions
    require_once(__DIR__ . '/utilities.php');
    
    // Load your data here (similar to the original file)
    // ... database queries ...
    
    // Display only specific sections
    echo '<ul class="custom-course-list">';
    
    // Include just the "This Course" option
    include_once(__DIR__ . '/this_course.php');
    
    // Include just "My Courses"
    include_once(__DIR__ . '/my_courses.php');
    
    echo '</ul>';
}

// Example 3: Creating a custom course list with specific templates
function displayGroupCoursesOnly($groupid, $userid) {
    define('INCLUDED_FROM_COURSECOPY', true);
    require_once(__DIR__ . '/utilities.php');
    
    // Load group courses data
    $query = "SELECT ic.id,ic.name,ic.copyrights,iu.LastName,iu.FirstName,iu.email,it.userid,ic.termsurl 
              FROM imas_courses AS ic,imas_teachers AS it,imas_users AS iu 
              WHERE it.courseid=ic.id AND it.userid=iu.id AND iu.groupid=:groupid 
              AND iu.id<>:userid AND ic.available<4 AND ic.copyrights>-1 
              ORDER BY iu.LastName,iu.FirstName,it.userid,ic.name";
    
    $courseTreeResult = $DBH->prepare($query);
    $courseTreeResult->execute(array(':groupid'=>$groupid, ':userid'=>$userid));
    $lastteacher = 0;
    
    // Display group courses
    echo '<div class="group-courses">';
    echo '<h3>Group Courses</h3>';
    include_once(__DIR__ . '/my_group_courses.php');
    echo '</div>';
}

// Example 4: Using templates for AJAX responses
function handleAjaxCourseRequest($requestType, $params) {
    define('INCLUDED_FROM_COURSECOPY', true);
    require_once(__DIR__ . '/utilities.php');
    
    switch($requestType) {
        case 'loadothers':
            // Load other groups
            $stm = $DBH->query("SELECT id,name FROM imas_groups ORDER BY name");
            if ($stm->rowCount()>0) {
                $page_hasGroups = true;
                $grpnames = array();
                $grpnames[] = array('id'=>0,'name'=>_("Default Group"));
                while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['id']==$params['groupid']) {continue;}
                    $grpnames[] = $row;
                }
            }
            include_once(__DIR__ . '/load_others.php');
            break;
            
        case 'loadothergroup':
            // Load specific group courses
            $query = "SELECT ic.id,ic.name,ic.copyrights,iu.LastName,iu.FirstName,iu.email,it.userid,iu.groupid,ic.termsurl,ic.istemplate 
                      FROM imas_courses AS ic,imas_teachers AS it,imas_users AS iu  
                      WHERE it.courseid=ic.id AND it.userid=iu.id AND iu.groupid=:groupid 
                      AND iu.id<>:userid AND ic.available<4 AND ic.copyrights>-1 
                      ORDER BY iu.LastName,iu.FirstName,it.userid,ic.name";
            $courseGroupResults = $DBH->prepare($query);
            $courseGroupResults->execute(array(':groupid'=>$params['groupid'], ':userid'=>$params['userid']));
            include_once(__DIR__ . '/load_other_group.php');
            break;
    }
}
?>
