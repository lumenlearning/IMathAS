<?php
/**
 * This file is included from fragments/activation.php.
 */

$studentPaymentDb = new \OHM\StudentPaymentDb($courseOwnerGroupId, $courseId, $userid);
$enrollmentId = $studentPaymentDb->getStudentEnrollmentId();

?>
<!-- Enrollment ID = <?php echo $enrollmentId; ?> -->
<form id="ohmActivateCodeForm" method="POST" action="#">
    <input type="hidden" name="action" value="activate_code"/>
    <input type="hidden" name="group_id" value="<?php echo $courseOwnerGroupId; ?>"/>
    <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
    <input type="hidden" name="student_id" value="<?php echo Sanitize::onlyInt($userid); ?>"/>
    <div class="access_code_input_wrapper">
      <label for="access_code">Enter an activation code:</label>
      <input type="text" name="access_code" id="access_code" placeholder="Enter code"/>
      <div id="access_code_error_text"></div>
      <button type="submit" id="access_code_submit">Access Assessments</button>
    </div>
</form>


<script>
    $('#ohmActivateCodeForm').on('submit', function (event) {
        event.preventDefault();

        $('#access_code_error_text').text('Activating your code...');

        var activationCodeForm = $('#ohmActivateCodeForm');
        var groupId = activationCodeForm.find('input[name="group_id"]').val();
        var courseId = activationCodeForm.find('input[name="course_id"]').val();
        var studentId = activationCodeForm.find('input[name="student_id"]').val();
        var activationCode = $('#access_code').val();

        request = $.ajax({
            type: 'POST',
            url: imasroot + '/ohm/assessments/activation_ajax.php',
            data: {
                'action': 'activate_code',
                'groupId': groupId,
                'courseId': courseId,
                'studentId': studentId,
                'activationCode': activationCode
            },
            success: function (data) {
                window.location.reload();
            },
            error: function (data) {
                results = JSON.parse(data.responseText);
                $('#access_code_error_text').text(results.message);
            }
        });
    });
</script>

