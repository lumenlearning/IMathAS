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
     * Serialize the form on the edit page, update the form hidden input with
     * the serialized data, and submit the preview form.
     */
    $("#desmos_preview_button").click(function() {
        $("#desmos_preview_button").html('Loading preview...');

        let formData = btoa( $("#desmos_item").serialize() );
        $("#desmos_edit_form_data").val(formData);
        enteringPreviewMode = true;
        $("#desmos_preview_form").submit();
    });

    $("#desmos_form_submit_button").click(function(e) {
        formIsSubmitting = true;
    });
});
