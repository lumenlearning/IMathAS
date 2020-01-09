$(document).ready(function() {
    let formDataBeforeChanges = $('#desmos_item').serialize();

    window.onbeforeunload = function () {
        let formDataBeforeUnload = $('#desmos_item').serialize();
        if (formDataBeforeUnload !== formDataBeforeChanges) {
            return 'Data has been modified. Are you sure you want to abandon changes?';
        }
    };

    /**
     * Validate form data.
     *
     * @returns {boolean} True if form data is valid. False if not.
     */
    function isValidDesmosFormData() {
        let title = $('input#title').val();
        if ('' === title.trim()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if a URL contains an item ID.
     *
     * @param url The URL to check.
     * @returns {boolean} True if an item ID was found. False if not.
     */
    function containsItemId(url) {
        let params = getUrlArguments(url);
        for (i = 0; i < params.length; i++) {
            if ('id=' === params[i].substring(0, 3)) {
                return true;
            }
        }
        return false;
    }

    function getUrlArguments(url) {
        let idx = url.indexOf('?');
        if (-1 === idx) {
            return [];
        }

        let queryString = url.substring(idx + 1);
        return queryString.split('&');
    }

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

    $("#desmos_form_submit_button").click(function(e) {
        let formAction = $('#desmos_item').attr('action');

        if (false === isValidDesmosFormData()) {
            alert('Title cannot be empty.');
            e.preventDefault();
            return;
        }

        // As far as I know, "id=" in query parameters is the only way to
        // detect if we're adding or editing a Desmos item.
        if (!containsItemId(formAction)) {
            // If we're creating a new Desmos item, return to the course page.
            // This prevents the user from continuing to submit the same
            // form to create more of the same item.
            return;
        }

        $.ajax({
            type: "POST",
            url: formAction,
            data: $('#desmos_item').serialize(),
            beforeSend: function () {
                $('#desmos_save_status')
                  .stop()
                  .removeClass('desmos_save_status_success')
                  .removeClass('desmos_save_status_failed')
                  .addClass('desmos_save_status_saving')
                  .text('Saving...')
                  .css('opacity', '1.0')
                  .css('display', 'inline-block');
            },
            success: function (data) {
                formDataBeforeChanges = $('#desmos_item').serialize();
                $('#desmos_save_status')
                  .removeClass('desmos_save_status_saving')
                  .addClass('desmos_save_status_success')
                  .text('Saved!')
                  .fadeOut(3000);
            },
            error: function () {
                $('#desmos_save_status')
                  .removeClass('desmos_save_status_saving')
                  .addClass('desmos_save_status_failed')
                  .text('Failed to save!');
            }
        });
        e.preventDefault();
    });
});
