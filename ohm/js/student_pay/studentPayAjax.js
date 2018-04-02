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
                $("span#student_payment_update_message").text("Saved!")
                    .css({'color': 'green', 'font-weight': 'normal', 'display': 'inline'})
                    .fadeOut(10000);
            },
            error: function (err) {
                $("span#student_payment_update_message").text("Failed to save new payment setting!")
                    .css({'color': 'red', 'font-weight': 'bold', 'display': 'inline'})
                    .fadeOut(60000);
            }
        });
    }
}

