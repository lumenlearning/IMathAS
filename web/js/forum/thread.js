$(document).ready(function ()
{
    var forumid= $('#forumid').val();
    var ShowRedFlagRow = -1;
    $("#show-all-link").hide();
    jQuerySubmit('get-thread-ajax',{forumid: forumid,ShowRedFlagRow:ShowRedFlagRow },'threadSuccess');
    limitToTagShow();
});

//$(document).ready(function ()
//{
//    var forumid= $('#forumid').val();
//    jQuerySubmit('get-thread-ajax',{forumid: forumid },'threadSuccess');
//});


//function threadSuccess(response)
//{
//    var count;
//    response = JSON.parse(response);
//    var fid = $('#forumid').val();
//    var courseId = $('#course-id').val();
//    if (response.status == 0)
//    {
//
//        var threads = response.data;
//
//        var html = "";
//        $.each(threads, function (index, thread)
//        {
//            if(fid == thread.forumiddata)
//            {
//                count =0;
//                $.each(threads,function (index,data)
//                {
//                        if(thread.threadId == data.threadId)
//                        {
//                            count++;
//                        }
//                });
//                count--;
//                    if(thread.parent == 0)
//                    {
//                            html += "<tr> <td><a href='post?courseid="+courseId+"&threadid="+thread.threadId+"'>" +(thread.subject) +"</a> "+ thread.name+" <img src='/img/flagempty.gif'  onclick='changeImage(this,"+false+"," + thread.threadId + ")' ><a href='move-thread?forumId="+thread.forumiddata+"&courseId="+courseId+"&threadId="+thread.threadId+"'>Move</a> <a href='modify-post?forumId="+thread.forumiddata+"&courseId="+courseId+"&threadId="+thread.threadId+"'>Modify</a><a href='#' name='tabs' data-var='"+thread.threadId+"' class='mark-remove'> Remove </a></td> ";
//                            html += "<td>" + count + "</td>";
//                            html += "<td>" + thread.views + "</td>";
//                            html += "<td>" + thread.postdate + "</td>";
//                    }
//            }
//        });
//
//        $(".forum-table-body").append(html);
//        $('.forum-table').DataTable();
//
//
//    }
//    else if (result.status == -1) {
//        $('#forum-table').hide;
//    }
//
//    $("a[name=tabs]").on("click", function () {
//        var threadsid = $(this).attr("data-var");
//       var html = '<div><p>Are you sure? This will remove your thread.</p></div>';
//        $('<div id="dialog"></div>').appendTo('body').html(html).dialog({
//            modal: true, title: 'Message', zIndex: 10000, autoOpen: true,
//            width: 'auto', resizable: false,
//            closeText: "hide",
//            buttons: {
//                "Cancel": function () {
//                    $(this).dialog('destroy').remove();
//                    return false;
//                },
//                "confirm": function () {
//                    $(this).dialog("close");
//                    var threadId = threadsid;
//                    jQuerySubmit('mark-as-remove-ajax', {threadId:threadId}, 'markAsRemoveSuccess');
//                    return true;
//                }
//            },
//            close: function (event, ui) {
//                $(this).remove();
//            }
//
//        });
//
//    });
//}
    function markAsRemoveSuccess(response) {
        var forumid = $("#forumid").val();
        var courseid = $("#course-id").val();
        var result = JSON.parse(response);
        if(result.status == 0)
        {
            window.location = "thread?cid="+courseid+"&forumid="+forumid;
        }

    }



function limitToTagShow() {

    $("#limit-to-tag-link").click(function () {
        $(".forum-table-body").empty();
        $("#limit-to-tag-link").hide();
        $("#limit-to-new-link").hide();
        $("#show-all-link").show();
        var ShowRedFlagRow = 1;
        var forumid= $('#forumid').val();
        var thread = {forumid: forumid , ShowRedFlagRow: ShowRedFlagRow};
        jQuerySubmit('get-thread-ajax',thread,'threadSuccess');

    });
    $("#show-all-link").click(function () {
        $(".forum-table-body").empty();
        $("#limit-to-tag-link").show();
        $("#show-all-link").hide();
        $("#limit-to-new-link").show();
        ShowRedFlagRow = 0;

        var forumid= $('#forumid').val();
        var thread = {forumid: forumid , ShowRedFlagRow: ShowRedFlagRow};
        jQuerySubmit('get-thread-ajax',thread,'threadSuccess');

    });
}

