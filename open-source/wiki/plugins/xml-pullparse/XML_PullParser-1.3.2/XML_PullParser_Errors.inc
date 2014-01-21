<?php
/*
Copyright (C) 2006  Myron Turner

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

/**
 *
 * @package    XML_PullParser_Errors
 * @author     Myron Turner <Myron_Turner@shaw.ca>
 * @copyright  2006 Myron Turner
 * @license    http://www.gnu.org/copyleft/lesser.html   LGPL License 2.1
 * @version    1.0.1
 *
*/

/** 
 *
*/

define("XML_PullParser_ERROR_SYSTEM", 0);
define("XML_PullParser_ERROR_MISMATCHED_TAGS",1);
define("XML_PullParser_ERROR_NO_DATA", 2);
define("XML_PullParser_ERROR_NO_DEFAULT_TOKEN", 3);
define("XML_PullParser_ERROR_NO_TOKEN", 4);
define("XML_PullParser_ERROR_BAD_PARAM", 5);
define("XML_PullParser_ERROR_BAD_INTERNAL_ARRAY", 6);
define("XML_PullParser_ERROR_USER_DEFINED", 7);
define("XML_PullParser_ERROR_NS_SUPPORT", 8);
        

$XML_PullParser_ERROR_DEF = array(
    "XML_PullParser_ERROR_SYSTEM",
    "XML_PullParser_ERROR_MISMATCHED_TAGS",
    "XML_PullParser_ERROR_NO_DATA",
    "XML_PullParser_ERROR_NO_DEFAULT_TOKEN",
    "XML_PullParser_ERROR_NO_TOKEN",
    "XML_PullParser_ERROR_BAD_PARAM",
    "XML_PullParser_ERROR_BAD_INTERNAL_ARRAY",
    "XML_PullParser_ERROR_USER_DEFINED",
    "XML_PullParser_ERROR_NS_SUPPORT"
);

$XML_PullParser_ERROR = array(
 "Uknown System Error",
 "Mismatched tags: child element declared in \$tags array or read buffer too small",
 "No Data Available",
 "Default token missing: no current token or \$current_element found",
 "No Token Available",
 "Missing or Wrong Parameter",
 "Required Internal Array Missing, Corrupt, or Empty",
 "User defined error",
 "NamespaceSupport has not been invoked" 
);

$XML_PullParser_Errors_lastError = "";
$XML_PullParser_Errors_errorStack = array();
$XML_PullParser_Errors_lastErrNum = Null;


/**
 *   Constructs a user-defined error message and returns
 *   an XML_PullParser_Errors object 
 *  
 *   @param string $msg  error string
 *   @return XML_PullParser_Errors object 
*/

function XML_PullParser_Errors_userDefined($msg) {
  global $XML_PullParser_ERROR;

  $err =  new XML_PullParser_Errors(XML_PullParser_ERROR_USER_DEFINED,  $msg); 
  $err->err = $XML_PullParser_ERROR[XML_PullParser_ERROR_USER_DEFINED];
  $err->err .= ": $msg";

  $db = debug_backtrace();

  $err->err .= "\nLine: " . $db[0]['line'];
  if(isset($db[1])) {
    $err->err .=", function: " . $db[1]['function'];
  }
  else {
   $err->err .= ", Top Level";
  }
  return $err;
}

/**
 *   Returns a user defined error message 
 *
 *   This method returns the error string 
 *   associated with the XML_PullParser_Errors object 
 *   passed in as a parameter
 *  
 *   @param $obj XML_PullParser_Errors object 
 *  
 *   @return string
*/

function  XML_PullParser_Errors_getUserDefined($obj) {
   return $obj->XML_PullParser_Errors_error();
}


/**
 *   Sets all XML_PullParser_Errors to their original values
 *  
 *   It's necessary to call this function only if you are creating
 *   more than one XML_PullParser object in your script; otherwise
 *   any error messages left over from a previous XML_PullParser object
 *   may still be in memory and, if so, will appear in the next trace.
 *  
*/

function XML_PullParser_Errors_INI() {
  global $XML_PullParser_ERROR, $XML_PullParser_Errors_lastError, $XML_PullParser_Errors_errorStack, $XML_PullParser_Errors_lastErrNum;
  $XML_PullParser_Errors_lastError = "";
  $XML_PullParser_Errors_errorStack = array();
  $XML_PullParser_Errors_lastErrNum = Null;
}

/**
 *   Returns the current error message
 *  
 *   @return string 
*/
function XML_PullParser_Errors_errMsg() {
  global $XML_PullParser_Errors_lastError;
  return $XML_PullParser_Errors_lastError;
}


/**
 *   
 *   Prints out a trace of errors up to and including the current error
 *   
*/

function  XML_PullParser_Errors_Trace()
{
  global $XML_PullParser_ERROR_DEF;

  echo "\n------Error Trace------------\n";
  while ($ref=XML_PullParser_Errors_Ref()) {
      $ref->XML_PullParser_Errors_printError();   
      $errno = $ref->XML_PullParser_Errors_errorNumber();
      Echo "Error Number: ";
      if ($errno == 0) {
            echo "0";      
      }
      else {
          echo $errno;
      }
      echo '  (' . $XML_PullParser_ERROR_DEF[$errno] . ")\n\n"; 
  }
  echo "-----End Trace:-------------\n\n";

}

/**
 *    Returns an XML_PullParser_Errors object popped from an internal
 *    stack which holds all errors up to and including the last error.
 *
 *    It is used by XML_PullParser_Errors_Trace() to output the trace.
 *    Once the trace is done the stack is empty.
 * 
 *    @return XML_PullParser_Errors object 
*/   
function  XML_PullParser_Errors_Ref() {
  global $XML_PullParser_Errors_errorStack;
  if (count($XML_PullParser_Errors_errorStack)) {
     return array_pop($XML_PullParser_Errors_errorStack);
  }
  return Null;
}


/**
 *    Returns the current Error number
 *
 *    @return integer
*/   

function  XML_PullParser_Errors_Num() {
  global $XML_PullParser_Errors_lastErrNum;
  return $XML_PullParser_Errors_lastErrNum;
}

/**
 *  Error processing class for XML_PullParser
 *
 * @package    XML_PullParser_Errors
 * @author     Myron Turner <Myron_Turner@shaw.ca>
 * @copyright  2006 Myron Turner
 * @license    http://www.gnu.org/copyleft/lesser.html   LGPL License 2.1
 * @version    1.0.0
 *
*/


class XML_PullParser_Errors
{


var $err;
var $err_num;
var $err_str;

function XML_PullParser_Errors($error_num,  $extra_info="") {
  global $XML_PullParser_ERROR, $XML_PullParser_Errors_lastError, $XML_PullParser_Errors_errorStack, $XML_PullParser_Errors_lastErrNum;

  $this->err_num = $error_num;
  $this->err_str = $XML_PullParser_ERROR[$error_num];
  $this->err = $XML_PullParser_ERROR[$error_num];

  if ($extra_info) {
        $this->err .= ": $extra_info";
  }
  $db = debug_backtrace();
  $this->err .= "\nLine: " . $db[0]['line'] . ", function: " . $db[1]['function'];

  $XML_PullParser_Errors_lastError = $this->err;
  $XML_PullParser_Errors_lastErrNum = $this->err_num;
  array_push($XML_PullParser_Errors_errorStack, $this);
  

}

function XML_PullParser_Errors_errorStr() {
    return $this->err_str;
}

function XML_PullParser_Errors_errorNumber() {
    return $this->err_num;
}

function XML_PullParser_Errors_error() {
  return $this->err;
}

function XML_PullParser_Errors_printError() {
  echo "ERROR:  " . $this->err . "\n";
}

}

?>
