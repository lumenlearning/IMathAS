<?php

require("../init.php");
if ($myrights<20) {exit;}

$isadmin = (isset($_GET['cid']) && $_GET['cid']=='admin' && $myrights==100);

if (!empty($_POST['checked'])) {
    $data = array_map('intval', $_POST['checked']);
    $ph = Sanitize::generateQueryPlaceholders($data);
    
    $query = "DELETE imas_questionerrors FROM imas_questionerrors 
        JOIN imas_questionset ON imas_questionerrors.qsetid=imas_questionset.id
        WHERE imas_questionerrors.qsetid IN ($ph)";
    if (!$isadmin) {
        $data[] = $userid;
        $query .= " AND imas_questionset.ownerid=?";
    }
    $stm = $DBH->prepare($query);
    $stm->execute($data);
}

if ($isadmin) {
    $query = 'SELECT * FROM imas_questionerrors GROUP BY qsetid,error ORDER BY qsetid';

    $stm = $DBH->query($query);
} else {
    $query = 'SELECT iqe.* FROM imas_questionerrors AS iqe
        JOIN imas_questionset AS iqs ON iqe.qsetid=iqs.id
        WHERE iqs.ownerid=? GROUP BY iqe.qsetid,iqe.error ORDER BY iqe.qsetid';

    $stm = $DBH->prepare($query);
    $stm->execute([$userid]);
}

require('../header.php');

echo '<h2>'._('Question Errors').'</h2>';
echo '<form method=post>';
echo '<p>'._('With selected:').'<button type=submit>'._('Clear error').'</button></p>';
echo '<ul>';
$lastqsetid = 0;
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    $qsetid = intval($row['qsetid']);
    if ($qsetid != $lastqsetid) {
        if ($lastqsetid > 0) { echo '</ul></li>'; }
        echo '<li><input type=checkbox name="checked[]" value="'.$qsetid.'"> ';
        echo 'Question <a target="_blank" href="../course/moddataset.php?cid=0&id='.$qsetid.'">#'.$qsetid.'</a>';
        echo '<ul>';
    }
    echo '<li><a target="_blank" href="../course/testquestion2.php?cid=0&qsetid='.$qsetid.'&seed='.intval($row['seed']).'">';
    echo 'Seed '.intval($row['seed']).'</a>: ' . Sanitize::encodeStringForDisplay($row['error']).'</li>';
    $lastqsetid = $qsetid;
}
if ($lastqsetid > 0) { echo '</ul></li>'; }
echo '</ul>';
echo '</form>';

require('../footer.php');


