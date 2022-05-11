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
 * File name: XMLResponseParser.php
 * Purpose: The purpose of this php file is to be an Object that can
 *             parse an XML string into an object that is easily useable.
 *             This class assumes the input is from a RocketGate postback
 *             message.
 *
 */

class XMLResponseParser {
  ////////////////////////////////////////////////////////////////////
  //
  // variables
  //
  ////////////////////////////////////////////////////////////////////
  //
  var $params;    // array of key value pairs

  ////////////////////////////////////////////////////////////////////
  //
  // XMLResponseParser() - constructor for the class
  // input: none
  //
  ////////////////////////////////////////////////////////////////////
  //
  function XMLResponseParser(){
    $this->params = array();
  }

  //////////////////////////////////////////////////////////////////////
  //
  // SetFromXML() - take an input string that is an xml document and parse
  //                it into an object that useable.
  // input: xml document in the from of a string
  // output: none
  //
  //////////////////////////////////////////////////////////////////////
  //
  function SetFromXML($xmlString){

    //
    //	Create a parser for the XML.
    //
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

    // Run parser
    //
    if ( xml_parse_into_struct($parser, $xmlString, $vals, $index) == 0) {

      //
      //  If we experienced a parsing error, setup a return
      //  response.
      //
      $msg = "xml_parser_create error: " . xml_error_string(xml_get_error_code($parser));
      // error_log($msg, 0);
      return; // And we're done.
    }


    //
    //	Loop over the items in the XML document and
    //	save them in the response.
    //
    foreach ($vals as $val) {			// Loop over elements
      if (isset($val['value']))			// Is value set?
        $this->Set($val['tag'], $val['value']);	// Save in parameters
    }
    
    //
    //	Release the parser and quit.
    //
    xml_parser_free($parser);			// Release the parser
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
    $this->Clear($key);				// Remove existing value
    $this->params[$key] = $value;		// Save new value
  }


  ////////////////////////////////////////////////////////////////////
  //
  // Get() - get a value from the parameters array
  // input: key of desired value
  // return : value from parameters array or NULL
  //
  ////////////////////////////////////////////////////////////////////
  //
  function Get($key){
    if (array_key_exists($key, $this->params)) {
      $value = $this->params[$key];		// Pull value from list
      $value = trim($value);			// Clean-up the string
      return $value;				// And return it to caller
    }
    return NULL;				// Key was not found
  }


  ////////////////////////////////////////////////////////////////////
  //
  // Clear() - used for clearing values for the array of perameters
  // input : name of key to be cleared
  // return : nothing returned
  //
  ////////////////////////////////////////////////////////////////////
  //
  function Clear($key){
    if (array_key_exists($key, $this->params))	// Does it exist?
      unset($this->params[$key]);		// Clear it
  }

} // end XMLResponseParser

?>
