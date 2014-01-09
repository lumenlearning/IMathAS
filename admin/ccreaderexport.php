<?php
//IMathAS: Common Catridge v1.1 Export
//(c) 2011 David Lippman

require("../validate.php");

$cid = intval($_GET['cid']);
if (!isset($teacherid)) {
	echo 'You must be a teacher to access this page';
	exit;
}

$pagetitle = "CC Export";
$loadmathfilter = 1;
$loadgraphfilter = 1;
	
require("../header.php");
echo "<div class=breadcrumb>$breadcrumbbase <a href=\"../course/course.php?cid=$cid\">$coursename</a> &gt; Common Cartridge Export</div>\n";

if (!isset($CFG['GEN']['noimathasexportfornonadmins']) || $myrights>=75) {
	echo '<div class="cpmid"><a href="exportitems.php?cid='.$cid.'">Export for another IMathAS system or as a backup for this system</a></div>';
}

$path = realpath("../course/files");

if (isset($_GET['delete'])) {
	unlink($path.'/CCEXPORT'.$cid.'.imscc');
	echo "export file deleted";
} else if (isset($_GET['create'])) {
	require("../includes/filehandler.php");
	$linktype = $_GET['type'];
	$iteminfo = array();
	$query = "SELECT id,itemtype,typeid FROM imas_items WHERE courseid=$cid";
	$r = mysql_query($query) or die("Query failed : " . mysql_error());
	while ($row = mysql_fetch_row($r)) {
		$iteminfo[$row[0]] = array($row[1],$row[2]);
	}
	
	$query = "SELECT itemorder FROM imas_courses WHERE id=$cid";
	$r = mysql_query($query) or die("Query failed : " . mysql_error());
	$items = unserialize(mysql_result($r,0,0));
	
	$newdir = $path . '/CCEXPORT'.$cid;
	mkdir($newdir);
	
	$manifestorg = '';
	$manifestres = array();
	
	$imgcnt = 1;
	if (substr($mathimgurl,0,4)!='http') {
		$addmathabs = true;
	} else {
		$addmathabs = false;
	}
	
	$htmldir = '';
	$filedir = '';
	if ($linktype=='canvas') {
		mkdir($newdir.'/wiki_content');
		mkdir($newdir.'/web_resources');
		$htmldir = 'wiki_content/';
		$filedir = 'web_resources/';
	}
	
	function filtercapture($str,&$res) {
		global $newdir,$imgcnt,$imasroot,$addmathabs,$mathimgurl,$filedir,$linktype;
		$str = forcefiltermath($str);
		$str = forcefiltergraph($str);
		$graphfiles = getgraphfilenames($str);
		foreach ($graphfiles as $f) {
			copy("../filter/graph/imgs/$f",$newdir.'/'.$filedir.$f);
			$resitem =  '<resource href="'.$filedir.$f.'" identifier="RESwebcontentImage'.$imgcnt.'" type="webcontent">'."\n";
			$resitem .= '  <file href="'.$filedir.$f.'" />'."\n";
			$resitem .= '</resource>';
			$res[] = $resitem;
			$imgcnt++;
		}
		if ($linktype=='canvas') {
			$str = str_replace($imasroot.'/filter/graph/imgs/','$IMS_CC_FILEBASE$/',$str); 
		} else {
			$str = str_replace($imasroot.'/filter/graph/imgs/','',$str); 
		}
		if ($addmathabs) {
			$str = str_replace($mathimgurl,'http://'. $_SERVER['HTTP_HOST']. $mathimgurl, $str);
		}
		return $str;
	}
	
	$ccnt = 1;
	$module_meta = '<?xml version="1.0" encoding="UTF-8"?>
		<modules xsi:schemaLocation="http://canvas.instructure.com/xsd/cccv1p0 http://canvas.instructure.com/xsd/cccv1p0.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://canvas.instructure.com/xsd/cccv1p0">
		<module identifier="imported">
		<title>Imported Content</title>
		<items>';
		
	
		
	
	function getorg($it,$parent,&$res,$ind) {
		global $iteminfo,$newdir,$installname,$urlmode,$linktype,$urlmode,$imasroot,$ccnt,$module_meta,$htmldir,$filedir,$cid;
		$out = '';
		
		foreach ($it as $k=>$item) {
			$canvout = '';
			if (is_array($item)) {
				if (strlen($ind)/2<3) {
					if ($linktype=='canvas') {
						$canvout .= '<item identifier="RESBLOCK'.$item['id'].'">'."\n";
						$canvout .= '<content_type>ContextExternalTool</content_type>';
						$canvout .= '<workflow_state>active</workflow_state>'."\n";
						$canvout .= '<title>'.htmlentities($item['name']).'</title>'."\n";
						$canvout .= '<identifierref>RESbltiimathas</identifierref>'."\n";
						$canvout .= '<url>'. $urlmode . $_SERVER['HTTP_HOST'] . $imasroot . '/bltilaunch.php?custom_view_folder='.$cid.'-'.$item['id'].'</url>'."\n";
						$canvout .= '<new_tab>false</new_tab>'."\n";
						$canvout .= "<position>$ccnt</position> <indent>".(strlen($ind)/2 - 1)."</indent> </item>";
						$ccnt++;
						$module_meta .= $canvout;
					} else {
						
						$fp = fopen($newdir.'/blti'.$item['id'].'.xml','w');
						fwrite($fp,'<cartridge_basiclti_link xmlns="http://www.imsglobal.org/xsd/imslticc_v1p0" xmlns:blti="http://www.imsglobal.org/xsd/imsbasiclti_v1p0" xmlns:lticm ="http://www.imsglobal.org/xsd/imslticm_v1p0" xmlns:lticp ="http://www.imsglobal.org/xsd/imslticp_v1p0">');
						fwrite($fp,'<blti:title>'.htmlentities($item['name']).'</blti:title>');
						fwrite($fp,'<blti:description></blti:description>');
						if ($linktype=='url') {
							$urladd = '?custom_view_folder='.$cid.'-'.$item['id'];
						} else {
							fwrite($fp,'<blti:custom><lticm:property name="view_folder">'.$cid.'-'.$item['id'].'</lticm:property></blti:custom>');
							$urladd = '';
						}
						fwrite($fp,'<blti:launch_url>http://' . $_SERVER['HTTP_HOST'] . $imasroot . '/bltilaunch.php'.$urladd.'</blti:launch_url>');
						if ($urlmode == 'https://') {fwrite($fp,'<blti:secure_launch_url>https://' . $_SERVER['HTTP_HOST'] . $imasroot . '/bltilaunch.php</blti:secure_launch_url>');}
						fwrite($fp,'<blti:vendor><lticp:code>IMathAS</lticp:code><lticp:name>'.$installname.'</lticp:name></blti:vendor>');
						fwrite($fp,'</cartridge_basiclti_link>');
						fclose($fp);
						$resitem =  '<resource identifier="RESBLOCK'.$item['id'].'" type="imsbasiclti_xmlv1p0">'."\n";
						$resitem .= '  <file href="blti'.$item['id'].'.xml" />'."\n";
						$resitem .= '</resource>';
						$res[] = $resitem;
					}
					$out .= $ind.'<item identifier="BLOCK'.$item['id'].'" identifierref="RESBLOCK'.$item['id'].'">'."\n";
					$out .= $ind.'  <title>'.htmlentities($item['name']).'</title>'."\n";
					$out .= $ind.'</item>'."\n";
					$out .= $ind.getorg($item['items'],$parent.'-'.($k+1),$res,$ind.'  ');
				}
			} 
		}
		return $out;
	}
	if ($linktype=='canvas') {
		$manifestres[] = '<resource identifier="coursesettings1" href="course_settings/syllabus.html" type="associatedcontent/imscc_xmlv1p1/learning-application-resource" intendeduse="syllabus">
		      <file href="course_settings/syllabus.html"/>
		      <file href="course_settings/course_settings.xml"/>
		      <file href="course_settings/assignment_groups.xml"/>
		      <file href="course_settings/module_meta.xml"/>
		    </resource>';
    	}
	$manifestorg = getorg($items,'0',$manifestres,'  ');
	
	if ($linktype=='canvas') {
		$module_meta .= '</items>  </module> </modules>';
		
		$fp = fopen($newdir.'/bltiimathas.xml','w');
		fwrite($fp,'<cartridge_basiclti_link xmlns="http://www.imsglobal.org/xsd/imslticc_v1p0" xmlns:blti="http://www.imsglobal.org/xsd/imsbasiclti_v1p0" xmlns:lticm ="http://www.imsglobal.org/xsd/imslticm_v1p0" xmlns:lticp ="http://www.imsglobal.org/xsd/imslticp_v1p0">');
		fwrite($fp,'<blti:title>'.htmlentities($installname).'</blti:title>');
		fwrite($fp,'<blti:description>Math Assessment</blti:description>');
		fwrite($fp,'<blti:vendor><lticp:code>IMathAS</lticp:code><lticp:name>'.$installname.'</lticp:name></blti:vendor>');
		fwrite($fp,'<blti:extensions platform="canvas.instructure.com">');
		fwrite($fp,' <lticm:property name="privacy_level">public</lticm:property>');
		fwrite($fp,' <lticm:property name="domain">'.$_SERVER['HTTP_HOST'].'</lticm:property>');
		fwrite($fp,'</cartridge_basiclti_link>');
		fclose($fp);
		$resitem =  '<resource identifier="RESbltiimathas" type="imsbasiclti_xmlv1p0">'."\n";
		$resitem .= '  <file href="bltiimathas.xml" />'."\n";
		$resitem .= '</resource>';
		$manifestres[] = $resitem;
		mkdir($newdir.'/non_cc_assessments');
    		mkdir($newdir.'/course_settings');
    		$fp = fopen($newdir.'/course_settings/syllabus.html','w');
    		fwrite($fp,'<html><body> </body></html>');
    		fclose($fp);
    		$fp = fopen($newdir.'/course_settings/assignment_groups.xml','w');
    		fwrite($fp,'<?xml version="1.0" encoding="UTF-8"?>
			<assignmentGroups xsi:schemaLocation="http://canvas.instructure.com/xsd/cccv1p0 http://canvas.instructure.com/xsd/cccv1p0.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://canvas.instructure.com/xsd/cccv1p0">
			  <assignmentGroup identifier="assngroup">
			    <title>Assignments</title>
			  </assignmentGroup>
			</assignmentGroups>');
		fclose($fp);
		$fp = fopen($newdir.'/course_settings/module_meta.xml','w');
		fwrite($fp,$module_meta);
		fclose($fp);
		$fp = fopen($newdir.'/course_settings/course_settings.xml','w');
		fwrite($fp,'<?xml version="1.0" encoding="UTF-8"?>
<course xsi:schemaLocation="http://canvas.instructure.com/xsd/cccv1p0 http://canvas.instructure.com/xsd/cccv1p0.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" identifier="coursesettings1" xmlns="http://canvas.instructure.com/xsd/cccv1p0">
  <title>imp test</title>
</course>
');
		fclose($fp);
	}
	
	$fp = fopen($newdir.'/imsmanifest.xml','w');
	fwrite($fp,'<?xml version="1.0" encoding="UTF-8" ?>'."\n");
	fwrite($fp,'<manifest identifier="imathas'.$cid.'" xmlns="http://www.imsglobal.org/xsd/imsccv1p1/imscp_v1p1" xmlns:lom="http://ltsc.ieee.org/xsd/imsccv1p1/LOM/resource" xmlns:lomimscc="http://ltsc.ieee.org/xsd/imsccv1p1/LOM/manifest" >'."\n");
	fwrite($fp,'<metadata>'."\n".'<schema>IMS Common Cartridge</schema>'."\n".'<schemaversion>1.1.0</schemaversion> '."\n");
	fwrite($fp, '<lomimscc:lom>
	      <lomimscc:general>
		<lomimscc:title>
		  <lomimscc:string language="en-US">Common Cartridge export of '.$cid.' from '.$installname.'</lomimscc:string>
		</lomimscc:title>
		<lomimscc:description>
		  <lomimscc:string language="en-US">Common Cartridge export of '.$cid.' from '.$installname.'</lomimscc:string>
		</lomimscc:description>
		<lomimscc:keyword>
		  <lomimscc:string language="en-US">IMathAS</lomimscc:string>
		</lomimscc:keyword>
	      </lomimscc:general>
	    </lomimscc:lom>'."\n".'</metadata>'."\n");
	fwrite($fp,'<organizations>'."\n".' <organization identifier="O_1" structure="rooted-hierarchy">'."\n".' <item identifier="I_1">'."\n");
	fwrite($fp,$manifestorg);
	fwrite($fp, ' </item>'."\n".' </organization>'."\n".'</organizations>'."\n");
	fwrite($fp,'<resources>'."\n");
	foreach($manifestres as $r) {
		fwrite($fp,$r."\n");
	}
	fwrite($fp,'</resources>'."\n");
	fwrite($fp,'</manifest>'."\n");
	fclose($fp);
	
	// increase script timeout value
	ini_set('max_execution_time', 300);
	
	// create object
	$zip = new ZipArchive();
	
	// open archive 
	if ($zip->open($path.'/CCEXPORT'.$cid.'.zip', ZIPARCHIVE::OVERWRITE) !== TRUE) {
	    die ("Could not open archive");
	}
	
	/*// initialize an iterator
	// pass it the directory to be processed
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../course/files/CCEXPORT'.$cid.'/'));
	
	// iterate over the directory
	// add each file found to the archive
	foreach ($iterator as $key=>$value) {
		if (basename($key)=='.' || basename($key)=='..') { continue;}
		$zip->addFile(realpath($key), basename($key)) or die ("ERROR: Could not add file: $key");        
	}
	*/
	function addFolderToZip($dir, $zipArchive, $zipdir = ''){
	    if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
	
		    //Add the directory
		    if(!empty($zipdir)) $zipArchive->addEmptyDir($zipdir);
		  
		    // Loop through all the files
		    while (($file = readdir($dh)) !== false) {
		  
			//If it's a folder, run the function again!
			if(!is_file($dir . $file)){
			    // Skip parent and root directories
			    if( ($file !== ".") && ($file !== "..")){
				addFolderToZip($dir . $file . "/", $zipArchive, $zipdir . $file . "/");
			    }
			  
			}else{
			    // Add the files
			    $zipArchive->addFile($dir . $file, $zipdir . $file);
			  
			}
		    }
		}
	    }
	} 
	addFolderToZip($newdir.'/',$zip);
	
	// close and save archive
	$zip->close();
	//rename($path.'/CCEXPORT'.$cid.'.zip',$path.'/CCEXPORT'.$cid.'.imscc');
	echo "Archive created successfully.";    
	
	function rrmdir($path) {
	  if (is_file($path) || is_link($path)) {
	    unlink($path);
	  }
	  elseif (is_dir($path)) {
	    if ($d = opendir($path)) {
	      while (($entry = readdir($d)) !== false) {
		if ($entry == '.' || $entry == '..') continue;
		$entry_path = $path .DIRECTORY_SEPARATOR. $entry;
		rrmdir($entry_path);
	      }
	      closedir($d);
	    }
	    rmdir($path);
	  }
	 }
 
	rrmdir($newdir);
	
	echo "<br/><a href=\"$imasroot/course/files/CCEXPORT$cid.zip\">Download</a><br/>";
	echo "Once downloaded, keep things clean and <a href=\"ccexport.php?cid=$cid&delete=true\">Delete</a> the export file off the server.";
} else {
	echo '<h2>Common Cartridge Export</h2>';
	echo '<p>This feature will allow you to export a v1.1 compliant IMS Common Cartridge export of your course, which can ';
	echo 'then be loaded into other Learning Management Systems that support this standard.  Inline text, web links, ';
	echo 'course files, and forums will all transfer reasonably well, but be aware that any math exported will call back to this server for display.</p>';
	echo '<p>Since LMSs cannot support the type of assessment that this system ';
	echo 'does, assessments are exported as LTI (learning tools interoperability) placements back to this system.  Not all LMSs ';
	echo 'support this standard yet, so your assessments may not transfer.  If they do, you will need to set up the LTI tool on your LMS ';
	echo 'to work with this system by supplying an LTI key and secret.  If this system and your LMS have domain credentials set up, you may not have to do ';
	echo 'anything.  Otherwise, you can use the LTI secret you set in your course settings, along with the key placein_###_0 (if you want students ';
	echo 'to create an account on this system) or placein_###_1 (if you want students to only be able to log in through the LMS), where ### is ';
	echo 'replaced with your course key.  <b>Important:</b> The key form placein_###_1 is necessary if you want grades from '.$installname.' to be ';
	echo 'reported back to the LMS automatically.  ';
	echo 'If you do not see the LTI key setting in your course settings, then your system administrator does ';
	echo 'not have LTI enabled on your system, and you cannot use this feature.</p>';
	if ($enablebasiclti==false) {
		echo '<p style="color:red">Note: Your system does not currenltly have LTI enabled.  Contact your system administrator</p>';
	}
	echo "<p><a href=\"ccreaderexport.php?cid=$cid&create=true&type=custom\">Create CC Export</a> with LTI placements as custom fields (works in BlackBoard)</p>";
	echo "<p><a href=\"ccreaderexport.php?cid=$cid&create=true&type=url\">Create CC Export</a> with LTI placements in URLs (works in Moodle)</p>";
	echo "<p><a href=\"ccreaderexport.php?cid=$cid&create=true&type=canvas\">Create CC+custom Export</a> (works in Canvas)</p>";
	
}
require("../footer.php");

?>
