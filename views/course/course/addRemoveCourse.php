<?php
/* @var $this yii\web\View */
use app\components\AppUtility;

$this->title = 'Add-Remove';
$this->params['breadcrumbs'][] = $this->title;
?>
<!DOCTYPE html>
<html lang="en-US">
<head>

</head>
<body>

        <h3>Current Teachers</h3>

        <input type="hidden" class="course-id" value="<?php echo $cid ?>">
        <div>
            <div class="lg-col-2 pull-left select-text-margin">
                <strong>With Selected:</strong>&nbsp;&nbsp;
            </div>
            <a class='btn btn-primary addRemoveTeacherButton addButton addTeacherButton-"+nonTeacher.id+" ' onclick='removeAllAsTeacher()'>Remove as Teacher </a>
            <table class="addRemoveTable teachers" id="teach">

            </table>
        </div>

        <h3>Potential Teachers</h3>

        <div>
            <div class="lg-col-2 pull-left select-text-margin">
                <strong>With Selected:</strong> &nbsp;&nbsp;
                <table class="addRemoveTable non-teachers" id="nonTeach">
             <a class='btn btn-primary addRemoveTeacherButton removeButton removeTeacherButton-"+teacher.id+" ' onclick='addAllAsTeacher()'>Add as Teacher </a>&nbsp;&nbsp;
                </table>
            </div>

        </div>
</body>
</html>

<script type="text/javascript">
    $(document).ready(function(){
        var cid = $(".course-id").val();
        var courseTeacher = {cid: cid};
        jQuerySubmit('get-teachers', courseTeacher, 'displayTeacherSuccess');
    });

    function displayTeacherSuccess(response)
    {
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            var teachers = result.data.teachers;
            var nonTeachers = result.data.nonTeachers;

            $.each(nonTeachers, function(index, nonTeacher){
                displayNonTeacher(nonTeacher);
            });

            $.each(teachers, function(index, teacher){
                displayTeacher(teacher);
            });
        }

    }


    function displayTeacher(teacher)
    {
        var firstName = capitalizeFirstLetter(teacher.FirstName);
        var lastName = capitalizeFirstLetter(teacher.LastName);
        var teacherHtml = "";
        teacherHtml = "<tr><td><input type='checkbox' name='teacher' value='"+teacher.id+"' class='addRemoveTeacherCheckbox removeCheckbox removeTeacherCheckbox-"+teacher.id+"' > </td> <td id='convertToUpper'>"+firstName+' '+lastName+"</td><td><a href='' onclick='removeTeacher("+teacher.id+")' class='addRemoveTeacher removeTeacherLink removeTeacher-"+teacher.id+"'>Remove as Teacher</a></td></tr>";
        $('#teach').append(teacherHtml);
    }

    function displayNonTeacher(nonTeacher)
    {
        var firstName = capitalizeFirstLetter(nonTeacher.FirstName);
        var lastName = capitalizeFirstLetter(nonTeacher.LastName);
        var nonTeacherHtml = "";
        nonTeacherHtml = "<tr><td><input type='checkbox' name='nonTeacher' value='"+nonTeacher.id+"' class= 'addRemoveTeacherCheckbox addCheckbox addTeacherCheckbox-"+nonTeacher.id+"'> </td> <td id='convertToUpper'>"+firstName+' '+lastName+"</td><td><a href='' onclick='addTeacher("+nonTeacher.id+")' class='addRemoveTeacher addTeacherLink addTeacher-"+nonTeacher.id+"'>Add as Teacher</a></td></tr>";
        $('#nonTeach').append(nonTeacherHtml);
    }

    function addTeacher(userId)
    {
        var cid = $(".course-id").val();
        jQuerySubmit('add-teacher-ajax',{cid:cid, userId:userId },'addTeacherSuccess');
    }

    function addTeacherSuccess(response)
    {
        console.log(response);
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            window.location = "add-remove-course?cid="+cid;
        }
    }


    function removeTeacher(userId)
    {
        var cid = $(".course-id").val();
        jQuerySubmit('remove-teacher-ajax',{cid:cid, userId:userId },'removeTeacherSuccess');
    }

    function removeTeacherSuccess(response)
    {
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            window.location = "add-remove-course?cid="+cid;
        }
    }

    function addAllAsTeacher()
    {
        var cid = $(".course-id").val();
        var nonTeachers = [];
        $("input:checkbox[name=nonTeacher]:checked").each(function()
        {
            nonTeachers.push($(this).val());
        });
        jQuerySubmit('add-all-as-teacher-ajax',{'usersId':JSON.stringify(nonTeachers), 'cid':cid},'addAllAsTeacherSuccess');
    }

    function addAllAsTeacherSuccess(response)
    {
        var cid = $(".course-id").val();
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            window.location = "add-remove-course?cid="+cid;
        }
    }

    function removeAllAsTeacher()
    {
        var cid = $(".course-id").val();
        var teachers = [];
        $("input:checkbox[name=teacher]:checked").each(function()
        {
            teachers.push($(this).val());
        });
        jQuerySubmit('remove-all-as-teacher-ajax',{'usersId':JSON.stringify(teachers), 'cid':cid},'removeAllAsTeacherSuccess');
    }
    function removeAllAsTeacherSuccess(response)
    {
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            var cid = $(".course-id").val();
            window.location = "add-remove-course?cid="+cid;
        }
    }

</script>