<?php
 if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']=='https'))  {
 	 $urlmode = 'https://';
 } else {
 	 $urlmode = 'http://';
 }
  error_reporting(0);

/*  function stripslashes_deep($value) {
	return (is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value));
  }
  if (get_magic_quotes_gpc()) {
   $_GET    = array_map('stripslashes_deep', $_GET);
   $_POST  = array_map('stripslashes_deep', $_POST);
   $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
  }
  */
if (isset($_GET['getxml'])) {
	echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
  <cartridge_basiclti_link xmlns="http://www.imsglobal.org/xsd/imslticc_v1p0"
      xmlns:blti = "http://www.imsglobal.org/xsd/imsbasiclti_v1p0"
      xmlns:lticm ="http://www.imsglobal.org/xsd/imslticm_v1p0"
      xmlns:lticp ="http://www.imsglobal.org/xsd/imslticp_v1p0"
      xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation = "http://www.imsglobal.org/xsd/imslticc_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticc_v1p0.xsd
      http://www.imsglobal.org/xsd/imsbasiclti_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imsbasiclti_v1p0.xsd
      http://www.imsglobal.org/xsd/imslticm_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticm_v1p0.xsd
      http://www.imsglobal.org/xsd/imslticp_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticp_v1p0.xsd">


      <blti:title>CC License Generator</blti:title>
<blti:description>Generates a Creative Commons License Statement</blti:description>
<blti:extensions platform="canvas.instructure.com">
  <lticm:property name="tool_id">editor_button</lticm:property>
  <lticm:property name="privacy_level">public</lticm:property>
  <lticm:options name="editor_button">
    <lticm:property name="url"><?php echo $GLOBALS['basesiteurl'] . '/lti/ccattribution.php'; ?></lticm:property>
    <lticm:property name="icon_url"><?php echo Sanitize::fullUrl($urlmode . $_SERVER['HTTP_HOST'] . str_replace('.php', '.png', $_SERVER['PHP_SELF']));?></lticm:property>
    <lticm:property name="text">CC License Generator</lticm:property>
    <lticm:property name="selection_width">700</lticm:property>
    <lticm:property name="selection_height">450</lticm:property>
  </lticm:options>
</blti:extensions>
<blti:icon><?php echo Sanitize::fullUrl($urlmode . $_SERVER['HTTP_HOST'] . str_replace('.php', '.png', $_SERVER['PHP_SELF'])); ?></blti:icon>

      <cartridge_bundle identifierref="BLTI001_Bundle"/>
      <cartridge_icon identifierref="BLTI001_Icon"/>
  </cartridge_basiclti_link>
  <?php
  exit;
}

$dbusername = getenv('DB_USERNAME');
$dbpassword = getenv('DB_PASSWORD');
$dbserver = getenv('DB_SERVER');
$dbname = "ltidata";
$tool = "ccrel";
try {
	$DBH = new PDO("mysql:host=$dbserver;dbname=$dbname", $dbusername, $dbpassword);
	$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	// global $DBH;
	$GLOBALS["DBH"] = $DBH;
} catch(PDOException $e) {
	die("<p>Could not connect to database: <b>" . $e->getMessage() . "</b></p></div></body></html>");
}
//DB $db = mysqli_connect($dbserver,$dbusername, $dbpassword, $dbname);

if (isset($_GET['url'])) {
	$key = substr($_GET['url'],-32);
	if (preg_match('/^[\w\d]{32}$/',$key)) {
		//DB $result = $db->query("SELECT data FROM ltidata WHERE tool='$tool' AND datakey='$key'");
		//DB $row = $result->fetch_row();
		$stm = $DBH->prepare("SELECT data FROM ltidata WHERE tool=:tool AND datakey=:key");
		$stm->execute(array(':tool'=>$tool, ':key'=>$key));
		$row = $stm->fetch(PDO::FETCH_NUM);
		$html = $row[0];
		header('Content-type: application/json');
		echo '{ "version":"1.0", "type":"rich", "width":500, "height":500, "html":"'.str_replace('"','\\"',$html).'" }';
		//unlink($key.'.txt.');
		//DB $db->query("DELETE FROM ltidata WHERE tool='$tool' AND datakey='$key'");
		$stm = $DBH->prepare("DELETE FROM ltidata WHERE tool=:tool AND datakey=:key");
		$stm->execute(array(':tool'=>$tool, ':key'=>$key));
		exit;
	}
}

$userid = intval($_POST['custom_canvas_user_id']);
$courseid = intval($_POST['custom_canvas_course_id']);
$consumerkey = $_POST['oauth_consumer_key'];


$licenses = array('cc-by'=>'Attribution',
		'cc-by-sa'=>'Attribution Share-Alike',
		'cc-by-sa-nc'=>'Attribution Non-Commercial Share-Alike',
		'cc-by-nc'=>'Attribution Non-Commercial',
		'cc0'=>'CC0 / Public Domain');

$licensesvid = array(
		'c'=>'Non-open Copyrighted',
		'cc-by'=>'Attribution',
		'cc-by-sa'=>'Attribution Share-Alike',
		'cc-by-nd'=>'Attribution No-Derivatives',
		'cc-by-sa-nc'=>'Attribution Non-Commercial Share-Alike',
		'cc-by-nc'=>'Attribution Non-Commercial',
		'cc-by-nc-nd'=>'Attribution Non-Commercial No-Derivatives',
		'cc0'=>'CC0 / Public Domain');

if (isset($_POST['license'])) {
	$_POST  = array_map('htmlentities', $_POST);

	$licenselinks = array('cc-by'=>'http://creativecommons.org/licenses/by/3.0',
		'cc-by-sa'=>'http://creativecommons.org/licenses/by-sa/3.0',
		'cc-by-nd'=>'http://creativecommons.org/licenses/by-nd/3.0',
		'cc-by-sa-nc'=>'http://creativecommons.org/licenses/by-nc-sa/3.0',
		'cc-by-nc-nd'=>'http://creativecommons.org/licenses/by-nc-nd/3.0',
		'cc-by-nc'=>'http://creativecommons.org/licenses/by-nc/3.0',
		'cc0'=>'http://creativecommons.org/publicdomain/zero/1.0/');
	$licenseimgs = array('cc-by'=>'https://i.creativecommons.org/l/by/3.0/80x15.png',
		'cc-by-sa'=>'https://i.creativecommons.org/l/by-sa/3.0/80x15.png',
		'cc-by-sa-nc'=>'https://i.creativecommons.org/l/by-nc-sa/3.0/80x15.png',
		'cc-by-nc'=>'https://i.creativecommons.org/l/by-nc/3.0/80x15.png',
		'cc0'=>'https://i.creativecommons.org/p/zero/1.0/80x15.png');


	$licused = array();

	$html = '<hr/><div style="font-size:x-small">The content of this page is licensed under a ';
	$html .= '<a rel="license" href="'.$licenselinks[$_POST['license']].'">';
	$html .= 'Creative Commons '.$licenses[$_POST['license']].' License</a> ';
	$html .= 'except for any elements that may be licensed differently.  The content of this page includes:<ul>';

	$licused[] = $_POST['license'];

	$cnt = 0;
	for ($i=0;$i<55;$i++) {
		if (isset($_POST['itemtype'.$i])) {

			$type = $_POST['itemtype'.$i];
			$thishtml = '<li>';
			if ($type=='orig' && $_POST['creator'.$i]!='[Creator]') {
				$thishtml .= 'Original content contributed by ';
				$thishtml .= $_POST['creator'.$i];
				if ($_POST['org'.$i]!='[Org]') {
					$thishtml .= ' of '.$_POST['org'.$i];
				}
				if ($_POST['project'.$i]!='[Project]') {
					$thishtml .= ' to '.$_POST['project'.$i];
				}
			} else if ($type=='origspec' && $_POST['creator'.$i]!='[Creator]' && $_POST['item'.$i]!='[Content Item]') {
				$thishtml .= $_POST['item'.$i];
				$thishtml .= ' is original content contributed by ';
				$thishtml .= $_POST['creator'.$i];
				if ($_POST['org'.$i]!='[Org]') {
					$thishtml .= ' of '.$_POST['org'.$i];
				}
				if ($_POST['project'.$i]!='[Project]') {
					$thishtml .= ' to '.$_POST['project'.$i];
				}
			} else if ($type=='cc' && $_POST['creator'.$i]!='[Creator]') {
				if ($_POST['url'.$i]!='[URL]') {
					$thishtml .= '<a href="'.$_POST['url'.$i].'">Content</a> ';
				} else {
					$thishtml .= 'Content ';
				}
				$thishtml .= 'created by ';
				$thishtml .= $_POST['creator'.$i];
				if ($_POST['org'.$i]!='[Org]') {
					$thishtml .= ' of '.$_POST['org'.$i];
				}
				if ($_POST['project'.$i]!='[Project]') {
					$thishtml .= ' for '.$_POST['project'.$i];
				}

				$thishtml .= ' under a <a rel="license" href="'.$licenselinks[$_POST['license'.$i]].'">';
				$thishtml .= 'Creative Commons '.$licenses[$_POST['license'.$i]].' License</a>';
				if (!in_array($_POST['license'.$i], $licused)) { $licused[] = $_POST['license'.$i] ;}
			} else if ($type=='ccspec' && $_POST['creator'.$i]!='[Creator]' && $_POST['item'.$i]!='[Content Item]') {
				if ($_POST['url'.$i]!='[URL]') {
					$thishtml .= '<a href="'.$_POST['url'.$i].'">'.$_POST['item'.$i].'</a>';
				} else {
					$thishtml .= $_POST['item'.$i];
				}
				$thishtml .= ' was created by ';
				$thishtml .= $_POST['creator'.$i];
				if ($_POST['org'.$i]!='[Org]') {
					$thishtml .= ' of '.$_POST['org'.$i];
				}
				if ($_POST['project'.$i]!='[Project]') {
					$thishtml .= ' for '.$_POST['project'.$i];
				}

				$thishtml .= ' under a <a rel="license" href="'.$licenselinks[$_POST['license'.$i]].'">';
				$thishtml .= 'Creative Commons '.$licenses[$_POST['license'.$i]].' License</a>';
				if (!in_array($_POST['license'.$i], $licused)) { $licused[] = $_POST['license'.$i] ;}
			} else if ($type=='vid' && $_POST['creator'.$i]!='[Creator]' && $_POST['item'.$i]!='[Content Item]' && $_POST['terms'.$i]!='[Terms]') {
				$thishtml .= 'The video of ';
				if ($_POST['url'.$i]!='[URL]') {
					$thishtml .= '<a class="inline_disabled" href="'.$_POST['url'.$i].'">'.$_POST['item'.$i].'</a>';
				} else {
					$thishtml .= $_POST['item'.$i];
				}
				$thishtml .= ' was created by ';
				$thishtml .= $_POST['creator'.$i];
				if ($_POST['org'.$i]!='[Org]') {
					$thishtml .= ' of '.$_POST['org'.$i];
				}
				if ($_POST['project'.$i]!='[Project]') {
					$thishtml .= ' for '.$_POST['project'.$i];
				}
				if ($_POST['license'.$i]=='c') {
					$thishtml .= '.  This video is copyrighted and is not licensed under an open license';
					if ($_POST['terms'.$i]!='[Terms]') {
						$thishtml .= '. Embedded as permitted by '.$_POST['terms'.$i].'.';
					}
				} else {
					$thishtml .= ' under a <a rel="license" href="'.$licenselinks[$_POST['license'.$i]].'">';
					$thishtml .= 'Creative Commons '.$licensesvid[$_POST['license'.$i]].' License</a>';
				}

			} else if ($type=='pd' && $_POST['creator'.$i]!='[Creator]') {
				if ($_POST['url'.$i]!='[URL]') {
					$thishtml .= '<a href="'.$_POST['url'.$i].'">Public domain content</a> ';
				} else {
					$thishtml .= 'Public domain content ';
				}
				$thishtml .= $_POST['typesel'.$i].' by ';
				$thishtml .= $_POST['creator'.$i];
			} else if ($type=='pdspec' && $_POST['creator'.$i]!='[Creator]' && $_POST['item'.$i]!='[Content Item]') {
				if ($_POST['url'.$i]!='[URL]') {
					$thishtml .= '<a href="'.$_POST['url'.$i].'">'.$_POST['item'.$i].'</a>';
				} else {
					$thishtml .= $_POST['item'.$i];
				}
				$thishtml .= ' is public domain content '.$_POST['typesel'.$i].' by ';
				$thishtml .= $_POST['creator'.$i];
			}
			$thishtml .= '</li>';
			if ($thishtml != '<li></li>') {
				$html .= $thishtml;
			}
		}
	}
	$html .= '</ul>';
	if (!isset($_POST['noccimage'])) {
		$html .= '<a rel="license" href="'.$licenselinks[$_POST['license']].'">';
		$html .= '<img alt="Creative Commons License" style="border-width:0" ';
		$html .= 'src="'.$licenseimgs[$_POST['license']].'"/></a>';
	}
	if ($consumerkey=='lumen') {
		$html .= '<p>If you believe that a portion of this Open Course Framework infringes ';
		$html .= 'another\'s copyright, <a href="http://lumenlearning.com/copyright">contact us</a>.</p>';
	}
	$html .= '</div>';

	$key = md5($html);
	/*$handle = fopen("$key.txt",'w');
	if ($handle===false) {
		echo '<p>Error: unable open file for writing</p>';
	} else {
		$fwrite = fwrite($handle,$html);
		if ($fwrite === false) {
			echo '<p>Error: unable to write to file</p>';
		}
		fclose($handle);
	}
	*/
	//DB $tostore = $db->real_escape_string($html);
	//DB $query = "SELECT id FROM ltidata WHERE tool='$tool' AND datakey='$key'";
	//DB $result = $db->query($query) or die("Query failed : $query " . $db->error);
	$stm = $DBH->prepare("SELECT id FROM ltidata WHERE tool=:tool AND datakey=:key");
	$stm->execute(array(':tool'=>$tool, ':key'=>$key));
	if ($stm->rowCount()>0) {
		//DB $row = $result->fetch_row();
		$row = $stm->fetch(PDO::FETCH_NUM);
		$id = $row[0];
		//DB $query = "UPDATE ltidata SET data='$tostore' WHERE datakey='$key' AND tool='$tool'";
		//DB $db->query($query) or die("Query failed : $query " . $db->error);
		$stm = $DBH->prepare("UPDATE ltidata SET data=:data WHERE tool=:tool AND datakey=:key");
		$stm->execute(array(':data'=>$html, ':tool'=>$tool, ':key'=>$key));
	} else {
		//DB $query = "INSERT INTO ltidata (tool,datakey,data) VALUES ('$tool','$key','$tostore')";
		//DB $db->query($query) or die("Query failed : $query " . $db->error);
		$stm = $DBH->prepare("INSERT INTO ltidata (tool,datakey,data) VALUES (:tool,:key,:data)");
		$stm->execute(array(':data'=>$html, ':tool'=>$tool, ':key'=>$key));
	}

	$returnurl = Sanitize::fullUrl($_POST['returnurl']);
	$url = $GLOBALS['basesiteurl'];

	//save data
	if ($courseid!=0 && $userid!=0) {
		//DB $tostore = $db->real_escape_string(serialize($_POST));
		//DB $storekey = $db->real_escape_string("$consumerkey-$courseid-$userid");
		$tostore = serialize($_POST);
		$storekey = "$consumerkey-$courseid-$userid";
		//DB $query = "SELECT id FROM ltidata WHERE tool='$tool' AND datakey='$storekey'";
		//DB $result = $db->query($query) or die("Query failed : $query " . $db->error);
		//DB if ($result->num_rows>0) {
		$stm = $DBH->prepare("SELECT id FROM ltidata WHERE tool=:tool AND datakey=:key");
		$stm->execute(array(':tool'=>$tool, ':key'=>$key));
		if ($stm->rowCount()>0) {
			//DB $row = $result->fetch_row();
			$row = $stm->fetch(PDO::FETCH_NUM);
			$id = $row[0];
			//DB $query = "UPDATE ltidata SET data='$tostore' WHERE datakey='$storekey' AND tool='$tool'";
			//DB $db->query($query) or die("Query failed : $query " . $db->error);
			$stm = $DBH->prepare("UPDATE ltidata SET data=:data WHERE tool=:tool AND datakey=:key");
			$stm->execute(array(':data'=>$tostore, ':tool'=>$tool, ':key'=>$storekey));
		} else {
			//DB $query = "INSERT INTO ltidata (tool,datakey,data) VALUES ('$tool','$storekey','$tostore')";
			//DB $db->query($query) or die("Query failed : $query " . $db->error);
			$stm = $DBH->prepare("INSERT INTO ltidata (tool,datakey,data) VALUES (:tool,:key,:data)");
			$stm->execute(array(':data'=>$tostore, ':tool'=>$tool, ':key'=>$storekey));
		}
		/*
		$handle = fopen("cc-$courseid-$userid.txt",'w');
		if ($handle===false) {
			echo '<p>Error: unable open file for writing</p>';
		} else {
			$fwrite = fwrite($handle,serialize($_POST));
			if ($fwrite === false) {
				echo '<p>Error: unable to write to file</p>';
			}
			fclose($handle);
		}
		*/
		//echo $query;
	} else {
		//echo "$courseid, $userid";
	}
	//exit;

	header('Location: ' . $returnurl .'?embed_type=oembed&endpoint='.urlencode($url).'&url='.urlencode($url.'/'.$key));
	exit;
} else {
	$returnurl = $_POST['launch_presentation_return_url'];
	//DB $storekey = $db->real_escape_string("$consumerkey-$courseid-$userid");
	$storekey = "$consumerkey-$courseid-$userid";
	//DB $query = "SELECT data FROM ltidata WHERE tool='$tool' AND datakey='$storekey'";
	//DB $result = $db->query($query) or die("Query failed : $query " . $db->error);
	$stm = $DBH->prepare("SELECT data FROM ltidata WHERE tool=:tool AND datakey=:key");
	$stm->execute(array(':tool'=>$tool, ':key'=>$storekey));
	if ($consumerkey=='lumen') {
		$default = array('itemtype50'=>'orig','creator50'=>'Lumen Learning');
	} else {
		$default = array();
	}
	//DB if ($result->num_rows>0) {
	if ($stm->rowCount()>0) {
		//DB $row = $result->fetch_row();
		$row = $stm->fetch(PDO::FETCH_NUM);
		$d = unserialize($row[0]);
	} else {
		$d = $default;
	}
	/*
	if (file_exists("cc-$courseid-$userid.txt")) {
		$d = unserialize(file_get_contents("cc-$courseid-$userid.txt"));
	} else {
		$d = array();
	}
	*/
	$toload = json_encode($d);
	$defaultload = json_encode($default);
}
//http://screencast.com/t/wBGcQsAd

$licenseopts = '';
foreach ($licenses as $k=>$lic) {
	$licenseopts .= '<option value="'.$k.'">'.$lic.'</option>';
}

$licensevidopts = '';
foreach ($licensesvid as $k=>$lic) {
	$licensevidopts .= '<option value="'.$k.'">'.$lic.'</option>';
}


?>
<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>

<style type="text/css">
.nomark {
	list-style-type: none;
	padding-left: 40px;
	text-indent: -30px;
}
.nomark li {
	padding-bottom: .5em;
	line-height: 1.7em;
}
.in, #test {
	font-size: 1em;
	font-family: serif;
}
.in {
	padding: .15em;
	background-color: #ddd;
	border: 0;
}
.in.req, .req {
	padding: .15em;
	border: 0;
	background-color: #edd;
}
#test {
	visibility: hidden;
}
.delbtn {
	background-color: #f00;
	border-radius: 8px;
	cursor: pointer;
	color: white;
	font-weight: bold;
	padding: 0px 5px;
}

</style>
<script type="text/javascript">
var toload = <?php echo str_replace("'","\\'",$toload); ?>;
var deftoload = <?php echo str_replace("'","\\'",$defaultload); ?>;
function getwidth(v) {
	$('#test').text(v);
	return $('#test').width()+7;
}

function checkwidth(v,w) {
	$('#test').text(v);
	var tw = $('#test').width();
	return tw + 30;
}
var itemcnt = 0;
$(function() {
		for (var i=0;i<50;i++) {
			if (typeof toload["itemtype"+i]!="undefined") {
				var html = gethtml(toload,toload["itemtype"+i],itemcnt,i);
				$('#contentholder').append('<li id="li'+itemcnt+'">'+html+'</li>');

				if (toload["itemtype"+i]=="cc" || toload["itemtype"+i]=="ccspec"  || toload["itemtype"+i]=="vid") {
					$('#license'+itemcnt).val(toload["license"+i]);
				}
				if (toload["itemtype"+i]=="pd" || toload["itemtype"+i]=="pdspec") {
					$('#typesel'+itemcnt).val(toload["typesel"+i]);
				}
				itemcnt++;
			}
		}
		var eitemcnt = 50;
		for (var i=50;i<55;i++) {
			if (typeof toload["itemtype"+i]!="undefined") {
				var html = gethtml(toload,toload["itemtype"+i],eitemcnt,i);
				$('#contentholder').append('<li id="li'+eitemcnt+'">'+html+'</li>');

				if (toload["itemtype"+i]=="cc" || toload["itemtype"+i]=="ccspec"  || toload["itemtype"+i]=="vid") {
					$('#license'+eitemcnt).val(toload["license"+i]);
				}
				if (toload["itemtype"+i]=="pd" || toload["itemtype"+i]=="pdspec") {
					$('#typesel'+eitemcnt).val(toload["typesel"+i]);
				}
				eitemcnt++;
			}
		}
		if (typeof toload["license"]!="undefined") {
			$('#license').val(toload["license"]);
		}
		$(".in").each(function() {
			$(this).css({"width":getwidth($(this).val())});
			if ($(this).val().match(/^\[[\w\s]+\]$/)) {
				$(this).css({"color":"gray"});
			}
		});
		$(".in").on("focus", function(event) {
				var target = $(event.target);
				if (target.val().match(/^\[[\w\s]+\]$/)) {
					target.data("origval",target.val());
					target.css({"color":"black"});
					target.val("");
				}
		}).on("focus keydown", function(event) {
				if (event.type=='keydown') {
					var keycode = event.which;
					var valid =
						(keycode == 8 || keycode ==46)   || // delete/backspace
						(keycode > 47 && keycode < 58)   || // number keys
						keycode == 32 || keycode == 13   || // spacebar & return key(s) (if you want to allow carriage returns)
						(keycode > 64 && keycode < 91)   || // letter keys
						(keycode > 95 && keycode < 112)  || // numpad keys
						(keycode > 185 && keycode < 193) || // ;=,-./` (in order)
						(keycode > 218 && keycode < 223);   // [\]' (in order)
					if (!valid) {return;}
				}
				var target = $(event.target);
				target.stop().animate({ width:checkwidth(target.val(),target.width())},100);
		}).on("blur", function(event) {
				var target = $(event.target);
				if (target.val()=='') {
					target.val(target.data("origval"));
					target.css({"color":"gray"});
				}
				target.stop().animate({ width:getwidth(target.val())},100);
		});
});

function delitem(c) {
	if (confirm("Are you sure you want to remove this attribution?")) {
		$('#li'+c).remove();
	}
}

function clearall() {
	if (confirm("Are you sure you want to remove ALL attribution items?")) {
		$('#contentholder').empty();
		itemcnt = 0;
		$('#license').val('cc-by');

		var eitemcnt = 50;
		for (var i=50;i<55;i++) {
			if (typeof deftoload["itemtype"+i]!="undefined") {
				var html = gethtml(deftoload,deftoload["itemtype"+i],eitemcnt,i);
				$('#contentholder').append('<li id="li'+eitemcnt+'">'+html+'</li>');

				if (deftoload["itemtype"+i]=="cc" || deftoload["itemtype"+i]=="ccspec"  || deftoload["itemtype"+i]=="vid") {
					$('#license'+eitemcnt).val(deftoload["license"+i]);
				}
				if (deftoload["itemtype"+i]=="pd" || deftoload["itemtype"+i]=="pdspec") {
					$('#typesel'+eitemcnt).val(deftoload["typesel"+i]);
				}
				eitemcnt++;
			}
		}
		if (typeof deftoload["license"]!="undefined") {
			$('#license').val(deftoload["license"]);
		}
		$(".in").each(function() {
			$(this).css({"width":getwidth($(this).val())});
			if ($(this).val().match(/^\[[\w\s]+\]$/)) {
				$(this).css({"color":"gray"});
			}
		});
		$(".in").on("focus", function(event) {
				var target = $(event.target);
				if (target.val().match(/^\[[\w\s]+\]$/)) {
					target.data("origval",target.val());
					target.css({"color":"black"});
					target.val("");
				}
		}).on("focus keydown", function(event) {
				if (event.type=='keydown') {
					var keycode = event.which;
					var valid =
						(keycode == 8 || keycode ==46)   || // delete/backspace
						(keycode > 47 && keycode < 58)   || // number keys
						keycode == 32 || keycode == 13   || // spacebar & return key(s) (if you want to allow carriage returns)
						(keycode > 64 && keycode < 91)   || // letter keys
						(keycode > 95 && keycode < 112)  || // numpad keys
						(keycode > 185 && keycode < 193) || // ;=,-./` (in order)
						(keycode > 218 && keycode < 223);   // [\]' (in order)
					if (!valid) {return;}
				}
				var target = $(event.target);
				target.stop().animate({ width:checkwidth(target.val(),target.width())},100);
		}).on("blur", function(event) {
				var target = $(event.target);
				if (target.val()=='') {
					target.val(target.data("origval"));
					target.css({"color":"gray"});
				}
				target.stop().animate({ width:getwidth(target.val())},100);
		});


	}
}

var licenseopts = '<?php echo $licenseopts;?>';
var licensevidopts = '<?php echo $licensevidopts;?>';

function ifd(val,alt) {
	if (typeof val !='undefined' && val != null) {
		return val;
	} else {
		return alt;
	}
}

function gethtml(data,type,i,ir) {
	if (ir==null) {ir = i;}
	var html = '<span class="delbtn" onclick="delitem('+i+')">&ndash;</span>&nbsp;&nbsp; ';
	html += '<input type="hidden" name="itemtype'+i+'" value="'+type+'"/>';
	if (type=='orig') {
		html += 'Original content contributed by ';
		html += '<input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'of <input name="org'+i+'" class="in" type="text" value="'+ifd(data["org"+ir],"[Org]")+'"/> ';
		html += 'to <input name="project'+i+'" class="in" type="text" value="'+ifd(data["project"+ir],"[Project]")+'"/> ';
	} else if (type=='origspec') {
		html += '<input name="item'+i+'" class="in req" type="text" value="'+ifd(data["item"+ir],"[Content Item]")+'"/> ';
		html += 'is original content contributed by ';
		html += '<input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'of <input name="org'+i+'" class="in" type="text" value="'+ifd(data["org"+ir],"[Org]")+'"/> ';
		html += 'to <input name="project'+i+'" class="in" type="text" value="'+ifd(data["project"+ir],"[Project]")+'"/> ';
	} else if (type=='cc') {
		html += 'Content created by ';
		html += '<input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'of <input name="org'+i+'" class="in" type="text" value="'+ifd(data["org"+ir],"[Org]")+'"/> ';
		html += 'for <input name="project'+i+'" class="in" type="text" value="'+ifd(data["project"+ir],"[Project]")+'"/>,  ';
		html += 'originally published at <input name="url'+i+'" class="in" type="text" value="'+ifd(data["url"+ir],"[URL]")+'"/>  ';
		html += 'under a <select class="req" id="license'+i+'" name="license'+i+'">'+licenseopts+'</select> license. ';
	} else if (type=='ccspec') {
		html += '<input name="item'+i+'" class="in req" type="text" value="'+ifd(data["item"+ir],"[Content Item]")+'"/> ';
		html += 'was created by  ';
		html += '<input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'of <input name="org'+i+'" class="in" type="text" value="'+ifd(data["org"+ir],"[Org]")+'"/> ';
		html += 'for <input name="project'+i+'" class="in" type="text" value="'+ifd(data["project"+ir],"[Project]")+'"/>,  ';
		html += 'originally published at <input name="url'+i+'" class="in" type="text" value="'+ifd(data["url"+ir],"[URL]")+'"/>  ';
		html += 'under a <select class="req" id="license'+i+'" name="license'+i+'">'+licenseopts+'</select> license. ';
	} else if (type=='vid') {
		html += 'The video of ';
		html += '<input name="item'+i+'" class="in req" type="text" value="'+ifd(data["item"+ir],"[Content Item]")+'"/> ';
		html += 'was created by ';
		html += '<input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'of <input name="org'+i+'" class="in" type="text" value="'+ifd(data["org"+ir],"[Org]")+'"/> ';
		html += 'to <input name="project'+i+'" class="in" type="text" value="'+ifd(data["project"+ir],"[Project]")+'"/>  ';
		html += 'and published at <input id="url'+i+'" name="url'+i+'" class="in url req" type="text" value="'+ifd(data["url"+ir],"[URL]")+'"/>  ';
		html += 'under a <select class="req" id="license'+i+'" name="license'+i+'">'+licensevidopts+'</select> license. ';
		html += 'Embedded as permitted by <input id="terms'+i+'" name="terms'+i+'" class="in req" type="text" value="'+ifd(data["terms"+ir],"[Terms]")+'"/>. ';
	} else if (type=='pd') {
		html += 'Public domain content ';
		html += '<select id="typesel'+i+'" name="typesel'+i+'"><option value="created">created</option><option value="published">published</option></select> ';
		html += 'by <input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'found at <input name="url'+i+'" class="in" type="text" value="'+ifd(data["url"+ir],"[URL]")+'"/>  ';

	} else if (type=='pdspec') {
		html += '<input name="item'+i+'" class="in req" type="text" value="'+ifd(data["item"+ir],"[Content Item]")+'"/> ';
		html += 'is public domain content <select id="typesel'+i+'" name="typesel'+i+'"><option value="created">created</option><option value="published">published</option></select> ';
		html += 'by <input name="creator'+i+'" class="in req" type="text" value="'+ifd(data["creator"+ir],"[Creator]")+'"/> ';
		html += 'found at <input name="url'+i+'" class="in" type="text" value="'+ifd(data["url"+ir],"[URL]")+'"/>  ';
	}


	return html;
}
var licused = [];
function checkissues() {

	var missing = 0;
	$('.req').each(function() {
		if ($(this).val().match(/^\[.*\]$/)) {missing++;}
	});
	if (missing>0) {
		alert("Missing some required entries.  Please fill those in.");
		return;
	}

	if ($('#contentholder li').size()==0) {
		alert("You must include at least one attribution.");
		return;
	}

	licused.length = 0;
	$('[id^=license]').each(function() {
			var el = $(this);
			if ($.inArray(el.val(),licused)==-1) {licused.push(el.val());}
	});


	var issues = '';
	if (licused[0]=='cc0' && licused.length>1) {
		issues += 'Cannot mix Attribution-required work into a CC0 / Public Domain page. ';
	}
	if (!licused[0].match(/sa/) && ($.inArray('cc-by-sa',licused)!=-1 || $.inArray('cc-by-sa-nc',licused)!=-1)) {
		issues += 'Cannot mix ShareAlike content into a non-ShareAlike page.  ';
	}
	if ($.inArray('cc-by-sa',licused)!=-1 && $.inArray('cc-by-sa-nc',licused)!=-1) {
		issues += 'Cannot mix Attribution Share-Alike works with Attribution Share-Alike Non-Commercial works. ';
	}
	if (!licused[0].match(/nc/) && ($.inArray('cc-by-nc',licused)!=-1 || $.inArray('cc-by-sa-nc',licused)!=-1)) {
		issues += 'Cannot mix Non-Commercial works into a page without that clause.  ';
	}
	if (issues!='') {
		if (confirm("This attribution statement has licensing issues.\n"+issues+" \nSubmit Anyway?")) {
			 $('#theform').submit();
		 }
	} else {
		$('#theform').submit();
	}

}

function additem(el) {
	var type = $(el).val();
	el.selectedIndex = 0;
	if (type=='none') {return;}

	var newli = document.createElement("li");
	newli.id = 'li'+itemcnt;

	var html = gethtml({}, type, itemcnt);
	itemcnt++;

	$(newli).html(html);
	$('#contentholder').append(newli);

	$(newli).find(".in").each(function() {
			$(this).css({"width":getwidth($(this).val())});
			if ($(this).val().match(/^\[[\w\s]+\]$/)) {
				$(this).css({"color":"gray"});
			}
		});
	$(newli).find(".in").on("focus", function(event) {
				var target = $(event.target);
				if (target.val().match(/^\[[\w\s]+\]$/)) {
					target.data("origval",target.val());
					target.css({"color":"black"});
					target.val("");
				}
		}).on("focus keydown", function(event) {
				if (event.type=='keydown') {
					var keycode = event.which;
					var valid =
						(keycode == 8 || keycode ==46)   || // delete/backspace
						(keycode > 47 && keycode < 58)   || // number keys
						keycode == 32 || keycode == 13   || // spacebar & return key(s) (if you want to allow carriage returns)
						(keycode > 64 && keycode < 91)   || // letter keys
						(keycode > 95 && keycode < 112)  || // numpad keys
						(keycode > 185 && keycode < 193) || // ;=,-./` (in order)
						(keycode > 218 && keycode < 223);   // [\]' (in order)
					if (!valid) {return;}
				}
				var target = $(event.target);
				target.stop().animate({ width:checkwidth(target.val(),target.width())},100);
		}).on("blur", function(event) {
				var target = $(event.target);
				if (target.val()=='') {
					target.val(target.data("origval"));
					target.css({"color":"gray"});
				}
				target.stop().animate({ width:getwidth(target.val())},100);
		});
	$(newli).find(".url").on("keyup", function(event) {
			var target = $(event.target);
			var id = event.target.id.substring(3);
			if ($('#terms'+id).val()=='[Terms]') {

				var changed = false;
				if (target.val().match(/youtu/)) {
					$('#terms'+id).val("YouTube's Terms of Use");
					changed=true;
				} else if (target.val().match(/vimeo/)) {
					$('#terms'+id).val("Vimeo's Terms of Use");
					changed=true;
				} else if (target.val().match(/ted\.com/)) {
					$('#terms'+id).val("Ted's Terms of Use");
					$('#license'+id).val("cc-by-nc-nd");
					changed=true;
				}
				if (changed) {
					$('#terms'+id).css({"color":"black"});
					$('#terms'+id).stop().animate({ width:getwidth($('#terms'+id).val())},100);
				}
			}
	});

}
</script>

</head>
<body>
<form method="post" action="ccattribution.php" id="theform">
Page License: <select class="req" id="license" name="license">
<?php
	foreach ($licenses as $key=>$val) {
		echo "<option value=\"" . Sanitize::encodeStringForDisplay($key) . "\">" . Sanitize::encodeStringForDisplay($val) . "</option>";
	}
?>
</select><br/>
<ul class="nomark" id="contentholder">
</ul>

<p><select id="addsel" onchange="additem(this)">
<option value="none">Add an attribution...</option>
<option value="orig">Original Content</option>
<option value="origspec">Original Content, specific item</option>
<option value="cc">CC Licensed Content</option>
<option value="ccspec">CC Licensed Content, specific item</option>
<option value="vid">Embedded Video</option>
<option value="pd">Public Domain</option>
<option value="pdspec">Public Domain, specific item</option>
</select></p>

<input type="hidden" name="returnurl" value="<?php echo Sanitize::encodeStringForDisplay($returnurl); ?>"/>
<input type="hidden" name="custom_canvas_course_id" value="<?php echo Sanitize::encodeStringForDisplay($courseid); ?>"/>
<input type="hidden" name="custom_canvas_user_id" value="<?php echo Sanitize::encodeStringForDisplay($userid); ?>"/>
<input type="hidden" name="oauth_consumer_key" value="<?php echo Sanitize::encodeStringForDisplay($consumerkey); ?>"/>
<?php if (isset($_POST['custom_no_image'])) {
	echo '<input type="hidden" name="noccimage" value="1"/>';
}?>
<p><input type="button" value="Insert" onclick="checkissues()"/> <input type="button" value="Clear All" onclick="clearall()"/></p>
<p style="font-size:80%"><i>Not all fields are required, but provide as much detail as you have.  <br/>
If an item requires special attribution requirements, add that detail after inserting the attribution statement
into your page.
</i></p>
<span id="test"></span>
</form>
</body>
</html>
