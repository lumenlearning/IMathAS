<?php 

require("../validate.php");

if (isset($teacherid)) {
	if (isset($_GET['depth'])) {
		$depth = intval($_GET['depth']);
	} else {
		$depth = 2;
	}
	$query = "SELECT itemorder FROM imas_courses WHERE id='$cid'";
	$result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	list($items,$blockcnt) = mysql_fetch_row($result);
	$items = unserialize($items);
	
	function rec($items,$curdepth,$ind) {
		global $depth,$cid;
		foreach ($items as $it) {
			if (is_array($it)) {
				if ($curdepth<$depth) {
					$url = 'https://www.myopenmath.com/bltilaunch.php?custom_view_folder='.$cid.'-'.$it['id'];
					echo $ind.$it['name'].'  <a href="'.$url.'">'.$url.'</a><br/>';
					rec($it['items'],$curdepth+1,'-&nbsp;');
				}
			}
		}
	}
	rec($items,0,'');
}
?>
