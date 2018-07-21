<?php
require_once(__DIR__ . "/../includes/sanitize.php");

$dbusername = getenv('DB_USERNAME');
$dbpassword = getenv('DB_PASSWORD');
$dbserver = getenv('DB_SERVER');
$dbname = "sagecell";

try {
	$DBH = new PDO("mysql:host=$dbserver;dbname=$dbname", $dbusername, $dbpassword);
	$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$GLOBALS["DBH"] = $DBH;
} catch(PDOException $e) {
	die("<p>Could not connect to database: <b>" . $e->getMessage() . "</b></p></div></body></html>");
}

if (empty($_POST['oauth_consumer_key'])) {
	echo 'Error: provide a key (any key - your domain name is suggested)';
	exit;
}
if (empty($_POST['resource_link_id'])) {
	echo 'Resource link id is required';
	exit;
}
$ltirole = strtolower($_REQUEST['roles']);
if (strpos($ltirole,'instructor')!== false || strpos($ltirole,'administrator')!== false) {
	$ltirole = 'instructor';
} else {
	$ltirole = 'learner';
}

$domain = $_POST['oauth_consumer_key'];
$linkid = $_POST['resource_link_id'];

if (isset($_GET['save'])) {
	$code = $_POST['code'];
	if ($_GET['id']!='new') {
		$id = intval($_GET['id']);
		$stm = $DBH->prepare("UPDATE celldata SET code=:code WHERE id=:id");
		$stm->execute(array(':code'=>$code, ':id'=>$id));
	} else {
		$query = "INSERT INTO celldata (domain,linkid,code) VALUES ";
		$query .= "(:domain, :linkid, :code)";
		$stm = $DBH->prepare($query);
		$stm->execute(array(':domain'=>$domain, ':linkid'=>$linkid, ':code'=>$code));
		$id = $DBH->lastInsertId();
	}
}

$stm = $DBH->prepare("SELECT code,id FROM celldata WHERE domain=:domain AND linkid=:linkid");
$stm->execute(array(':domain'=>$domain, ':linkid'=>$linkid));
if ($stm->rowCount()>0) {
	list($code, $id) = $stm->fetch(PDO::FETCH_NUM);
} else {
	$code = "a = solve(x^2+3*x-2==0,x); print a\nplot(x^2+3*x-2,(-4,4))";
	$id = 'new';
}

?>
<html>
<head>
<title>Sage Cell</title>
<script type="text/javascript" src="https://sagecell.sagemath.org/static/embedded_sagecell.js"></script>

<!-- Initialize each cell -->
<script>
$(function() {
        sagecell.makeSagecell({
            inputLocation: '#sagecell1',
            replaceOutput: true,
            code: "<?php echo Sanitize::encodeStringForJavascript($code); ?>",
            hide: ['messages', 'computationID', 'files', 'sageMode',
                   'editorToggle', 'sessionTitle', 'done', "permalink"],
            evalButtonText: 'Evaluate'
        });
});
</script>
</head>

<body>
<div id="sagecell1"></div>
<p>This web page provides a gateway to the <a href="http://www.sagemath.org/">Sage</a> computer algebra system.
See <a href="http://www.sagemath.org/eval.html#Calculus/Basics/Differential">some examples</a> of what it can do, or
see the <a href="http://www.sagemath.org/doc/reference/index.html">reference manual</a> for help with syntax.</p>
<?php
if ($ltirole == 'instructor') {
	echo '<p>Instructors can set default code which will show when students view this placement of this tool.  Students will be able to modify the code, but not save their changes.</p>';
	echo '<form method="post" action="sagecelllti.php?save=true&amp;id='.Sanitize::onlyInt($id).'" onsubmit="getcode()">';
	echo '<input type="submit" value="Save Default Code"/>';
	echo '<input type="hidden" name="oauth_consumer_key" value="'.Sanitize::encodeStringForDisplay($domain).'"/>';
	echo '<input type="hidden" name="resource_link_id" value="'.Sanitize::encodeStringForDisplay($linkid).'"/>';
	echo '<input type="hidden" name="roles" value="'.Sanitize::encodeStringForDisplay($ltirole).'"/>';
	echo '<textarea style="visibility:hidden;position:absolute;" name="code" id="savecode"></textarea>';
	echo '</form>';
	echo '<script type="text/javascript">
	      function getcode() {
		var c = document.getElementById("sagecell1").getElementsByTagName("textarea")[0].value;
		document.getElementById("savecode").value = c;
	      }</script>';
}
?>
</body>
</html>
