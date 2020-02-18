$(document).ready(function() {
    let formIsSubmitting = false;
    let enteringPreviewMode = false;
    let formDataBeforeChanges = $('#desmos_item').serialize();

    window.onbeforeunload = function () {
        if (formIsSubmitting || enteringPreviewMode) {
            enteringPreviewMode = false;
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
        if (null === results) return 0;

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
                enteringPreviewMode = true;
                let id = Number($.urlParam('id'));
                let courseId = Number($.urlParam('cid'));
                $(location).attr('href', '/course/itempreview.php?cid=' + courseId + '&type=desmos&id=' + id);
            },
            error: function(data) {
                console.log("Failed to temporarily store serialized form data for Desmos interactive.");
                console.log(data.responseText);
                $("#desmos_preview_button").html('Preview');
            }
        });
    });

    /*
     * Unserialize form data from browser local storage, populate
     */
    $("#desmos_return_to_edit_button").click(function() {
        let id = Number($.urlParam('id'));
        let idParam = 0 === id ? '' : '&id=' + id;
        let courseId = Number($.urlParam('cid'));
        $(location).attr('href', '/course/itemadd.php?mode=returning_from_preview&cid='
          + courseId + '&type=desmos' + idParam);
    });

    $("#desmos_form_submit_button").click(function(e) {
        formIsSubmitting = true;
    });
});
