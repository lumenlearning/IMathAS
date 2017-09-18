<?php

@set_time_limit(0);
ini_set("max_input_time", "600");
ini_set("max_execution_time", "600");

if (empty($_POST['url']) || empty($_POST['toget'])) {
	return '';
}
$url = $_POST['url'];
$toget = $_POST['toget'];

require("phpQuery-onefile.php");
require("../includes/S3.php");
$bucket = 'candimgs';
$s3base = 'https://s3-us-west-2.amazonaws.com/';
$s3 = new S3(getenv('LUMEN_AWS_KEY'),getenv('LUMEN_AWS_SECRET'));
$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$randpath = '';
for ($i=0;$i<6;$i++) {
	$randpath .= $str{rand(0,61)};
}
$curdir = rtrim(dirname(__FILE__), '/\\');
$galleryPath = "$curdir/tmp/";

function storefiletos3($name) {
	global $bucket, $randpath, $galleryPath, $s3, $s3base;
	$s3->putObjectFile($galleryPath.$name, $bucket, $randpath.'/'.$name, "public-read");
	return $s3base.$bucket.'/'.$randpath.'/'.$name;
}

function processImage($image, $thumbWidth, $thumbHeight )
{
    global $galleryPath;
    
    if (strpos($image,'.jpg')!==false || strpos($image,'.jpeg')!==false) {
    	    $im = imagecreatefromjpeg($galleryPath.$image);
    } else if (strpos($image,'.gif')!==false) {
    	    $im = imagecreatefromgif($galleryPath.$image);
    } else {
    	    $im = imagecreatefrompng($galleryPath.$image);
    }
    $size = getimagesize($galleryPath.$image);
    $w = $size[ 0 ];
    $h = $size[ 1 ];
   
    // create thumbnail
    $tw = $thumbWidth;
    $th = $thumbHeight;
    
    if ($w<=500 || $h<40) {
    	    return $image;
    }
    $imname = 'sm_'.str_replace(array('.png','.jpg','.jpeg'),'',$image);
   
    if ( $w/$h > $tw/$th )
    { // wider
	$tmph = $h*($tw/$w);
	$imT = imagecreatetruecolor( $tw, $tmph );
	imagecopyresampled( $imT, $im, 0, 0, 0, 0, $tw, $tmph, $w, $h ); // resize to width
    }else
    { // taller
      
	//nocrop version
	$tmpw = $w*($th/$h);
	$imT = imagecreatetruecolor( $tmpw, $th );
	imagecopyresampled( $imT, $im, 0, 0, 0, 0, $tmpw, $th, $w, $h ); // resize to width
    }
   
    // save the image
   imagejpeg( $imT, $galleryPath . $imname . '.jpg', 80 ); 
   return $imname . '.jpg';
}


//start good stuff

if ($toget=='wikipedia') {
	$html = file_get_contents($_POST['url']);
	phpQuery::newDocumentHTML($html);
	pq(".mw-editsection")->remove();
	pq(".navbox,.vertical-navbox,.catlinks,.metadata")->remove();
	pq("#See_also")->parent()->nextAll("ul:first")->remove();
	pq("#See_also")->parent()->remove();
	pq("noscript")->remove();
	pq("#toc")->parents("table")->remove();
	pq("#toc")->remove();
	pq("#bottom-navigation")->remove();
	$html = pq("#mw-content-text")->html();
} else if ($toget == 'selector') {
	$html = file_get_contents($_POST['url']);
	phpQuery::newDocumentHTML($html);
	$sel = preg_replace('/[^\w:\-\.#]/','',$_POST['selector']);
	$html = pq($sel)->html();
} else if ($toget == 'selparent') {
	$html = file_get_contents($_POST['url']);
	$html = mb_convert_encoding($html, "UTF-8");
	phpQuery::newDocumentHTML($html);
	$sel = preg_replace('/[^\w:\-\.#]/','',$_POST['selparent']);
	pq(".questionX,.buttonX")->remove();
	$html = pq($sel)->parent()->html();
} else {
	$html = $_POST['html'];
}
$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));


$parseUrl = parse_url($url);
$parent = (substr($parseUrl['path'], -1) == '/') ? $parseUrl['path'] : dirname($parseUrl['path']) . "/";
$basedir = Sanitize::url($parseUrl["scheme"]."://".$parseUrl["host"].((isset($parseUrl["port"]))?":".$parseUrl["port"]:"").$parent);
$rootdir = Sanitize::url($parseUrl["scheme"]."://".$parseUrl["host"].((isset($parseUrl["port"]))?":".$parseUrl["port"]:""));


if (strpos($html, '<body')===false) {
	$html = '<html><body>'.$html.'</body></html>';
}
phpQuery::newDocumentHTML($html);
$as = pq("a");
foreach ($as as $a) {
	if (substr(pq($a)->attr("href"),0,4)!='http' && substr(pq($a)->attr("href"),0,1)!='#') {
		pq($a)->replaceWith(pq($a)->contents());		
	}
}

//this fix is for ck12 ugly
$iframes = pq("iframe");
foreach ($iframes as $iframe) {
	$src = pq($iframe)->attr("src");
	if (preg_match('|/flx/show/video.*?(youtu.*)$|',$src,$matches)) {
		$parts = explode('%', $matches[1]);
		pq($iframe)->attr("src", 'https://www.'.$parts[0].'?rel=0&amp;wmode=transparent');
	}
}

$imgs = pq("img");
foreach ($imgs as $img) {
	$src = pq($img)->attr("src");
	if (substr($src,0,2)=='//') {
		$path = 'http:' . $src;
	} else if ($src{0}=="/") {
		$path = $rootdir . $src;
	} else if (substr($src,0,4)=='http') {
		$path = $src;
	} else {
		$path = $basedir . $src;
	}
	$pathparts = explode('?',$path);
	$localname = preg_replace('/[^\w-\._]/','',urldecode(basename($pathparts[0])));
	copy($path, $galleryPath.'/'.$localname);
	$newname = processImage($localname, 500,400);
	pq($img)->attr("src", storefiletos3($newname));
	pq($img)->wrap('<a href="'.storefiletos3($localname).'"/>');
}
$out = pq("body")->html();
$out = preg_replace('/<!--.*?-->/sm','',$out);
echo trim($out);
?>
