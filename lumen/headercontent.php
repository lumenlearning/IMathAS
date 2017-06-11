<?php 
if ($coursetheme=='otbsreader.css') {
	$nologo = true;
}
if (!isset($flexwidth) && ($coursetheme=='lumen_fw.css' || $coursetheme=='lumen.css')) {
	$smallheaderlogo = '<img src="'.$imasroot.'/img/collapse.gif"/>';
?>
<div id="headercontent">
<?php
$usernameinheader = true;
if (isset($userid) && $selfhasuserimg==1) {
	if ($myrights > 5) {
		echo "<a href=\"#\" onclick=\"GB_show('Account Settings','$imasroot/forms.php?action=chguserinfo&greybox=true',800,'auto')\" title=\"Account Settings\">";
	}
	if(isset($GLOBALS['CFG']['GEN']['AWSforcoursefiles']) && $GLOBALS['CFG']['GEN']['AWSforcoursefiles'] == true) {
		echo "<img src=\"{$urlmode}{$GLOBALS['AWSbucket']}.s3.amazonaws.com/cfiles/userimg_sm{$userid}.jpg\" style=\"float:right;margin-top:15px;\"/>";
	} else {
		$curdir = rtrim(dirname(__FILE__), '/\\');
		$galleryPath = "$curdir/course/files/";
		echo "<img src=\"$imasroot/course/files/userimg_sm{$userid}.jpg\" style=\"float:right;margin-top:15px;\"/>";
	}
	if ($myrights > 5) {
		echo '</a>';
	}
}
?>
<div id="headerrightlinks">
<?php 
if (isset($userid)) {
	if ($myrights > 5) {
		echo "&nbsp;<br/><a href=\"#\" onclick=\"GB_show('Account Settings','$imasroot/forms.php?action=chguserinfo&greybox=true',800,'auto')\" title=\"Account Settings\"><span id=\"myname\">$userfullname</span> <img style=\"vertical-align:top\" src=\"$imasroot/img/gears.png\"/></a><br/>";
	} else {
		echo '&nbsp;<br/><span id="myname">'.$userfullname.'</span><br/>';
	}
}

?>
</div>
<div id="headerbarlogo"><a href="<?php echo $imasroot;?>/index.php"><img src="<?php echo $imasroot;?>/lumen/lumom.png" height="50" /></a>

<span style="padding-left: 30px;">
<?php
if (isset($userid)) {
	echo "<a href=\"$imasroot/index.php\">Home</a> | ";
	echo '<a href="#" onclick="jQuery(\'#homemenu\').css(\'left\',jQuery(this).offset().left+\'px\');mopen(\'homemenu\',0)" onmouseout="mclosetime()">My Classes <img src="'.$imasroot.'/img/smdownarrow.png" style="vertical-align:middle"/></a> | ';
	
	if (isset($teacherid)) {
		echo "<a href=\"$imasroot/help.php?section=coursemanagement\">Help</a> ";
	} else {
		echo "<a href=\"$imasroot/help.php?section=usingimas\">Help</a> ";
	}
	echo "| <a href=\"$imasroot/actions.php?action=logout\">Log Out</a>";
	echo '</span>';
	echo '<div id="homemenu" class="ddmenu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()"></div>';
} else {
	echo '</span>';
}
?>

</div>

</div>
<?php
$nologo = true;
$haslogout = true;
} else if (isset($CFG['GEN']['hidedefindexmenu'])) {
	unset($CFG['GEN']['hidedefindexmenu']);
}
?>
