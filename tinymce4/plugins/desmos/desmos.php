<?php require_once(__DIR__ . '/../../../init.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!--[if lt IE 9]>
    <meta http-equiv="X-UA-Compatible" content="IE=7" />
    <![endif]-->
    <title>Desmos Interactive Editor</title>
    <script type="text/javascript" src="<?php echo $GLOBALS['CFG']['desmos_calculator']; ?>"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" type="text/javascript"></script>

    <link rel="stylesheet" href="/themes/lumen.css?v=112019" type="text/css" />
    <link rel="stylesheet" type="text/css" href="/themes/lux-temp.css" />
    <link rel="stylesheet" type="text/css" href="/desmos/desmos-temp.css" />
    <script type="text/javascript" src="js/desmos.js"></script>
</head>
<body onload="desmosDialog.init();">

<form onsubmit="return false;" action="#" style="margin:10px;">
    <figure id="editdesmos" class="js-desmos" data-json=""></figure>
        <div class="desmos-nav-btns"  style="float: right">
            <button id="desmos_preview_button" class="button -offset" type="button" onclick="top.tinymce.activeEditor.windowManager.close();">Cancel</button>
            <button id="desmos_form_submit_button" class="button -offset" type="submit" name="submitbtn" value="Submit" style="clear:both" onclick="desmosDialog.insert();">Add</button>
        </div>
    </div>
</form>

</body>
</html>
