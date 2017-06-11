<?php
  require '../init_without_validate.php';

  /**
   * Define constants
   */
  define("ZDAPIKEY", $GLOBALS['CFG']['GEN']['zdapikey']);
  define("ZDURL", $GLOBALS['CFG']['GEN']['zdurl']);
  define("ZDUSER", $GLOBALS['CFG']['GEN']['zduser']);

  /**
   * Submit data via cURL.
   *
   * @param String $url The end of the ZenDesk URL
   * @param JSON $json JSON encoded data to be submitted to the URL
   *
   * @return Array
   */
  function curlWrap($url, $json) {
    // Initialize curl session
    $ch = curl_init();

    // Set options for curl transfer
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
    curl_setopt($ch, CURLOPT_URL, ZDURL . $url);
    curl_setopt($ch, CURLOPT_USERPWD, ZDUSER . "/token:" . ZDAPIKEY);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Perform curl session and close
    $output = curl_exec($ch);
    curl_close($ch);

    // JSON decode the output and return
    $decoded = json_decode($output);
    return $decoded;
  }

  /**
   * Loop over $_POST data, find the ones with 'z_' at the beginning, and
   * strip the html and php tags.
   *
   * @var Array $arr
   */
  foreach($_POST as $key => $value){
    if(preg_match('/^z_/i',$key)){
      $arr[strip_tags($key)] = strip_tags($value);
    }
  }

  /**
   * Assemble JSON encode.
   *
   * @var JSON encoded Array $create
   */
  $create = json_encode(
    array(
      'request' => array(
        'subject' => $arr['z_subject'],
        'comment' => array(
          'body'=> $arr['z_description'] . ' Requester email: ' . $arr['z_email']
        ),
        'priority' => $arr['z_priority']
      )
    )
  );
var_export($create);
  $return = curlWrap("/requests.json", $create);

?>
