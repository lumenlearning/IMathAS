/**
 * Set the student payment status for a group via an AJAX call.
 *
 * @param {integer} groupId The group ID.
 */
function updateStudentPaymentType(groupId) {
    var newPaymentType = $('#student_payment_type option:selected').val();
    var newPaymentTypeDesc = $('#student_payment_type option:selected').text();

    var userInput = confirm(`You are about to change the student payment type for ALL of the courses for this group!
\nNew payment type: ${newPaymentTypeDesc}`);

    if (userInput) {
        var request = $.ajax({
            type: 'POST',
            url: imasroot + '/ohm/assessments/settings_ajax.php',
            data: {
                'paymentType': newPaymentType,
                'groupId': groupId,
                'action': 'setGroupPaymentType'
            },
            success: function (res) {
                $("span#student_payment_update_message").text("✓ Payment setting saved!")
                    .css({
                      'background-color': 'green',
                      'color': 'white',
                      'display': 'inline',
                      'margin-top': '1em',
                      'padding': '0.5em',
                      'border-radius': '5px',
                    });
            },
            error: function (err) {
                $("span#student_payment_update_message").text("✗ Failed to save new payment setting!")
                    .css({
                      'background-color': '#d61616',
                      'color': 'white',
                      'display': 'inline',
                      'margin-top': '1em',
                      'padding': '0.5em',
                      'border-radius': '5px',
                    });
            }
        });
    }
}

