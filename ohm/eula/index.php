<?php
require(__DIR__ . '/../../init.php');
require(__DIR__ . '/../../header-eula.php');
?>
            <section id="js-eula-agreement" class="eula-agreement lux-component">
                <h1>Lumen OHM &ndash; End User License Agreement</h1>
                <p>Last updated on 12/12/2020</p>
                <div class="eula-frame u-margin-vertical-sm">
                    <?php include_once 'agreement.php' ?>
                </div>
                <form action="" method="">
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
