<?php

$envvars = file('.env');

foreach ($envvars as $num => $envvar) {
    putenv($envvar);
}

?>