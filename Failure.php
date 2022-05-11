<?php
/*
 * Copyright notice:
 * (c) Copyright 2018 RocketGate
 * All rights reserved.
 *
 * The copyright notice must not be removed without specific, prior
 * written permission from RocketGate.
 *
 * This software is protected as an unpublished work under the U.S. copyright
 * laws. The above copyright notice is not intended to effect a publication of
 * this work.
 * This software is the confidential and proprietary information of RocketGate.
 * Neither the binaries nor the source code may be redistributed without prior
 * written permission from RocketGate.
 *
 * The software is provided "as-is" and without warranty of any kind, express, implied
 * or otherwise, including without limitation, any warranty of merchantability or fitness
 * for a particular purpose.  In no event shall RocketGate be liable for any direct,
 * special, incidental, indirect, consequential or other damages of any kind, or any damages
 * whatsoever arising out of or in connection with the use or performance of this software,
 * including, without limitation, damages resulting from loss of use, data or profits, and
 * whether or not advised of the possibility of damage, regardless of the theory of liability.
 *
 * File name: Failure.php
 * Purpose: This page shows an example of how to handle a failure
 *             from the RocketGate Hosted Page system.  All failures
 *             come to this page.
 *
 *    Failures come in 5 catagories:
 *             errcat    Description
 *             1         Bank declined financial information
 *             2         RocketGate scrubbing decline
 *             3         System Error
 *             4         Rejected: Missing fields / Field validation
 *             5         Host Page Environment error
 *
 *             Each error catagory has a series of error codes.  Description
 *             of the codes can be found in the RocketGate documentation
 *
 *
 */

//
// Include the Database and Postback Configs
//
include("rg_config.php");

//
// Include the class that can build a link
//
include("LinkReader.php");

//
// It is important to confirm that the failure information is coming
// from RocketGate.  This is done by checking the hash value in the
// incoming URL against our internally computed hash value.
//
// First, split the incoming URL to obtain everything after the "?".
//
list($uri_string, $values_string) = explode('?', $_SERVER['REQUEST_URI']);

//
// Create a LinkReader.php class instance to check the hash
// contained in the URL.
//
$link_reader = new LinkReader(RG_HASH_SECRET);

//
// Confirm that the incoming link is from RocketGate
//
if($link_reader->ParseLink($values_string) != 0){
  //
  // Either this link was not made by RocketGate, or there is a
  // problem with the secret key
  //
  die("Link contains invalid hash value!!!<br/>\n");
}

//
// The error_msg variable will hold the message associated with the errcat
//
$error_msg = "";
$errcode = $link_reader->Get('errcode');

//
// Determine the type of error catagory
//
switch($link_reader->Get('errcat')){
 case 1:  // Bank declined financial information
   $error_msg = "This transaction was declined by the financial institution.";
   break;

 case 2:  // RocketGate scrubbing decline
   if( ($errcode >= 208 && $errcode <= 210) || $errcode == 218) {
     $error_msg = "You already appear to have an existing membership.";
   } else {
     $error_msg = "This transaction was declined due to fraud scrubbing.";
   }
   break;

 case 3:  // System Error
   $error_msg = "This transaction has been declined and terminated due to an internal system error";
   break;

 case 4:  // Rejected: Missing fields / Field validation
   if($errcode == 439) {
     $error_msg = "You have re-submitted a purchase.";
   } elseif($errcode == 440) {
     $error_msg = "You already appear to have an existing membership.";
   } else {
     $error_msg = "This transaction was declined due to invalid customer input";
   }
   break;

 case 5:  // Host Page Environment error
   if($errcode == 506) {
     $error_msg = "No Customer record found.";
   } else {
      $error_msg = "This transaction has been declined and terminated because the postback failed";
   } 
   break;
 default: 
   $error_msg = "This transaction has been declined for unknown reasons";
} // end switch


//
// If the error code is postback failure, remove the potential login so user can re-join.
//
if($link_reader->Get('errcode') >= 510 && $link_reader->Get('errcode') <= 516) {

  $mysqli = new mysqli(RG_DB_SERVER, RG_DB_USERNAME, RG_DB_PASSWORD);

  if($mysqli->connect_errno){
    die("Problem connecting to the database: ");

  } else {
    //
    // The db connection was successful - now select the database to use
    //
    if(! $mysqli->select_db(RG_DB_NAME) ){
      //
      // Indicate an error if it not possible to connect to the correct database
      //
      die("Problem selecting database: " . $mysqli->error );
    }
  }

  if (!($stmt = $mysqli->prepare("delete from rg_user_info where user_id = ? AND invoice_id = ?"))) {
    die("Prepare insert failed: ");
  }

  if (! $stmt->bind_param("ss", $_GET['id'], $_GET['invoiceID'] ) ) {
    die("Update Binding failed.<br/>\n" . $mysqli->errno );
  }

  if (! $stmt->execute() ) {
    die("Insert failed: ");
  }

  /* close connection */
  $mysqli->close();
}


?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="HandheldFriendly" content="True">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="cleartype" content="on">
    <title>Purchase Failed</title>
    <link rel="stylesheet" href="https://secure.rocketgate.com/hostedpage/parentFrame.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="https://secure.rocketgate.com/hp/css/release.1.0.css" type="text/css" media="screen" />
</head>

<body>

<div id="container">
   <div id="masthead">
      <h1 align=center><img src="/logo.jpg" width="252" height="33"></h1>
   </div><!-- end masthead -->
    <div id="contentWrapper">

    <div id="sidebar">
      <h2>General Links and Information</h2>
 <ul>
 <li><a href="http://www.example.com">Website Home</a></li>
 </ul>

 <h2>Customer Support</h2>
 <p>Toll Free (US only): 855-553-1284</p>
 <p>Outside US call: +1 702-749-4453</p>
 <p><a href="support@example.com">support@example.com</a></p>

<h2>Warning:</h2>
<p>
For any questions or assistance, please contact support@example.com
or xxx-xxx-xxxx 24/7. We are always here to help!
</p>
    </div><!-- end sidebar -->

    <div id="frameWrapper">
    <center>

<FORM METHOD="POST" ACTION="">
  <div id="formWrapper">
  <div class="header">&nbsp;</div>
    <h2>Transaction Failed!</h2>
    
    <p class="errorMSG" style="text-align: center;">
      <?=$error_msg?>
      <br/><br/>
      <a style="font-size: 130%" href="<?=RG_MERCHANT_URL?>">Return to site</a>
    </p>
    <br>
    <div class="footer">&nbsp;</div>
  </div>
</form>

 </div><!-- end frameWrapper -->
    <div id="footer">
    </div><!-- end footer -->
  </div><!-- end contentWrapper -->
  </div><!-- end container -->

</body>
</html>
