$(document).ready(function() {
    let formIsSubmitting = false;
    let formDataBeforeChanges = $('#desmos_item').serialize();

    window.onbeforeunload = function () {
        if (formIsSubmitting) {
            formIsSubmitting = false;
            return;
        }

        let formDataBeforeUnload = $('#desmos_item').serialize();
        if (formDataBeforeUnload !== formDataBeforeChanges) {
            return 'Data has been modified. Are you sure you want to abandon changes?';
        }
    };

    /*
     * Helper function to get a URL query parameter.
     */
    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        return results[1] || 0;
    };

    /*
     * Serialize the form on the edit page, save in the user's PHP session,
     * and redirect to the view page in preview mode.
     */
    $("#desmos_preview_button").click(function() {
        $("#desmos_preview_button").html('Loading preview...');
        let formData = $("#desmos_item").serialize();
        $.ajax({
            type: "POST",
            url: "/course/itempreview.php?mode=store_temp_preview_data",
            data: {
                tempSerializedPreviewData: formData,
            },
            success: function(data) {
                let courseId = Number($.urlParam('cid'));
                $(location).attr('href', '/course/itempreview.php?cid=' + courseId + '&type=desmos');
            },
            error: function(data) {
                console.log("Failed to temporarily store serialized form data for Desmos interactive.");
                console.log(data.responseText);
                $("#preview_button").html('Preview');
            }
        });
    });

    /*
     * Unserialize form data from browser local storage, populate
     */
    $("#desmos_return_to_edit_button").click(function() {
        $("#desmos_preview_container").hide();
        $("#desmos_preview_content").empty();
        $("div.mainbody").css("background-color", "#FFFFFF");
        $("div.breadcrumb").css("background-color", "#FFFFFF");
        $("#desmos_edit_container").show();
        $('link[title=lux]')[0].disabled=false;
    });

    $("#desmos_form_submit_button").click(function(e) {
        formIsSubmitting = true;
    });
});
