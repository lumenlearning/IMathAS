<?php
require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");
?>

<div id="directPay"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.min.js"></script>
<script src="../../../lumen-components/build/vanilla/render_direct_pay_component.js"></script>
<script>
  renderDirectPayComponent.renderDirectPayComponent('directPay', {
    'endpoint': '<?php echo $GLOBALS['basesiteurl']
        . "/ohm/assessments/direct_pay.php?action=payment_proxy" ?>'
  });
</script>

<?php
require_once(__DIR__ . "/../../footer.php");
exit;

