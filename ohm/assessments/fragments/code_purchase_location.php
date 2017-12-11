<?php
/**
 * This fragment is included by other fragments in this directory to display an institution's
 * bookstore URL, if one is returned from the Lumenistration API.
 */
?>
    at your campus bookstore<?php // Opening tag is here to prevent whitespace.

$institutionData = $studentPayment->getInstitutionData();
$bookstoreUrl = $institutionData->getBookstoreUrl();

if (empty($bookstoreUrl)) {
	echo '.';
} else {
	?>
    or on the <a href="<?php echo $bookstoreUrl; ?>">bookstore website</a>.
	<?php
}

