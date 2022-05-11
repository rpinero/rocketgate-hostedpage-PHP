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
 * File name: LinkReader.php
 * Purpose: This php file defines a class that can verify the
 *             hash value provided by RocketGate in the URL of
 *             a success or failure page.
 *
 *             Once the hash has been validated, the argument
 *             values in the URL are available to applications
 *             using the classes 'Get' function.
 *
 *             This class operates on the name-value pairs AFTER
 *             the '?' in a URL.
 *
 */

class LinkReader {
  ////////////////////////////////////////////////////////////////////
  //
  // variables
  //
  ////////////////////////////////////////////////////////////////////
  //
  var $hash_key;            // secret shared key for the hash function
  var $params;              // array of key value pairs

  ////////////////////////////////////////////////////////////////////
  //
  // LinkReader() - constructor for the class
  // input: shared key string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function LinkReader($key_string){
    //
    // set the key for seeding the hash
    //
    $this->hash_key = $key_string;

    //
    // prepare the parameter array
    //
    $this->params = array();

  } // end constructor


  ////////////////////////////////////////////////////////////////////
  //
  // ParseLink() - This function validates the hash value that is
  //               attached to a URL.  If the hash value computed by
  //               this function is the same as the value in the URL,
  //               the function returns 'true'.
  //
  // input: The input to the function should be the entire string
  //        value after the ? in $_SERVER['REQUEST_URI']
  //
  // return: boolean
  //
  ////////////////////////////////////////////////////////////////////
  //
  function ParseLink($link_string){

    //
    // Intialize the hash value found in the URL.
    //
    $found_hash = "";			// Assume no hash found

    //
    // Get the arguments from the incoming URL
    //
    $value_pairs = explode('&', $link_string);

    //
    // Build up the name value pair array.
    //
    foreach($value_pairs as $key => $value){
      //
      // The value pairs are in the format of 'key=value'.
      // They need to be split.
      //
      list($pair_key, $pair_value) = explode('=', $value);

      //
      // Set the key value pair
      //
      $this->Set($pair_key, urldecode($pair_value));

      //
      // If this is the hash value, save it.
      //
      if(strcmp($pair_key, "hash") == 0)
      	$found_hash = urldecode($pair_value);
    }

    //
    // Compute a hash for the link string.
    //
    $computed_hash = $this->ComputeHash($link_string);

    //
    // Compare the incoming hash with the computed hash.
    //
    return strcmp($found_hash, $computed_hash);

  } // end ConfirmHash


  ////////////////////////////////////////////////////////////////////
  //
  // ComputeHash() - This funcion produces the correct hash value
  //                 for a given URL string.
  //
  // input: the values pair string
  // return: string
  //
  ////////////////////////////////////////////////////////////////////
  //
  function ComputeHash($link_string){
    //
    // Determine the position of the hash variable.
    // It should always be at the end of the string.
    //
    $index_of_hash = strpos($link_string, '&hash');

    //
    // Get the string up to the hash key.
    //
    $no_hash_string = substr($link_string, 0, $index_of_hash);

    //
    // URL Decode the string minus the hash
    //
    $unencoded_str = urldecode($no_hash_string);

    //    
    // Add the shared secret to the decoded string.
    //
    $unencoded_str .= "&secret=" . $this->hash_key;

    //
    // Hash the unencoded string and return the raw output
    //
    $sha1Hash = hash("sha1", $unencoded_str, true);

    //
    //	Note:	Older versions of PHP may not include the
    //		"hash" function.  In that instance, the "pack"
    //		function can be used as shown below.
    //
    //	$sha1Hash = pack("H*", sha1($unencoded_str));

    //
    // Base64 encode the hash output
    //
    $b64 = base64_encode($sha1Hash);

    //
    // return the final base-64 encoded string
    //
    return $b64;

  } // end HashCompute


  ////////////////////////////////////////////////////////////////////
  //
  // Get() - get a value from the parameters array
  // input: key of the value
  // return : value from the key value pair
  //
  ////////////////////////////////////////////////////////////////////
  //
  function Get($key){
    //
    // return the value denoted by the key in the params array
    //
    if (array_key_exists($key, $this->params)) {
      $value = $this->params[$key];		// Pull value from list
      return $value;				// And return it to caller
    }
    return NULL;				// Key was not found
  }

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

} // end LinkReader

?>
