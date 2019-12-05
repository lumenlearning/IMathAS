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
                $("#desmos_previewmode_buttons").show();
                $("#desmos_preview_container").html(data);
                $("#desmos_preview_container").show();
                loadDesmos();
            },
            error: function(data) {
                console.log("Failed to get view page content for Desmos item.");
                console.log(data.responseText);
                $("#preview_button").html('Preview');
            }
        });
    });

    $("#desmos_edit_button").click(function() {
        $("#desmos_previewmode_buttons").hide();
        $("#desmos_preview_container").hide();
        $("#desmos_preview_container").empty();
        $("#desmos_edit_container").show();
    });

    $("#desmos_save_button").click(function() {
        $("#desmos_form_submit_button").trigger('click');
    });
});