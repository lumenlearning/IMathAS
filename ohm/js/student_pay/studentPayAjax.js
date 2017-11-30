var request;

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
            success: function(res) {
                console.log("response:", res);
            },
            error: function(err) {
                console.log('error:', err);
            }
        });

    }
}