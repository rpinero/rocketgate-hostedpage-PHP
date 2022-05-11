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
 * Purpose: This page uses the LinkBuilder util to build links to RG join pages
 *             
 */


//
// Include the Database and Postback Configs
//
require_once("rg_config.php");

//
// Include the LinkBuilder Class
//
include("LinkBuilder.php");

//
// These values must always be set.
//
$urlStuff = new LinkBuilder(RG_HASH_SECRET); // create a LinkBuilder Object
$urlStuff->Set("merch", RG_MERCHANT_ID);

$time = uniqueTimeStamp();

$urlStuff->Set("id", $time);
$urlStuff->Set("invoice", $time);


// Pass the Product ID in the prodid

if (  isset($_GET['prodid']) && strlen($_GET['prodid']) > 0) {
  $urlStuff->Set("prodid", $_GET['prodid']);


  if (  $_GET['prodid'] == 1) {
    $urlStuff->Set("amount", 1.99);

  } else if (  $_GET['prodid'] == 2) {
    $urlStuff->Set("amount", 2.99);
    $urlStuff->Set("rebill-freq", "MONTHLY");

  } else if (  $_GET['prodid'] == 3) {
    $urlStuff->Set("amount", 3.99);
    $urlStuff->Set("rebill-freq", "60");
    $urlStuff->Set("rebill-count", "0");
  }
} else {
  die("Missing Product ID. Please use valid join link from <a href=\"" . RG_MERCHANT_URL . "\">return to website</a>\n");
}

//
// this is required for a credit card transaction
//
$urlStuff->Set("method", "CC");
$urlStuff->Set("purchase", "TRUE");


//
// Get the encoded portion of the link
//
$str = $urlStuff->Encode();

//
// Establish which machine to send the request - put the link together
//
$link = RG_LINK . $str;

//
// Redirect user to join page link
//
//echo "<a href=\"" . $link . "\">Test</a>\n";
//
header('Cache-Control: no-cache');
header('Location: ' . $link);

?>
