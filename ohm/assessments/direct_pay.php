<?php
require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");

$endpoint = $GLOBALS["basesiteurl"]
    . '/ohm/assessments/direct_pay.php?action=payment_proxy';
$apiKey = $GLOBALS["student_pay_api"]["stripe_api_key"];
// FIXME: How are we pricing things? Where is this value coming from??
$amount = '3000'; // must be a string
$redirectTo = $GLOBALS["basesiteurl"] . '/ohm/assessment/showtest.php';
?>

<div id="directPay"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.min.js"></script>
<script src="../../../lumen-components/build/vanilla_js/direct_pay_components.js"></script>
<script>
  directPayComponents.renderDirectPayLandingPage('directPay', {
    'endpoint': '<?php echo $endpoint; ?>',
    'stripeKey': '<?php echo $apiKey; ?>',
    'chargeAmount': '<?php echo $amount; ?>',
    'redirectTo': '<?php echo $redirectTo; ?>',
  });
</script>

<?php
require_once(__DIR__ . "/../../footer.php");
exit;

