$(document).ready(function() {
    $("#desmos_preview_button").click(function() {
        $("#desmos_preview_button").html('Loading preview...');
        let formData = $("#desmos_item").serialize();
        $.ajax({
            type: "POST",
            url: "/desmos/views/view.php?mode=preview",
            data: formData,
            success: function(data) {
                $("#desmos_edit_container").hide();
                $("#desmos_preview_button").html("Preview");
                $("#desmos_preview_content").html(data);
                $("div.mainbody").css("background-color", "#F9FAFB");
                $("div.breadcrumb").css("background-color", "#F9FAFB");
                $("#desmos_preview_container").show();
                $('link[title=lux]')[0].disabled=true;
                $('html, body').animate({ scrollTop: 0 }, "slow");
                loadDesmos();
            },
            error: function(data) {
                console.log("Failed to get view page content for Desmos item.");
                console.log(data.responseText);
                $("#preview_button").html('Preview');
            }
        });
    });

    $("#desmos_return_to_edit_button").click(function() {
        $("#desmos_preview_container").hide();
        $("#desmos_preview_content").empty();
        $("div.mainbody").css("background-color", "#FFFFFF");
        $("div.breadcrumb").css("background-color", "#FFFFFF");
        $("#desmos_edit_container").show();
        $('link[title=lux]')[0].disabled=false;
    });

    $("#desmos_save_button").click(function() {
        $("#desmos_form_submit_button").trigger('click');
    });
});
