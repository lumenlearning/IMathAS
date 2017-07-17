<?php
require("../init_without_validate.php");
$addr = "https://www.wamap.org/course/quickdrill.php";

//DB $query = "SELECT i_qs.description,i_qs.id FROM imas_questionset as i_qs JOIN imas_library_items as ili ON ili.qsetid=i_qs.id ";
//DB $query .= "WHERE ili.libid=1270 ORDER BY i_qs.description";
//DB $result = mysql_query($query) or die("Query failed: $query: " . mysql_error());
$query = "SELECT i_qs.description,i_qs.id FROM imas_questionset as i_qs JOIN imas_library_items as ili ON ili.qsetid=i_qs.id ";
$query .= "WHERE ili.libid=1270 ORDER BY i_qs.description";
$stm = $DBH->query($query);
$optionlist = '';
//DB while ($row = mysql_fetch_row($result)) {
while ($row = $stm->fetch(PDO::FETCH_NUM)) {
	$optionlist .= '<option value="'.$row[1].'">'.$row[0].'</option>';
}
?>
<html>
<head>
 <title>Quick Drill Link Generator</title>
 <script type="text/javascript">
 var baseaddr = "<?php echo $addr;?>";
 function makelink() {
	 var id = document.getElementById("qid").options[document.getElementById("qid").selectedIndex].value;
	 if (id=='') {alert("Question ID is required"); return false;}
	 var sa = document.getElementById("sa").options[document.getElementById("sa").selectedIndex].value;
	 var mode = document.getElementById("type").options[document.getElementById("type").selectedIndex].value;
	 var val = document.getElementById("val").value;
	 if (mode!='none' && val=='') { alert("need to specify N"); return false;}
	 var url = baseaddr + '?id=' + id + '&sa='+sa;
	 if (mode != 'none') {
		 url += '&'+mode+'='+val;
	 }
	 url += '&public=true';
	 document.getElementById("output").innerHTML = "<p>URL to use: "+url+"</p><p><a href=\""+url+"\" target=\"_blank\">Try it</a></p>";
 }
 </script>
 </head>
 <body>
 <h2>Quick Drill Link Generator</h2>
 <table border=0>
 <tr><td>Question to use:</td><td><select id="qid"><?php echo Sanitize::encodeStringForDisplay($optionlist); ?></select></td></tr>
 <tr><td>Show answer option:</td><td><select id="sa">
 	<option value="0">Show score - reshow question with answer if wrong</option>
	<option value="1">Show score - don't reshow question w answer if wrong</option>
	<option value="4">Show score - don't show answer - make student redo same version if missed</option>
	<option value="2">Don't show score at all</option>
	<option value="3">Flash Cards Style: don't show score, but use Show Answer button</option>
	</select></td></tr>
 <tr><td>Behavior:</td><td><select id="type">
 	<option value="none">Just keep asking questions forever</option>
	<option value="n">Do N questions, then stop</option>
	<option value="nc">Do until N questions are correct, then stop</option>
	<option value="t">Do as many questions as possible in N seconds</option>
	</select><br/>
	Where N = <input type="text" size="4" id="val"/></td></tr>
</table>

<input type="button" value="Generate Link" onclick="makelink()"/>

<div id="output"></div>
</body>
</html>
