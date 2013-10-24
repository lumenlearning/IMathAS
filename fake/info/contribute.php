<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
<style type="text/css">
body {
	font-family: "Trebuchet MS", "Arial", "Helvetica", sans-serif;
	padding: 15px;
	font-size: medium;
}
.paybtn {
	border: 1px solid #ccc;
	background-color: #FFEE88;
	border-radius: 3px;
	color: #000033;
	cursor: pointer;
	font-family: "Trebuchet MS", "Arial", "Helvetica", sans-serif;
	font-size: medium;
	width: 15em;
	padding: 3px;
}
.paybtn:hover {
	background-color: #FFDD88;
}
.paybtn:active {
	background-color: #FFAA88;
}
</style>
<script type="text/javascript">
function contribute() {
	var v = $("#os0").val();
	$('#paybtn').val("Taking you to PayPal...");
	$.get("paypalreturn.php?click=true&v="+v).done(function() {
		$("#theform").submit();
	});
	return false;
}
function later() {
	$.get("paypalreturn.php?later=true");
	parent.GB_hide();
}
function no() {
	$.get("paypalreturn.php?never=true");
	parent.GB_hide();
}
</script>
</head>
<body>
<p>Hi there!</p>

<p>It's been a couple weeks since you started in MyOpenMath - hope your 
class is going well!  Isn't your teacher awesome for picking free course
materials and saving you some money?</p>

<p>Unfortunately, free doesn't pay the bills, so we're hoping with all that
money you saved, you might be willing to chip in a few bucks to help
support MyOpenMath.  If you can't, that's cool, but we'd certainly appreciate it.</p>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" id="theform" >
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="KR5KKSW8WPSJU">
<table><tbody><tr><td style="padding-right: 20px">
<input type="hidden" name="on0" value="Contribution Amount">Contribution Amount<br/>
<select id="os0" name="os0">
	<option value="Bronze">Bronze $5.00 USD</option>
	<option value="Silver" <?php if (isset($_GET['t']) && $_GET['t']==1) {echo 'selected="selected";';}?>>Silver $10.00 USD</option>
	<option value="Gold">Gold $15.00 USD</option>
</select></td><td style="vertical-align: bottom"><input type="hidden" name="currency_code" value="USD"><input id="paybtn" class="paybtn" type="button" value="Contribute Now" onclick="contribute()"/></td></tr>
<tr><td></td><td><input class="paybtn" type="button" value="Not Today - Ask Again Later" onclick="later()"/></td></tr>
<tr><td></td><td><input class="paybtn" type="button" value="Not This Term, Sorry" onclick="no();"/></td></tr>
</tbody></table>
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>




</body>
</html>
