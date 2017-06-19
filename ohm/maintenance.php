<html>
  <head>
    <meta charset="utf-8">
    <title>OHM | Maintenance</title>
    <link rel="stylesheet" href="ohm/maintenance.css" />
  </head>
  <body class="bg">
    <div class="maintenance-block">
      <img class="logo" src="ohm/img/ohm-logo-800.png" alt="lumen logo">
      <h1 class="block-head">We're down for maintenance.</h1>
      <div class="block-text">
        <?php if ($maintenance_text) {
          echo "<p>$maintenance_text</p>";
        } else {
          echo "<p>Sorry about that.  We'll be up and running again in no time!</p>";
        } ?>
      </div>
    </div>
  </body>
</html>
