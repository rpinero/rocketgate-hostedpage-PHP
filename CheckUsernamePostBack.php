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
 *    File name: CheckUsernamePostBack.php
 *    Purpose: This page shows an example of how to handle username availability tests
 *
 *             This page will only be called by RocketGate before submitting a purchase
 *             in order to test if a username is available in merchant's database.
 *
*/


//
// Include the Database and Postback Configs and functions
//
require_once("rg_config.php");

//
// Include the class that will parse the XML messaged passed
// by RocketGate.
//
require_once("XMLResponseParser.php");

//
// Prepare the input XML data - read the data from the input stream
//
$xml_in = @file_get_contents('php://input');
// $xml_in = @file_get_contents('./test.xml');

if (strlen($xml_in) == 0) {
    postback_response(1, "XML Request not found.");
}


//
// Establish a connection to a database if needed
//
//
$mysqli = new mysqli(RG_DB_SERVER, RG_DB_USERNAME, RG_DB_PASSWORD);

if($mysqli->connect_errno){
  //
  // Problem connecting to the database.  Send a response to RocketGate
  //
  postback_response(2,"Problem connection to the database: " . $mysqli->connect_error );

} else {
  //
  // The db connection was successful - now select the database to use
  //
  if(! $mysqli->select_db(RG_DB_NAME) ){
    //
    // Indicate an error if it not possible to connect to the correct database
    //
    postback_response(3, "Problem selecting database: " . $mysqli->error );
  }
}


//
// Use the XMLResponseParser class to parse the incoming data
//
$gw_response = new XMLResponseParser();
//
// Send the xml input to be processed by the XMLResponseParser class
//
$gw_response->SetFromXML($xml_in);

if ( count($gw_response->params) == 0  ) {
  postback_response(4, "XMLResponseParser failed to parse");
}

//
// Require username and merchantProductID for testing unique username per product.
//
if ( strlen($gw_response->Get('username')) == 0  ) {
  postback_response(4, "username is missing");
}

if ( strlen($gw_response->Get('merchantProductID')) == 0  ) {
  postback_response(4, "merchantProductID is missing");
}

//
// Prepare query to test for existing username *on this product*
//
if (!($stmt = $mysqli->prepare("SELECT count(*) AS user_count FROM rg_user_info WHERE user_name = ? AND product_id = ?"))) {
  postback_response(5, "Prepare select failed: " . $mysqli->errno );
}

if (! $stmt->bind_param("ss", $gw_response->Get('username'), $gw_response->Get('merchantProductID') ) ) 
{
  postback_response(6, "Binding parameters failed: " . $stmt->error );
}

if (! $stmt->execute() ) {
  postback_response(7, "Select failed: " . $stmt->error );
}

// Bind Result value
$stmt->bind_result($user_count);

$row = $stmt->fetch();

if ($user_count == 0) {
  postback_response(0,'');	// ZERO means we succeeded.

} else {
  postback_response(7, $user_count . " users exist with username " . $gw_response->Get('username') . " and product " . $gw_response->Get('merchantProductID') );
}

//
// Close the database connection and quit.
//
/* close connection */
$mysqli->close();

?>
