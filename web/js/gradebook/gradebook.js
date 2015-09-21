$(document).ready(function () {
    var courseId = $(".course-info").val();
    var userId = $(".user-info").val();
    var showpics = $("#showpics").val();
    selectCheckBox();
    studentLock();
        var table = $('.gradebook-table').DataTable( {
            scrollY: "300px",
            scrollX: true,
            scrollCollapse: true,
            "paginate": false,
            "ordering":false,
            paging: false
        });
    new $fn.dataTable.FixedColumns( table );
    var data = {courseId: courseId, userId: userId};
    jQuerySubmit('fetch-gradebook-data-ajax', data, 'fetchDataSuccess');
    var x = document.cookie;
    var s = x.split(/:/);
    conditionalColor('gradebook-table',0,low,high);

});
var data;
var showPics = 0;
var GradebookData;
function fetchDataSuccess(response){
    var result = JSON.parse(response);
    GradebookData = result.data.gradebook;
}
function selectCheckBox() {
    $('.check-all').click(function () {
        $('.gradebook-table-body input:checkbox').each(function () {
            $(this).prop('checked', true);
        })
    });

    $('.uncheck-all').click(function () {
        $('.gradebook-table-body input:checkbox').each(function () {
            $(this).prop('checked', false);
        })
    });
}

function highlightrow(el) {
    el.setAttribute("lastclass",el.className);
    el.className = "highlight";
}
function unhighlightrow(el) {
    el.className = el.getAttribute("lastclass");
}

function studentLock() {
    $('#lock-btn').click(function (e) {
        var course_id = $("#course-id").val();
        var markArray = [];
        var dataArray = [];
        $('.gradebook-table input[name = "checked"]:checked').each(function () {
            markArray.push($(this).val());
            for(var i=1;i < GradebookData.length-1;i++){
                if(GradebookData[i][4][0] == $(this).val())
                {
                    dataArray.push(GradebookData[i][0][0]);
                }
            }
        });

        if (markArray.length != 0) {
            var html = '<div><p>Are you SURE you want to lock the selected students out of the course?</p></div><p>';
            $.each(dataArray, function (index, studentData) {
                html += studentData + '<br>';
            });
            var cancelUrl = $(this).attr('href');
            e.preventDefault();
            $('<div id="dialog"></div>').appendTo('body').html(html).dialog({
                modal: true, title: 'Message', zIndex: 10000, autoOpen: true,
                width: 'auto', resizable: false,
                closeText: "hide",
                buttons: {
                    "Yes, Lock Out Student": function () {
                        $('.gradebook-table input[name = "checked"]:checked').each(function () {
                            $(this).prop('checked', false);
                        });
                        $(this).dialog("close");
                        var data = {checkedStudents: markArray, courseId: course_id};
                        jQuerySubmit('mark-lock-ajax', data, 'markLockSuccess');
                        return true;
                    },
                    "Cancel": function () {

                        $(this).dialog('destroy').remove();
                        $('.gradebook-table input[name = "checked"]:checked').each(function () {
                            $(this).prop('checked', false);
                        });
                        return false;
                    }
                },
                close: function (event, ui) {
                    $(this).remove();
                }
            });
        }
        else {
            var msg = "Select atleast one student.";
            CommonPopUp(msg);
        }
    });
}
function markLockSuccess(response){
    location.reload();
}

function studentUnEnroll() {
    var markArray = createStudentList();
    if (markArray.length != 0) {
        document.getElementById("checked-student").value = markArray;
        document.forms["un-enroll-form"].submit();
    } else {
        var msg = "Select at least one student to unenroll.";
        CommonPopUp(msg);
    }
}

function createStudentList(appendId, e){
    var markArray = [];
    $('.gradebook-table input[name = "checked"]:checked').each(function () {
        markArray.push($(this).val());
    });
    if (markArray.length != 0) {
        appendId.value = markArray;
    } else {
        var msg = "Select atleast one student.";
        CommonPopUp(msg);
        e.preventDefault();
    }
}

function studentMessage() {
    var markArray = createStudentList();
    if (markArray.length != 0) {
        document.getElementById("message-id").value = markArray;
        document.forms["gradebook-message-form"].submit();
    } else {
        var msg = "Select at least one student to send Message.";
        CommonPopUp(msg);
    }
}

function studentEmail() {
    var markArray = createStudentList();
    if (markArray.length != 0) {
        document.getElementById("student-id").value = markArray;
        document.forms["gradebook-email-form"].submit();
    } else {
        var msg = "Select at least one student to send Email.";
        CommonPopUp(msg);
    }
}

function studentCopyEmail() {
    var markArray = createStudentList();
    if (markArray.length != 0) {
        document.getElementById("email-id").value = markArray;
        document.forms["copy-emails-form"].submit();
    } else {
        var msg = "Select at least one student.";
        CommonPopUp(msg);
    }
}

function teacherMakeException() {
    var markArray = createStudentList();
    if (markArray.length != 0) {
        document.getElementById("exception-id").value = markArray;
        document.forms["make-exception-form"].submit();
    } else {
        var msg = "Select at least one student to make an exception.";
        CommonPopUp(msg);
    }
}
function chgfilter() {
    var cat = document.getElementById("filtersel").value;
    //var studentId = $("#student-id").val();
    var courseId = $("#course-id").val();
    //window.location = "gradebook?cid="+courseId+"&stu=0&catfilter=" + cat;
}

function createStudentList(){
    var markArray = [];
    $('.gradebook-table input[name = "checked"]:checked').each(function () {
        markArray.push($(this).val());
    });
    return markArray;
}

 function chgexport() {
     var courseId = $("#course-id").val();
     var studentId = $("#student-id").val();
 var type = document.getElementById("exportsel").value;
 if (type==1) { toopen = '&export=true';}
 if (type==2) { toopen =  '&emailgb=me';}
 	if (type==3) { toopen = '&emailgb=ask';}
 	if (type==0) { return false;}
  	//window.location = toopen;
     window.location = "gradebook-export?cid="+courseId+"&stu="+studentId+toopen;
 }

function updateColors(el) {
    console.log(el);
    //alert('a');
    var courseId = $("#course-id").val();
    if (el.value==0) {
        var tds=document.getElementById("gradebook-table").getElementsByTagName("td");
        for (var i=0;i<tds.length;i++) {
            tds[i].style.backgroundColor = "";
        }
    } else {
        var s = el.value.split(/:/);
        conditionalColor("gradebook-table",0,s[0],s[1]);
    }
    document.cookie = 'colorize-'+ courseId +'='+el.value;
}

function conditionalColor(table,type,low,high) {

var tbl = document.getElementById(table);
if (type==0) {  //instr gb view
    var poss = [];
    var startat = 2;
    var ths = tbl.getElementsByTagName("thead")[0].getElementsByTagName("th");
    for (var i=0;i<ths.length;i++) {
        if (k = ths[i].innerHTML.match(/(\d+)(&nbsp;|\u00a0)pts/)) {
            poss[i] = k[1]*1;
            if (poss[i]==0) {poss[i]=.0000001;}
        } else {
            poss[i] = 100;
            if(ths[i].className.match(/nocolorize/)) {
                startat++;
            }
        }
    }
    var trs = tbl.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
    for (var j=0;j<trs.length;j++) {
        var tds = trs[j].getElementsByTagName("td");
        for (var i=startat;i<tds.length;i++) {
            if (low==-1) {
                if (tds[i].className.match("isact")) {
                    tds[i].style.backgroundColor = "#99ff99";
                } else {
                    tds[i].style.backgroundColor = "#ffffff";
                }
            } else {
                if (tds[i].innerText) {
                    var v = tds[i].innerText;
                } else {
                    var v = tds[i].textContent;
                }
                if (k = v.match(/\(([\d\.]+)%\)/)) {
                    var perc = k[1]/100;
                } else if (k = v.match(/([\d\.]+)\/(\d+)/)) {
                    if (k[2]==0) { var perc = 0;} else { var perc= k[1]/k[2];}
                } else {
                    v = v.replace(/[^\d\.]/g,"");
                    var perc = v/poss[i];
                }

                if (perc<low/100) {
                    tds[i].style.backgroundColor = "#ff9999";

                } else if (perc>high/100) {
                    tds[i].style.backgroundColor = "#99ff99";
                } else {
                    tds[i].style.backgroundColor = "#ffffff";
                }
            }
        }
    }
} else {
    var trs = tbl.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
    for (var j=0;j<trs.length;j++) {
        var tds = trs[j].getElementsByTagName("td");
        if (tds[1].innerText) {
            var poss = tds[1].innerText.replace(/[^\d\.]/g,"");
            var v = tds[2].innerText.replace(/[^\d\.]/g,"");
        } else {
            var poss = tds[1].textContent.replace(/[^\d\.]/g,"");
            var v = tds[2].textContent.replace(/[^\d\.]/g,"");
        }
        if (v/poss<low/100) {
            tds[2].style.backgroundColor = "#ff6666";

        } else if (v/poss>high/100) {
            tds[2].style.backgroundColor = "#66ff66";
        } else {
            tds[2].style.backgroundColor = "#ffffff";

        }

    }
}

}

function hi(a){
    alert(a);
}