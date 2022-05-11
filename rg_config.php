<?php

//
// MERCHANT TODO: Configure these key variables.
//

define('RG_MERCHANT_URL', 'http://example.com/index.html');
define('RG_MERCHANT_LOGIN_URL', 'http://example.com/login/');

define('RG_MERCHANT_ID', 1);
define('RG_GW_PASSWORD', 'testpassword');
define('RG_HASH_SECRET', 'hashsecret');

// Salt used for protecting password, don't change after install or you will have different salts used on passwords in the database.
//  Modify this to your own value
define('RG_MERCHANT_PASSWORD_SALT', 'sdfj0j80hssflhjsfh8sfh8sgo2');

// Local Database Configs
define('RG_DB_SERVER', 'localhost');
define('RG_DB_NAME', 'demo');
define('RG_DB_USERNAME', 'rg_demouser');
define('RG_DB_PASSWORD', '3mpGsdfljsfjslf2r8');

// Set to FALSE for production, TRUE for Testing/Dev.
define('RG_TEST_MODE', TRUE);

if(RG_TEST_MODE)
{
  define('RG_LINK', 'https://dev-secure.rocketgate.com/hostedpage/servlet/HostedPagePurchase?');
}
else
{
  define('RG_LINK', 'https://secure.rocketgate.com/hostedpage/servlet/HostedPagePurchase?');
}


//
// RocketGate requires a response to this postback.  This function
// provides a properly formatted response message.
//
// $results indicates success or failure. A value of 0 indicates the server has received, parsed and processed the postback.
//          A non-zero value indicates that an error occurred.
// $message is an optional value that could be used to pass an error description which can be used in debugging the error.
//
function postback_response($results,$message){
  header("Content-Type: text/xml");
  header("Cache-Control: no-cache");

  $retStr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  $retStr .= "<Response>";
  $retStr .= "<results>" . $results . "</results>";
  if ($message != '') $retStr .= "<message>" . $message . "</message>";
  $retStr .= "</Response>\n";

  echo $retStr;             // Send to RocketGate
  die; // and quit program
} // end pb_response


//
// Use Timestamp for Customer ID Generators
//
function uniqueTimeStamp() {
  $milliseconds = microtime();
  $timestring = explode(" ", $milliseconds);
  $sg = $timestring[1];
  $mlsg = substr($timestring[0], 2, 4);
  $timestamp = $sg.$mlsg;
  return $timestamp;
}
 
?>
