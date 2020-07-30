<?php
/**
 * This fragment is included by other fragments in this directory to display an institution's
 * bookstore URL, if one is returned from the Lumenistration API.
 */
?>
    at your campus bookstore<?php // Opening tag is here to prevent whitespace.

$institutionData = $studentPayment->getInstitutionData();
$bookstoreUrl = is_null($institutionData) ? null : $institutionData->getBookstoreUrl();

if (is_null($institutionData) || empty($bookstoreUrl)) {
	echo '.';
} else {
	if(preg_match("/http[s]?:\/\//i", $bookstoreUrl)){
		$fixedBookstoreURL = $bookstoreUrl;
	} else {
		$fixedBookstoreURL = 'https://' . $bookstoreUrl;
	}
	?>
    or on the <a class="bookstore-url" target="_blank" href="<?php echo $fixedBookstoreURL; ?>">bookstore website</a>.
	<?php
}
