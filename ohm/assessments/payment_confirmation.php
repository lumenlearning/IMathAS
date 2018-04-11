<?php
if (!isset($_COOKIE['ohm_payment_confirmation'])) {
	header('Location: ' . $GLOBALS['basesiteurl']);
	exit;
}

require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");

$cookieData = json_decode($_COOKIE['ohm_payment_confirmation'], true);

echo '<pre>';
print_r($cookieData);
echo '</pre>';

$confirmationNum = $cookieData['confirmationNum'];
$courseId = $cookieData['courseId'];

$redirectTo = $GLOBALS["basesiteurl"] . '/assessment/showtest.php';
$paymentStatus = 'has_access';

$stm = $DBH->prepare('SELECT email FROM imas_users WHERE id = :id');
$stm->execute(array(':id' => $userid));
$userEmail = $stm->fetch(PDO::FETCH_ASSOC)['email'];

$stm = $DBH->prepare('SELECT name FROM imas_courses WHERE id = :id');
$stm->execute(array(':id' => $courseId));
$courseName = $stm->fetch(\PDO::FETCH_ASSOC)['name'];
?>

    <div id="directPay"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.min.js"></script>
    <script src="<?php echo $GLOBALS['student_pay_api']['direct_pay_component_url']; ?>"></script>
    <script>
      directPayComponents.renderDirectPayLandingPage('directPay', {
        'confirmationNum': '<?php echo $confirmationNum; ?>',
        'userEmail': '<?php echo $userEmail; ?>',
        'courseTitle': '<?php echo $courseName; ?>',
        'redirectTo': '<?php echo $redirectTo; ?>',
        'paymentStatus': '<?php echo $paymentStatus; ?>',
      });
    </script>

<?php
require_once(__DIR__ . "/../../footer.php");
exit;

