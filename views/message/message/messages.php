<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\AppUtility;

$this->title = 'Messages';
$this->params['breadcrumbs'][] = $this->title;
?>
<!DOCTYPE html>
<html>
<head>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css"
          href="<?php echo AppUtility::getHomeURL() ?>js/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <script type="text/javascript" src="<?php echo AppUtility::getHomeURL() ?>js/general.js?ver=012115"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" charset="utf8"
            src="<?php echo AppUtility::getHomeURL() ?>js/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8"
            src="<?php echo AppUtility::getHomeURL() ?>js/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
</head>
<body>
<div>
    <?php echo $this->render('../../instructor/instructor/_toolbarTeacher'); ?>
    <input type="hidden" class="send-msg" value="<?php echo $course->id ?>">
    <input type="hidden" class="send-userId" value="<?php echo $course->ownerid ?>">
</div>
<div class="message-container">
<div><p><a href="<?php echo AppUtility::getURLFromHome('message', 'message/send-message?cid='.$course->id.'&userid='.$course->ownerid); ?>" class="btn btn-primary ">Send New Message</a>
    | <a href="">Limit to Tagged</a> | <a href="">Sent Messages</a>
    | <a class="btn btn-primary ">Picture</a></p>
</div>
<div>
    <p><span class="col-md-1"><b>Filter By</b></span>
        <span class="col-md-3">
        <select name="seluid" class="dropdown form-control" id="seluid">
            <option value="0">All Courses</option>

        </select>

        </span> <span class="col-md-1"><b>To</b></span>

        <span class="col-md-3">
        <select name="seluid" class="dropdown form-control" id="seluid">
            <option value="0">Select a user..</option>
            <?php foreach ($users as $user) { ?>
                <option
                    value="<?php echo $user['id'] ?>"><?php echo $user['FirstName'] . " " . $user['LastName']; ?></option>
            <?php } ?>
        </select>

        </span></p>
</div><br><br>
    <div>
        <p>check: <a href="">None</a>
            <a href="">All</a>
            With Selected:
            <a class="btn btn-primary ">Mark as Unread</a>
            <a class="btn btn-primary ">Mark as Read</a>
            <a class="btn btn-primary ">Delete</a>
    </div>

    <table id="message-table display-message-table" class="message-table display-message-table">
        <thead>
        <tr>
            <th></th>
            <th>Message</th>
            <th>Replied</th>
            <th>Flag</th>
            <th>From</th>
            <th>Course</th>
            <th>Sent</th>
        </tr>
        </thead>
        <tbody class="message-table-body">
        </tbody>
    </table>
</div>
</body>
</html>
<script type="text/javascript">
    $(document).ready(function () {
        var cid = $(".send-msg").val();
        var userId = $(".send-userId").val();
        alert(userId);
        var allMessage = {cid: cid, userId: userId};
        jQuerySubmit('display-message-ajax',allMessage, 'showMessageSuccess');
    });

    function showMessageSuccess(response)
    {console.log(response);
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            var messageData = result.messageData;
            showMessage(messageData);
        }
    }

    function showMessage(messageData)
    {
        var html = "";
        $.each(messageData, function(index, messageData){
            html += "<tr> <td><input type='checkbox' name='msg-check' value='"+messageData.id+"' class='message-checkbox-"+messageData.id+"' ></td>";
            html += "<td>"+messageData.title+"</td>";
            html += "<td>"+messageData.replied+"</td>";
            html += "<td>abc</td>";
            html += "<td>"+messageData.msgFrom+"</td>";
            html += "<td>"+messageData.courseName+"</td>";
            html += "<td>"+messageData.msgDate+"</td>";
        });
        $(".message-table-body").append(html);
        $('.display-message-table').DataTable();
    }
</script>