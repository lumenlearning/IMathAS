var request;

/**
 * Set the student payment status for a group via an AJAX call.
 *
 * @param {boolean} paymentStatus The desired payment status for the group.
 * @param {integer} groupId The group ID.
 */
function setGroupStudentPayment(paymentStatus, groupId) {
    var userInput = confirm(`You are about to ${paymentStatus ? 'ENABLE' : 'DISABLE'} student payments for ALL of the courses for this group!`);

    if (userInput) {
        request = $.ajax({
            type: 'POST',
            url: imasroot + '/ohm/assessments/settings_ajax.php',
            data: {
                'paymentStatus': paymentStatus,
                'groupId': groupId,
                'action': 'setGroupPaymentStatus'
            },
            success: function (res) {
                updateStudentPaymentButton(paymentStatus, groupId);
                $("span#student_payment_toggle_message").text("Saved!")
                    .css({'color': 'green', 'font-weight': 'normal', 'display': 'inline'})
                    .fadeOut(5000);
            },
            error: function (err) {
                $("span#student_payment_toggle_message").text("Failed!")
                    .css({'color': 'red', 'font-weight': 'bold', 'display': 'inline'})
                    .fadeOut(5000);
            }
        });
    }
}


/**
 * Update the button to do the opposite of its current action.
 *
 * @param {boolean} newPaymentStatus The new payment status for a group.
 * @param {integer} groupId The group ID.
 */
function updateStudentPaymentButton(newPaymentStatus, groupId) {
    var buttonText = null;
    var buttonOnClick = null;

    if (true === newPaymentStatus) {
        buttonText = 'Disable student payments';
        buttonOnClick = 'setGroupStudentPayment(false, ' + groupId + ');';
    } else {
        buttonText = 'Enable student payments';
        buttonOnClick = 'setGroupStudentPayment(true, ' + groupId + ');';
    }

    $("button#student_payment_toggle")
        .text(buttonText)
        .attr('onClick', buttonOnClick);
}
