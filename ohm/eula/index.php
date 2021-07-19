<?php

use OHM\Eula\EulaService;
use OHM\Exceptions\DatabaseWriteException;

require(__DIR__ . '/../../init.php');

if (!isset($GLOBALS['userid']) || is_null($GLOBALS['userid'])) {
    echo '<p>Error: You must be signed in to view the EULA.</p>';
    exit;
}

if ('accept-eula' == $_POST['action']) {
    $eulaService = new EulaService($GLOBALS['DBH']);
    try {
        $eulaService->updateUserAcceptanceToLatest($GLOBALS['userid']);
    } catch (DatabaseWriteException $e) {
        error_log($e->getMessage());
    }

    $destUrl = $_POST['dest-url'];
    ob_clean();
    header('Location: ' . $GLOBALS['basesiteurl'] . $destUrl);
    exit;
}

$eulaFormAction = $GLOBALS['basesiteurl'] . '/ohm/eula/index.php';
// Strip protocol and hostname. This prevents redirects to external URLs.
$url = parse_url(urldecode($_GET['dest']));
$destUrl = $url['path'] . '?' . $url['query'];

require(__DIR__ . '/../../header-eula.php');
?>
            <section id="js-eula-agreement" class="eula-agreement lux-component">
                <h1>Lumen OHM &ndash; End User License Agreement</h1>
                <p>Last updated on 12/12/2020</p>
                <div class="eula-frame u-margin-vertical-sm">
                    <?php include_once 'agreement.php' ?>
                </div>
                <form action="<?php echo $eulaFormAction; ?>" method="POST">
                    <input type="hidden" name="action" value="accept-eula"/>
                    <input type="hidden" name="dest-url" value="<?php echo $destUrl; ?>"/>
                    <label for="eula-checkbox">
                        <input type="checkbox" id="eula-checkbox">
                        <span>I have read the End User License Agreement</span>
                    </label>
                    <div class="u-margin-vertical">
                        <button type="button" id="js-eula-button--disagree" class="button">I disagree</button>
                        <button type="submit" disabled id="js-eula-button--agree" class="button button--primary">I agree</button>
                    </div>
                </form>
            </section>
            <section id="js-eula-disagree" class="eula-disagree lux-component" hidden>
                <?php include_once '../img/stop-icon.svg' ?>
                <p class="h1">Uh oh</p>
                <p>To view this content you must agree to the End User License Agreement (EULA).</p>
                <button type="button" id="js-eula-button--return" class="button button--primary u-margin-vertical">Return</button>
            </section>
            <script>
                $('#eula-checkbox').click(function() {
                    if ($(this).is(':checked')) {
                        $('#js-eula-button--agree').prop('disabled', false);
                    } else {
                        $('#js-eula-button--agree').prop('disabled', true);
                    }
                });
                $('#js-eula-button--disagree').click(function() {
                    $('#js-eula-agreement').hide();
                    $('#js-eula-disagree').show();
                });
                $('#js-eula-button--return').click(function() {
                    $('#js-eula-agreement').show();
                    $('#js-eula-disagree').hide();
                });
            </script>
        </div>
    </div>
</body>

</html>
