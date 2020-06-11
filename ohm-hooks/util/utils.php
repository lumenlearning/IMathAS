<?php

function onUserLookup($groupType): void {
    if (1 == $groupType) {
        echo ' (Lumen Customer)';
    }
}
