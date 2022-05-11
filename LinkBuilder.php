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
 * File name: LinkBuilder.php
 * Purpose: The purpose of this php file is to be an Object that can
 *             build a link for passing a charge request to the
 *             RocketGate system.  This is meant to be used in production
 *             or as an example of how to accomplish encoded link
 *             building.
 *
 */

class LinkBuilder {
  ////////////////////////////////////////////////////////////////////
  //
  // variables
  //
  ////////////////////////////////////////////////////////////////////
  //
  var $hashKey;         // secret shared key for the hash function
  var $params;          // array of key value pairs


  ////////////////////////////////////////////////////////////////////
  //
  // LinkBuilder() - constructor for the class
  // input: shared key string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function LinkBuilder($keyString){
    //
    // set the key for seeding the hash
    //
    $this->hashKey = $keyString;

    //
    // prepare the parameter array
    //
    $this->params = array();

  } // end constructor


  ////////////////////////////////////////////////////////////////////
  //
  // Set() - set a key value pair
  // input: key and value to be stored as strings
  // return : nothing returned
  //
  ////////////////////////////////////////////////////////////////////
  //
  function Set($key, $value){
    //
    // remove white space from begining and end of incoming value
    //
    $valueTrim = trim($value);

    //
    // unset the array value if it exists already
    //
    $this->Clear($key);

    //
    // do some checking on the 'amount' variable if it is set
    // remove the '$' if it exists
    //
    if(strtolower($key) == "amount"){
      $pattern = '/^\$/';
      while(preg_match($pattern, $valueTrim)){
	$valueTrim = substr($amount, 1);
      }
    }

    //
    // store the key value pair
    //
    $this->params[$key] = $valueTrim;
  } // end Set


  ////////////////////////////////////////////////////////////////////
  //
  // Clear() - used for clearing values for the array of perameters
  // input : name of key to be cleared
  // return : nothing returned
  //
  ////////////////////////////////////////////////////////////////////
  //
  function Clear($key){
    //
    // check if there is a preexisting key in the parameters array
    //
    if(array_key_exists($key, $this->params)){
      //
      // remove the key value pair from the parameters
      //
      unset($this->params[$key]);
    }
  } // end Clear


  ////////////////////////////////////////////////////////////////////
  //
  // Encode() - this is the function that will produce the correct link portion
  //            for connecting to the Rocket Gate system
  // return: string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function Encode(){
    $unencodedRetStr = ""; // this string will be hashed
    $encodedRetStr = "";   // this string is returned
    $sha1Hash = "";        // this string will hold the hash output
    $b64 = "";             // this string will hold the base64 encoding of the hash

    //
    // loop through all the keys and values
    //
    foreach($this->params as $key => $value){
      //
      // check if an '&' is needed
      //
      if(strlen($unencodedRetStr) > 0){
	$unencodedRetStr .= "&";
      }

      //
      // add values to the unencoded string
      //
      $unencodedRetStr .= $key . "=" . $value;

      //
      // check if an '&' is needed
      //
      if(strlen($encodedRetStr) > 0){
	$encodedRetStr .= "&";
      }
      //
      // add values to the encoded string
      //
      $encodedRetStr .= $key . "=" . urlencode($value);
    } // end looping

    //    
    // get the unencoded string ready to hash by adding the shared secret key
    //
    $unencodedRetStr .= "&secret=" . $this->hashKey;

    //
    // hash the unencoded string and return the raw output
    //
    $sha1Hash = hash("sha1", $unencodedRetStr, true);

    //
    //	Note:	Older versions of PHP may not include the
    //		"hash" function.  In that instance, the "pack"
    //		function can be used as shown below.
    //
    //
    //	$sha1Hash = pack("H*", sha1($unencodedRetStr));
    //	

    //
    // base64 encode the hash output
    //
    $b64 = base64_encode($sha1Hash);

    //
    // prepare the encoded string to return
    //
    $encodedRetStr .= "&hash=" . urlencode($b64);

    //
    // return the encoded final string
    //
    return $encodedRetStr;
  } // end Encode


  ////////////////////////////////////////////////////////////////////
  //
  // the functions below are for debugging purposes
  // they are meant to be helpful routines
  //
  ////////////////////////////////////////////////////////////////////


  ////////////////////////////////////////////////////////////////////
  //
  // getKeys() - this function produces a string of all the keys
  // return : string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function getKeys(){
    $retStr = "";
    foreach($this->params as $key => $value){
      $retStr .= "'" . $key . "' ";
    }
    return $retStr;
  } // end getKeys


  ////////////////////////////////////////////////////////////////////
  //
  // getValues() - this function produces a string of all the values
  // return : string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function getValues(){
    $retStr = "";
    foreach($this->params as $key => $value){
      $retStr .= "'" . $value . "' ";
    }
    return $retStr;
  } // end getValues


  ////////////////////////////////////////////////////////////////////
  //
  // getEncodedKeys() - this function produces a string of all the
  //                    keys encoded
  // return : string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function getEncodedKeys(){
    $retStr = "";
    foreach($this->params as $key => $value){
      $retStr .= "'" . urlencode($key) . "' ";
    }
    return $retStr;
  } // end getEncodedKeys


  ////////////////////////////////////////////////////////////////////
  //
  // getEncodedValues() - this function produces a string of all the
  //                      values encoded
  // return : string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function getEncodedValues(){
    $retStr = "";
    foreach($this->params as $key => $value){
      $retStr .= "'" . urlencode($value) . "' ";
    }
    return $retStr;
  } // end getEncodedValues


  ////////////////////////////////////////////////////////////////////
  //
  // debugPrint() - this function prints all the key value pairs from
  //                the perameter array
  // return : string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function debugPrint(){
    print_r($this->params);
  } // end debugPrint

} // end LinkBuilder

?>
