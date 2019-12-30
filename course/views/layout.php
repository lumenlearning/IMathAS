<?php
require(__DIR__ . "/../../header.php");
if (file_exists($body)) {
    include "$body";
} else {
    echo $body;
}
require(__DIR__ . "/../../footer.php");