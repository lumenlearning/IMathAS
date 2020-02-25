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

        let id = $.urlParam('id');

        let formDataBeforeUnload = $('#desmos_item').serialize();
        if (formDataBeforeUnload !== formDataBeforeChanges || 0 === id) {
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
     * and redirect to the preview page.
     */
    $("#desmos_preview_button").click(function() {
        $("#desmos_preview_button").html('Loading preview...');
        let formData = $("#desmos_item").serialize();

        let courseId = Number($.urlParam('cid'));
        // Allow multiple preview tabs
        let previewId = $.urlParam('preview_id');
        if (0 === previewId) previewId = Date.now();

        $.ajax({
            type: "POST",
            url: "/course/itempreview.php?mode=store_temp_preview_data&preview_id="
              + previewId + '&cid=' + cid,
            data: {
                tempSerializedPreviewData: formData,
            },
            success: function(data) {
                enteringPreviewMode = true;
                let id = Number($.urlParam('id'));

                $(location).attr('href', '/course/itempreview.php?cid='
                    + courseId + '&type=desmos&id=' + id + '&preview_id=' + previewId);
            },
            error: function(data) {
                console.log("Failed to temporarily store serialized form data for Desmos interactive.");
                console.log(data.responseText);
                $("#desmos_preview_button").html('Preview');
            }
        });
    });

    /*
     * Return to the edit page. OHM will re-populate the form using session data.
     */
    $("#js-return-to-edit").click(function() {
        let id = Number($.urlParam('id'));
        let idParam = 0 === id ? '' : '&id=' + id;
        let courseId = Number($.urlParam('cid'));
        let previewId = $.urlParam('preview_id');
        $(location).attr('href', '/course/itemadd.php?mode=returning_from_preview&cid='
          + courseId + '&type=desmos' + idParam + '&preview_id=' + previewId);
    });

    $("#desmos_form_submit_button").click(function(e) {
        formIsSubmitting = true;
    });
});
