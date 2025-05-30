<?php
//IMathAS:  User image upload function
//(c) 20009 David Lippman

$curdir = rtrim(dirname(__FILE__), '/\\');
require_once "$curdir/filehandler.php";

// $image is $_FILES[ <image name> ]
// $imageId is the id used in a database or wherever for this image
// $thumbWidth and $thumbHeight are desired dimensions for the thumbnail
// return true on success, false on failure
function processImage( $image, $imageId, $thumbWidth, $thumbHeight )
{
    $type = $image[ 'type' ];
    $curdir = rtrim(dirname(__FILE__), '/\\');
    $galleryPath = "$curdir/../course/files/";
   
    if ( strpos( $type, 'image/' ) === FALSE )
    { // not an image
        return FALSE;
    }
    $type = str_replace( 'image/', '', $type );
    if ($type=='pjpeg') { //stupid IE6
	    $type = 'jpeg';
    }
    if ($type!='jpeg' && $type!='png' && $type!='gif') {
	    //invalid image type
	    return FALSE;
    }
    
    $createFunc = 'imagecreatefrom' . $type;
   
    $im = @$createFunc( $image[ 'tmp_name' ] );
    if (!$im) {
	    return false;
    }
    $size = getimagesize( $image[ 'tmp_name' ] );
    $w = $size[ 0 ];
    $h = $size[ 1 ];
   
    // create thumbnail
    $tw = $thumbWidth;
    $th = $thumbHeight;
    
   
    if ( $w/$h > $tw/$th )
    { // wider
	$imT = imagecreatetruecolor( $tw, $th );
        $tmpw =round($w*($th/$h));
        $temp = imagecreatetruecolor( (int) $tmpw, (int) $th );
        imagecopyresampled( $temp, $im, 0, 0, 0, 0, $tmpw, $th, $w, $h ); // resize to width
        imagecopyresampled( $imT, $temp, 0, 0, (int) round($tmpw/2-$tw/2),0, $tw, $th, $tw, $th ); // crop
        imagedestroy( $temp );
    }else
    { // taller
        /* crops
	$imT = imagecreatetruecolor( $tw, $th );
	$tmph = $h*($tw/$w );
        $temp = imagecreatetruecolor( $tw, $tmph );
        imagecopyresampled( $temp, $im, 0, 0, 0, 0, $tw, $tmph, $w, $h ); // resize to height
        imagecopyresampled( $imT, $temp, 0, 0, 0, $tmph/2-$th/2, $tw, $th, $tw, $th ); // crop
	imagedestroy( $temp );
	*/
	//nocrop version
	$tmpw = round($w*($th/$h));
	$imT = imagecreatetruecolor( (int) $tmpw, (int) $th );
	imagecopyresampled( $imT, $im, 0, 0, 0, 0, $tmpw, $th, $w, $h ); // resize to width
    }
    if ($type=='jpeg') {
    	$exif = exif_read_data($image[ 'tmp_name' ]);	  
    	if (isset($exif['Orientation']) && $exif['Orientation']>1) {
		switch($exif['Orientation']) {
			case 3:
			    $imT = imagerotate($imT, 180, 0);
			    $changed = true;
			    break;
			case 6:
			    $imT = imagerotate($imT, -90, 0);
			    $changed = true;
			    break;
			case 8:
			    $imT = imagerotate($imT, 90, 0);
			    $changed = true;
			    break;
		}
	}
    }
    // save the image
   imagejpeg( $imT, $galleryPath . 'userimg_'.$imageId . '.jpg', 100 );
   return relocatecoursefileifneeded($galleryPath . 'userimg_'.$imageId . '.jpg', 'userimg_'.$imageId . '.jpg');
}

?>
