<?php

require("../init.php");

if (isset($teacherid)) {
	if (isset($_GET['depth'])) {
		$depth = intval($_GET['depth']);
	} else {
		$depth = 2;
	}
	//DB $query = "SELECT itemorder FROM imas_courses WHERE id='$cid'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	//DB list($items,$blockcnt) = mysql_fetch_row($result);
	$stm = $DBH->prepare("SELECT itemorder FROM imas_courses WHERE id=:id");
	$stm->execute(array(':id'=>$cid));
	list($items,$blockcnt) = $stm->fetch(PDO::FETCH_NUM);
	$items = unserialize($items);

	function rec($items,$curdepth,$ind) {
		global $depth,$cid;
		foreach ($items as $it) {
			if (is_array($it)) {
				if ($curdepth<$depth) {
					$url = 'https://www.myopenmath.com/bltilaunch.php?custom_view_folder='.$cid.'-'.$it['id'];
					echo '<tr><td>'.$it['name'].'</td><td><a href="'.$url.'">'.$url.'</a></td></tr>';
					rec($it['items'],$curdepth+1,'-&nbsp;');
				}
			}
		}
	}
	echo '<table><tbody>';
	rec($items,0,'');
	echo '</tbody></table>';
}
?>
