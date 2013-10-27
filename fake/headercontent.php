<?php 
if (!isset($flexwidth) && ($coursetheme=='otbs_fw.css' || $coursetheme=='otbs.css')) {
	$smallheaderlogo = '<img src="'.$imasroot.'/img/collapse.gif"/>';
?>
<div id="headercontent">
<div id="headerrightlinks">
<?php 
$usernameinheader = true;
echo '<span id="myname">'.$userfullname.'</span><br/>';
echo "<a href=\"$imasroot/index.php\">Home</a> | ";
if ($myrights > 5) {
	echo "<a href=\"#\" onclick=\"GB_show('Account Settings','$imasroot/forms.php?action=chguserinfo&greybox=true',800,500)\">Account Settings</a> | ";
}
if (isset($teacherid)) {
	echo "<a href=\"$imasroot/help.php?section=coursemanagement\">Help</a> ";
} else {
	echo "<a href=\"$imasroot/help.php?section=usingimas\">Help</a> ";
}
echo "| <a href=\"$imasroot/actions.php?action=logout\">Log Out</a>";

?>
</div>
<div id="headerbarlogo"><img src="<?php echo $imasroot;?>/img/mom.png" /> provided by <a href="http://www.lumenlearning.com"><img style="vertical-align: -30%" src="<?php echo $imasroot;?>/img/lumensm.png"/></a></div>

</div>
<?php
}
?>
