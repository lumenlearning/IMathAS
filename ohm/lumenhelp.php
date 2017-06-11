<?php
require("../init.php");
include("../header.php");
$pagetitle = "Lumen Help";

$stm = $DBH->prepare("SELECT FirstName FROM imas_users WHERE id=:id");
$stm->execute(array(':id'=>$userid));
$userfirstname = $stm->fetchColumn(0);

$stm = $DBH->prepare("SELECT LastName FROM imas_users WHERE id=:id");
$stm->execute(array(':id'=>$userid));
$userlastname = $stm->fetchColumn(0);

$stm = $DBH->prepare("SELECT email FROM imas_users WHERE id=:id");
$stm->execute(array(':id'=>$userid));
$useremail = $stm->fetchColumn(0);
?>

<head>
  <link href="../themes/lumenhelpstyles.css" rel="stylesheet" type="text/css">
</head>

<?php
echo "<h2>LuMOM Help</h2>
<div class=\"helpsection\">
  <h3>Contact Us</h3>
  <p>Contact Lumen's support team by filling out the form below.</p>
  <div class=\"contactform\">
    <form method=\"post\" name=\"emailform\" action=\"contactformsubmit.php\">
      <input type=\"hidden\" name=\"firstname\" value=$userfirstname>
      <input type=\"hidden\" name=\"lastname\" value=$userlastname>
      <input type=\"hidden\" name=\"email\" value=$useremail>
      <input type=\"hidden\" name=\"id\" value=$userid>
      <label for=\"subject\">Subject: </label><input id=\"mailsubject\" type=\"text\" name=\"subject\"><br>
      <label for=\"message\">Message: </label><textarea name=\"message\"></textarea><br>
      <input type=\"submit\" value=\"Submit\">
    </form>
  </div>"
  ?>

  <div class="helpsection">
    <h3>Community Forum</h3>
    <p>Look for solutions on <a href="#">MyOpenMath's community forum</a>.</p>
  </div>

  <div class="helpsection">
    <h3>Preexisting Knowledge Base</h3>
    <p>Explore the <a href="#">preexisting knowledge base</a>.</p>
  </div>

  <div class="helpsection">
    <h3>Documentation</h3>
    <p>Read the <a href="../help.php">MyOpenMath documentation</a>.</p>
  </div>
<?php
require("../footer.php");
?>
