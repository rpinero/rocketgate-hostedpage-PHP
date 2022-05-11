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
 *    File name: CancelPostBack.php
 *    Purpose: This page shows an example of how to handle the data
 *             that comes back in the cancel post back process from RocketGate.
 *
 *             This page will only be called when there is a membership cancelation
 *
*/


//
// Include the Database and Postback Configs and Functions
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
$mysqli = new mysqli(localhost, RG_DB_USERNAME, RG_DB_PASSWORD);

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

if ( strlen($gw_response->Get('customerID')) == 0  ) {
  postback_response(4, "customerID is missing");
}
if ( strlen($gw_response->Get('invoiceID')) == 0  ) {
  postback_response(4, "invoiceID is missing");
}

//
// Prepare the statement for the customer update
//
if (!($stmt = $mysqli->prepare("DELETE FROM rg_user_info WHERE user_id = ? and invoice_id = ?"))) {
  postback_response(5, "Prepare delete failed: " . $mysqli->errno );
}

//
// Prepare Delete
//

if (! $stmt->bind_param("ss", $gw_response->Get('customerID'), $gw_response->Get('invoiceID') ) ) {
  postback_response(6, "Binding parameters failed: " . $stmt->error );
}

//
// Delete Customer Record
//
if (! $stmt->execute() ) {
  postback_response(7, "Delete failed: " . $stmt->error );
}

//
// All of the database updates have been completed.  Send a
// SUCCESS response to RocketGate.
//
  postback_response(0,'');				// ZERO means we succeeded.

//
// Close the database connection and quit.
//
/* close connection */
$mysqli->close();

?>
