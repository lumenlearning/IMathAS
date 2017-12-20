<?php
/**
 * This file is included from fragments/activation.php.
 */
?>

<h1 class="greeting">Enter a Lumen OHM course activation code.</h1>

<div class="access-sub-block">
  <div class="access-sub-block-left">
    <?php
    if (in_array($paymentStatus, $canEnterCode)) {
        $validApiResponse = true;
      require_once(__DIR__ . "/activate_code.php");
    }
    ?>
  </div>
  <div class="access-sub-block-right">
    <p class="emphasis">Your trial has expired.</p>
    <p>
      You need an activation code to complete the Lumen OHM assessments in this
      course. Purchase one <?php require(__DIR__ . '/code_purchase_location.php'); ?>
    </p>
    <p>
      In the meantime, you can still view your other course materials.
    </p>
  </div>
</div>
