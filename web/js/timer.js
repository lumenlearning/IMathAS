var Timer;
var TotalSeconds;


function CreateTimer(TimerID, Time) {
    Timer = document.getElementById(TimerID);
    TotalSeconds = Time;

    UpdateTimer()
    window.setTimeout("Tick()", 1000);
}

function Tick() {
    TotalSeconds -= 1;
    UpdateTimer()
    window.setTimeout("Tick()", 1000);
}

function UpdateTimer() {
    var hour = (Math.floor(TotalSeconds/3600) < 10) ? '0'+Math.floor(TotalSeconds/3600) : Math.floor(TotalSeconds/3600);
    var min = Math.floor((TotalSeconds%3600)/60);
    var sec = ((TotalSeconds%3600)%60 < 10) ? '0'+(TotalSeconds%3600)%60 :(TotalSeconds%3600)%60 ;

    Timer.innerHTML = hour +':'+ Math.floor((TotalSeconds%3600)/60) +':' + sec;
}

function toggletimer() {
    if ($("#timerhide").text()=="[x]") {
        $("#timercontent").hide();
        $("#timerhide").text(' [Show Timer]');
        $("#timerhide").attr("title","Show Timer");
    } else {
        $("#timercontent").show();
        $("#timerhide").text("[x]");
        $("#timerhide").attr("title","Hide");
    }
}

function toggleintroshow(n) {
    var link = document.getElementById("introtoggle"+n);
    var content = document.getElementById("intropiece"+n);
    if (link.innerHTML.match("Hide")) {
        link.innerHTML = link.innerHTML.replace("Hide","Show");
        content.style.display = "none";
    } else {
        link.innerHTML = link.innerHTML.replace("Show","Hide");
        content.style.display = "block";
    }
}
function togglemainintroshow(el) {
    if ($("#intro").hasClass("hidden")) {
        $(el).html("Hide Intro/Instructions");
        $("#intro").removeClass("hidden").addClass("intro");
    } else {
        $("#intro").addClass("hidden");
        $(el).html("Show Intro/Instructions");
    }
}

$('.licensePopup').click(function(e)
{
    e.preventDefault();
    var questionId= $(".question-id").val();
    var html = '<div><p><Strong>Question License</Strong></p>' +
        '<p>Question ID '+questionId +' (Universal ID 11435814263779)</p>'  +
        '<p> This question was written by Lippman, David. This work is licensed under the<a target="Licence" href="http://www.imathas.com/communitylicense.html"> IMathAS Community License (GPL + CC-BY)</a> </p>'
        +'<p>The code that generated this question can be obtained by instructors by emailing abhishek.prajapati@tudip.com</p></div>';
    $('<div  id="dialog"></div>').appendTo('body').html(html).dialog
    ({
        modal: true, title: 'License', zIndex: 10, autoOpen: true,
        width: 'auto', resizable: false,
        closeText: "hide",
        buttons:
        {
            "Cancel": function ()
            {
                $(this).dialog('destroy').remove();
                return false;

            }
        }

    });

});