<?php
// Some data is not available until after the page has loaded, so we wait
// for the page to load before attempting to sending data to FullStory.
$fullstory = new \OHM\Tracking\FullStory();
$fullstory->getUserMetadataSnippet();

// The closed php tag is needed because this file is require'd in footer.php.
?>
