<?php

$envvars = file('.env');

foreach ($envvars as $num => $envvar) {
    $key_and_value = explode("=", $envvar);
    $key = $key_and_value[0];
    $value = $key_and_value[1];
    $ENV[$key] = $value;
}

?>