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
 * File name: Success.php
 * Purpose: This page is an example of the final step in a successful
 *             transaction using the RocketGate HostedPage signup system.
 *             This example page simply shows a success message.
 *
 */

//
// Include the Database and Postback Configs
//
require_once("rg_config.php");

//
// Include the class that can build a link
//
include("LinkReader.php");

//
// It is important to confirm that the link is coming from RocketGate.
// This is done by checking the hash value in the incoming URL against
// our internally computed hash value.
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
  die("Invalid hash!<br/>\n");
}

//
// Establish a connection to a database 
//
//
$mysqli = new mysqli(RG_DB_SERVER, RG_DB_USERNAME, RG_DB_PASSWORD);

if($mysqli->connect_errno){
  //
  // Problem connecting to the database. 
  //
  die("Problem connection to the database: " . $mysqli->connect_error . "\n");

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

//
// Confirm user exists, get product id for linking to purchase 
//
if (!($stmt = $mysqli->prepare("select user_confirmed, product_id from rg_user_info where user_id = ? and invoice_id = ?"))) {
  die("Query Prepare failed.<br/>\n" . $mysqli->errno );
}

if (! $stmt->bind_param("ss", $_GET['id'], $_GET['invoiceID'] ) ) {
  die("Query Binding failed.<br/>\n" . $mysqli->errno );
}

if (! $stmt->execute() ) {
  die("Select failed.<br/>\n" . $mysqli->errno );
}

$stmt->bind_result($hp_confirmed, $product_id); 

if ($stmt->fetch()) {

  // echo "test: hp_confirmed=" . $hp_confirmed . ", and product_id=" . $product_id . ".\n";
  $stmt->close();

  if (! $hp_confirmed) {
    //
    // Update rg_user_info with successful postback confirmation.
    //
    if (!($stmt = $mysqli->prepare("update rg_user_info set user_confirmed = true where user_id = ? and invoice_id = ?"))) {
      die("Update Prepare failed.<br/>\n"); //  . $mysqli->errno );
    }

    if (! $stmt->bind_param("ss", $_GET['id'], $_GET['invoiceID'] ) ) {
      die("Update Binding failed.<br/>\n" . $mysqli->errno );
    }

    if (! $stmt->execute() ) {
      die("Update failed.<br/>\n" . $mysqli->errno );
    }
    $stmt->close();
  }

} else {
  $stmt->close();
  die("Customer Record not found. Please contact Support for assistance.\n");
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="HandheldFriendly" content="True">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="cleartype" content="on">
    <title>Purchase Successful</title>
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
<p>For any questions or assistance, please contact support@example.com
or xxx-xxx-xxxx 24/7. We are always here to help!
</p>
    </div><!-- end sidebar -->

    <div id="frameWrapper">
    <center>

  <form action="" method="post">
  <div id="formWrapper">
  <div class="header">&nbsp;</div>
    <h2>Transaction Successful!</h2>
    <p class="successMSG" style="text-align: center;">Thank you for your purchase. <br/>
       You can now access:
       <a style="font-size: 130%" href="<? printf("%s%02d/",RG_MERCHANT_LOGIN_URL,$product_id) ?>">Product <? printf("%02d",$product_id) ?></a>
    </p>
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
