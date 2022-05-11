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
 * File name: rg_install.php
 * Purpose: This page installs the required database table needed for access control
 *
 */

//
// Include the Database and Postback Configs
//
include("rg_config.php");

//
// Establish a connection to a database if needed
//
//
$mysqli = new mysqli(RG_DB_SERVER, RG_DB_USERNAME, RG_DB_PASSWORD);

if($mysqli->connect_errno){
  //
  // Problem connecting to the database.  Send a response to RocketGate
  //
  die("Problem connection to the database: " . $mysqli->connect_error );

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

if ($mysqli->query("drop table rg_user_info") === TRUE) {
    echo "Table rg_user_info dropped successfully.\n";
} else {
    echo "Error dropping table\n Might not have previously existed...: " . $mysqli->error;
}

$sql = "CREATE TABLE rg_user_info(
    user_id                 varchar(36),
    invoice_id              varchar(36),
    user_name               VARCHAR(48) NOT NULL,
    user_password           VARCHAR(36) NOT NULL,
    site_id                 VARCHAR(12),
    product_id              VARCHAR(36),
    user_confirmed          BOOLEAN,
    user_created_date       TIMESTAMP DEFAULT NOW(),
    PRIMARY KEY (user_id, invoice_id),
    UNIQUE INDEX rg_user_info_user_prod_uni (user_name,product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql) === TRUE) {
    echo "Table rg_user_info created successfully";
} else {
    echo "Error creating table: " . $mysqli->error;
}

/* close connection */
$mysqli->close();

?>
