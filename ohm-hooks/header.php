<?php

use OHM\Tracking\FullStory;

/**
 * Insert FullStory snippet into the <head> element.
 */
function insertIntoHead(): void
{
    $fullStory = new FullStory();
    $fullStory::outputHeaderSnippet();
}
