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
 * @category   XML
 * @package    XML_PullParser
 * @author     Myron Turner <Myron_Turner@shaw.ca>
 * @copyright  2006 Myron Turner
 * @license    http://www.gnu.org/copyleft/lesser.html   LGPL License 2.1
 * @version    1.3.2
 *
*/


/**
 *  Pull in Errors module
 *
*/

    if(isset($XML_PullParser_Errors_Path) && 
                             is_readable($XML_PullParser_Errors_Path . "/XML_PullParser_Errors.inc")) {
      include  ($XML_PullParser_Errors_Path . "/XML_PullParser_Errors.inc");// user-defined directory
    }

    else {
       require_once "XML_PullParser_Errors.inc";
    }


// Constants


// Globals


/**
 *  internal variable
 *
 *  @global array $GLOBALS['_XML_PP_tagsArray'] 
 *  @name $_XML_PP_tagsArray
 *  
*/
	$GLOBALS['_XML_PP_tagsArray'] = array(); 

/**
 *  internal variable
 *
 *  @global array $GLOBALS['_XML_PP_childtagsArray'] 
 *  @name $_XML_PP_childtagsArray
 *  
*/
	$GLOBALS['_XML_PP_childtagsArray'] = array(); 


/**
 *  The maximum length of data which fread reads in with each file access
 *   
 *  @global integer $GLOBALS['XML_PullParser_readLength'] 
 *  @see XML_PullParser_setReadLength
 *  
*/
	$GLOBALS['XML_PullParser_readLength'] =  8192;   


/**
 *  Case sensitivity is switched off by  default. Setting this to true to produces case sensitive processing.
 *  @global boolean $GLOBALS['XML_PullParser_caseSensitive'] 
 *  @see XML_PullParser_caseSensitive
 *  
*/
	$GLOBALS['XML_PullParser_caseSensitive'] = FALSE;   

/**
 *  Enables the by-passing of blank strings during the parsing of character data. 
 *  Blank strings are defined as strings containing only white space
 *  @global boolean $GLOBALS['XML_PullParser_XCLUDE_BLANKS'] 
 *  @name $XML_PullParser_XCLUDE_BLANKS
 *  @see XML_PullParser_excludeBlanks
 *  
*/
$GLOBALS['XML_PullParser_XCLUDE_BLANKS'] = false;   

/**
 *  Setting this to true will exclude all text which does not contain
 *  alphanumeric characters and underscore [_]. It tests the text
 *  against the Perl regular expression <b>\w</b>.
 *
 *  @global boolean $GLOBALS['XML_PullParser_XCLUDE_BLANKS_STRICT'] 
 *  @name $XML_PullParser_XCLUDE_BLANKS_STRICT
 *  @see XML_PullParser_excludeBlanksStrict
*/
$GLOBALS['XML_PullParser_XCLUDE_BLANKS_STRICT'] = false;   

/**
 *  Setting this to TRUE causes all packets of CDATA to be passed through
 *  the PHP trim function
 *  @global boolean $GLOBALS['XML_PullParser_TRIM_CDATA'] 
 *  @name $XML_PullParser_TRIM_CDATA
 *  @see XML_PullParser_trimCdata
 *  
*/
$GLOBALS['XML_PullParser_TRIM_CDATA'] = false;   

/**
 *  Setting this to TRUE enables Namespace support
 *  
 *  @global boolean $GLOBALS['XML_PullParser_NAMESPACE_DEF'] 
 *  @name $XML_PullParser_NAMESPACE_DEF
 *  
*/

$GLOBALS['XML_PullParser_NAMESPACE_DEF'] = false;   


/**
 *  This governs whether namepace prefixes are left in place
 *  
 *  Default is False.  Prefixes are respected unless this is set to TRUE.
 *  To do this call the package level function
 *  {@link XML_PullParser_Disable_NS_Prefixes} with a value of TRUE.
 *  
 *  @see XML_PullParser_Disable_NS_Prefixes
 *  @global boolean $GLOBALS['XML_PullParser_DisableNamespacePrefixes'] 
 *  @name $XML_PullParser_DisableNamespacePrefixes
 *  
*/

$GLOBALS['XML_PullParser_DisableNamespacePrefixes'] = false;   

// End Globals


// Start Package Level Functions

/**
 * 
 *
 *    This function creates the $tags array--{@link XML_PullParser::$tags}--that XML_PullParser uses to identify 
 *    the elements which are of interest and which will be returned in tokens from calls
 *     to {@link XML_PullParser_getToken}.
 *
 *    The returned tokens will include everything between the Start and End tags of these elements,
 *    including the Start and End tags. 
 *
 *    It accepts as parameters either an array of strings or a variable length parameter list of strings
 *    identifying the elements of interest. 
 *
 *    This list must not include the names of elements which are children of elements named
 *    in the list, unless they are also declared in the {@link XML_PullParser::$child_tags} array,
 *    in which case they are escaped.  See {@link XML_PullParser::$_escaped_tags }. 
 *    
 *    Child elements of interest should be listed separately in the $child_tags array, which
 *    can be passed in through the contructor or set in {@link XML_PullParser_declareChildElements}. 
 *    
 *    With this function, one pre-declares the tags of interest; alternatively,
 *    an array of element names can be passed in to the PullParser constructor. 
 *
 *    @see XML_PullParser_getEscapedToken()
 *    @see XML_PullParser_declareChildElements 
 *    @see XML_PullParser::$child_tags 
 *    @see XML_PullParser::$tags 
 *     
 *    @param mixed $tags
 *    @return array
 * 
*/
function XML_PullParser_declareElements($tags) 
{
    global $_XML_PP_tagsArray;
  
    if (is_array($tags)) {
        foreach ($tags as $tag) {
            if (!is_string($tag)) {
                print 'Incorrect type found in $tags array: ' . $tag . "\n";
                exit;
           }
      }
    $_XML_PP_tagsArray = $tags;
        return $_XML_PP_tagsArray;  
   } 
    $arg_list = func_get_args();

        for ($i = 0; $i < func_num_args(); $i++) {
        $_XML_PP_tagsArray[] = $arg_list[$i];
            if (!is_string($arg_list[$i])) {
                print 'Incorrect type found in $tags array: ' . $tag . "\n";
                exit;
           }
    }


    return $_XML_PP_tagsArray;
}


/**
 *    Used to predeclare the child tags of interest.
 *    
 *    This function takes as its parameter either an array of element names or a
 *    variable length list of element names. It then constructs the $child_tags array
 *    from these names.
 *
 *    @param mixed $tags  
 *    @return array
 *    @see XML_PullParser::$child_tags
 *
*/


function XML_PullParser_declareChildElements($tags) 
{
    global $_XML_PP_childtagsArray;


    if (is_array($tags)) {
        foreach ($tags as $tag) {
            if (!is_string($tag)) {
                print 'Incorrect type found in $tags array: ' . $tag . "\n";
                exit;
           }
        $_XML_PP_childtagsArray[$tag] = true;
      }

        return $_XML_PP_childtagsArray;  
   } 
    $arg_list = func_get_args();

        for ($i = 0; $i < func_num_args(); $i++) {
        $_XML_PP_childtagsArray[$arg_list[$i]] = true;
            if (!is_string($arg_list[$i])) {
                print 'Incorrect type found in $tags array: ' . $tag . "\n";
                exit;
           }
    }

    return $_XML_PP_childtagsArray;
}

/**
 *  To be called when more than one XML_PullParser session is created.
 *  
 *  It resets the two global arrays which initialize the tags and child tags arrays,
 *  setting them both back to the empty array.  
 *  Unless this function is called, each new instance will still hold array
 *          elements from the previous instance.
 *  
 *
*/

function XML_PullParser_reInitTagArrays() 
{
    global $_XML_PP_childtagsArray;
    global $_XML_PP_tagsArray;

    $_XML_PP_childtagsArray = array();
    $_XML_PP_tagsArray = array();
}

/** 
 *
 * This function sets the length of the data which fread will read at each file access.
 * It has effect only if called before the XML_PullParser constructor 
 *
 * The default read size is 8kb.  But if any of the elements declared in either the
 * {@link XML_PullParser::$tags} array or the {@link XML_PullParser::$child_tags} array holds more than 8kb, then the
 * read size must be increased to a size that will accommodate it.  The xml file
 * itself can be any size.  But XMLPull_arser has to be able to read in everything between
 * the Start and End tags of the largest element declared in either of these arrays.         
 *
 *      @param integer $num   fread will read $num * 4096 bytes (the default is 2, i.e. 8kb)  
 *
*/

function XML_PullParser_setReadLength($num=2)
{
    global $XML_PullParser_readLength;
    $XML_PullParser_readLength = $num*4096; 
}

/**
 *  Setting this to true will exclude all text lines which
 *  consist solely of \t \n \r \s \f
 *
 *  @param boolean $bool
 *  @see $XML_PullParser_XCLUDE_BLANKS 
*/

function XML_PullParser_excludeBlanks($bool)
{
    global $XML_PullParser_XCLUDE_BLANKS;

    $XML_PullParser_XCLUDE_BLANKS = $bool; 
}

/**
 *  Calling this with a true value will exclude all
 *  text lines which do not contain alphanumeric characters,
 *  the hyphen, the underscore
 *
 *  @param boolean $bool
 *  @see $XML_PullParser_XCLUDE_BLANKS_STRICT
 *
*/
function XML_PullParser_excludeBlanksStrict($bool)
{
    global $XML_PullParser_XCLUDE_BLANKS_STRICT;

    $XML_PullParser_XCLUDE_BLANKS_STRICT = $bool; 
}

/**
 *  
 *  Calling this function with a value of
 *  TRUE will cause all CDATA packets to be passed
 *  through the PHP trim function
 *  
 *  @see $XML_PullParser_TRIM_CDATA
 *  @param boolean $bool
*/
function XML_PullParser_trimCdata($bool)
{
  global $XML_PullParser_TRIM_CDATA;
  $XML_PullParser_TRIM_CDATA = $bool;
}


/**
 *  To implement case sensitive parsing of element and attribute identifiers, call this function with the parameter set to TRUE.
 *
 *  By default PHP converts all element and attribute names to upper case.
 *  XML_PullParser follows this protocol, called 'case-folding.'
 *
 *  <br>
 *  <b>Note:</b> Case-folding is not a function of the underlying expat processor.  Conversion
 *  to upper-case occurs only after the expat processor has done its work, which means that
 *  the XML document must conform to the rule requiring XML documents to be case-sensitive.
 *  
 *
 *  @param boolean $bool  
 *
**/

function XML_PullParser_caseSensitive($bool)
{
    global $XML_PullParser_caseSensitive;
    $XML_PullParser_caseSensitive = $bool;
}


/**
 *  Calling this function with a value of
 *  TRUE enables Namespace support
 *  
 *  @see $XML_PullParser_NAMESPACE_DEF
*/
function XML_PullParser_NamespaceSupport($bool)
{
    global $XML_PullParser_NAMESPACE_DEF;
    $XML_PullParser_NAMESPACE_DEF = $bool;
}
 

/**
 *  Controls handling of namespace prefixes when namespace support is <b>not</b> invoked
 *  
 *  Calling this method with TRUE will remove namespace prefixes
 *  from the names of elements and attributes.
 *
 *  The default behavior is to leave prefixes in place.  When prefixes are in effect,
 *  two elements of the same name, one with a prefix and one without, are treated as two
 *  different elements. This default behavior is consistent with releases prior to 
 *  version 1.3.1, when this functionality was added.
 *
 *  @see $XML_PullParser_DisableNamespacePrefixes
 *  @param $bool boolean
*/
function XML_PullParser_Disable_NS_Prefixes($bool) {
   global $XML_PullParser_DisableNamespacePrefixes;

   $XML_PullParser_DisableNamespacePrefixes = $bool;

}


function _pr_pre($ar)
{
echo "<br>------<pre>";
print_r($ar);
echo "</pre><br>------<br>";
}


// End Package Level Functions



// Start Class Definition

/**
 *
 * XML_PullParser is a token-based interface to the PHP expat XML library.
 * 
 * It moves the the API of the php XML facility from an event-based model to a token-based model. 
 * Instead of procesing data as it is passed from the parser to callbacks, a script using
 * PullParser requests "tokens" from XML_PullParser_getToken(). Tokens are arrays representing 
 * XML structures, which become available in the order in which they appear in the 
 * document being parsed.
 *
 * Methods are provided to get tokens and extract their data.  The API consists of the
 * methods and functions with the XML_PullParser_ prefix.  Methods beginning with the
 * underscore are internal.
 *
 * All class methods which return tokens return NULL when tokens are not available and  
 * so can be used in while loops:
 *   <pre> 
 *          while($token=$parser->XML_PullParser->getToken()){
 *          }
 *   </pre>
 *
 *  Similarly, all data accessors return either NULL, or the empty string or array when no data
 *  is available.
 *  
 *  
 *  Documentation and examples are available in this Class Documentation created using phpDocumentor;
 *  in the manual pages, which were created from XML files using XML_PullParser; and in the sample files,
 *  which are complete PHP files based on the manual code listings.
 *
 * @package XML_PullParser 
 * @author Myron Turner <Myron_Turner@shaw.ca>
 * @license    http://www.gnu.org/copyleft/lesser.html   LGPL License 2.1
 * @version 1.3.2
 *   
 *
*/

class XML_PullParser {



/**
 *    @var array $tags
 *   
 *    The list of tag names of elements on which XML_PullParser will report.  The format of this array is:
 *    <pre>
 *     array ("element_1", "element_2", . . "element_n")
 *    </pre>  
 *    It can be passed into the constructor or predeclared using XML_PullParser_declareElements.
 *  
 *    @see XML_PullParser_declareElements
*/
var $tags; 	       


/**
      
 *    @var array $child_tags  
 *
 *    An array of child elements of interest, which is used by the constructor.
 *    
 *
 *    It can be passed into the constructor as an array of names 
 *    or set using XML_PullParser_declareChildElements
 *
 *      @see XML_PullParser_declareChildElements
*/
var $child_tags;       


/**
 *    @var array $accumulator		internal
*/
var $accumulator;      // array that holds the parsed data:  each element in the array points to a tag, 
                       // an array of attributes, or a string of character data   


/**
 *    @var array $top_level_tags 	internal
*/
var $top_level_tags;   // an array which is an index into the accumulator -- used for the _getTokenRaw()
                       // it points to the start and end tags of elements specified in the tags array 

/**
 *  @var  resource $xml_parser 	a resource handle for referencing the XML instance
*/
var $xml_parser;       // php xml_parser object


/**
 *  @var  resource $fp	file pointer to xml document
*/
var $fp;


/**
 *  @var array $current_slice	internal
*/
var $current_slice;     //  This is a slice of the accumulator created in _getTokenRaw().
                        //  It is the raw basis for the $current_tag_array and the $converted_token
                        //  
                        //  It is case enfolded or not depending on the PHP XML case-enfolding option

/**
 *  @var array $current_tag_array   internal
*/
var $current_tag_array;      /*  This array is the basis for arrays returned by XML_PullParser_getElement().
 
                                It is created from $current_slice in _processToken(), includes only
                                elements specified in the $child_tags array.                             
                                       
                                It is an associative array, which groups common elements in 
                                numerically indexed sub-arrays, that are referenced through
                                the element name: 

                                    TITLE => Array (
                                            [0] S__TITLE
                                              . . . .
                                            [3] E__TITLE
                                            [4] S__TITLE
                                               . . .  
                                            [6] E__TITLE 
                                           )
                                    NAME => Array (
                                            ) 

                                  $title = XML_PullParser_getElement("TITLE") will return the array
                                  pointed to by "TITLE". */
                           
                             


/**
 *
 *  @var array $current_element 
 *
 *   (internal) the default token for various data accessors, generally consisting of elements 
 *   declared in the {@link $child_tags} array.  It is created by {@link XML_PullParser_getElement}
 *
 * 
*/
var $current_element;    // points to the last element structure returned by XML_PullParser_getElement():
                         // $current_tag_array[$el]

/**
 *  @var array $converted_token 
 *
 *     This holds a copy of the token returned by {@link XML_PullParser_getToken}.
 *     It is the fall-back for several of the data accessors when no other usable token,
 *     including {@link $current_element}, is available for their searches.
 *     It is often referred to in this documentation and in the manual as "current token." 
*/
var $converted_token;



/**  
 *  @var string $stream the data stream
*/
var $stream;


/**
 *  @var array  internal
*/
var $push_back_stk;   //  index into the accumulator tracking previously accessed tokens


/**
 *  
 *  @var integer $read_length  sets length of fread buffer
 *  @see XML_PullParser_setReadLength
*/
var $read_length;


/**
 *  
 *  @var string $_strippedCdataDelimiter  sets the delimiter which separates
 *       text elements parsed by XML_PullParser_getTextStripped
 *  @see XML_PullParser_getTextStripped
*/
var $_strippedCdataDelimiter = ' ';

/**
 *  Elements declared in both the $tags array and the $child $tags array.  
 *
 *  A separate stack is maintained for these
 *  and they are accessed through {@link XML_PullParser_getEscapedToken}
 *
*/
var $_escaped_tags;


/**
 *
 * @var mixed $XML_PullParser_currentNS holds the current namespace definition
 *      will be a string if only one namespace is declared, an array if more than one
 *
*/
var $XML_PullParser_currentNS = Null;
/*

  @var boolean (internal)

var $_escapes_ini = false;
*/


/**
 *
 *  @var integer pointer into the attribute loop 
 *  @see XML_PullParser_setAttrLoop 
 *  @see XML_PullParser_nextAttr
 *  @see XML_PullParser_resetAttrLoopPtr
*/
var $_attr_loop_pos = 0;

/**
 *    $_next_element_array == $current_element; 
 *    the assigment is made in in XML_PullParser_getElement 
 *
 *    It is used by XML_PullParser_nextElement 
 *    as a stack for returning the next element; 
 *    When the final element is removed the stack is 
 *    exhausted
 *
 *  @see XML_PullParser_getElement
 *  @see XML_PullParser_nextElement
 *
*/
var $_next_element_array = array();

/**
 *  Created in XML_PullParser_setAttrLoop
 *  and used as a stack by XML_PullParser_nextAttr
 *  to return the next attribute.
 *  This stack remains in memory until the next call
 *  to XML_PullParser_setAttrLoop or XML_PullParser_setAttrLoop_elcd
 *  
 *  The stack pointer can be reset to the top of the stack
 *  using XML_PullParser_resetAttrLoopPtr
 *
 *  @see XML_PullParser_resetAttrLoopPtr
 *  @see XML_PullParser_setAttrLoop
 *  @see XML_PullParser_setAttrLoop_elcd
 *  @see XML_PullParser_nextAttr
*/
var $_attr_loop_array = array();


var $_in_stream = false;

/**
 *  used by methods which silently reset the $current_element
 *  to an alternate token
 *
*/
var $_save_current_element;

/**
 *   PHP XML Callback:  Internal
*/
function _characterData($parser, $data) 
{

    array_push($this->accumulator,array("cdata" => $data)); 
}



/**
 *   PHP XML Callback:  Internal
*/
function _startElement($parser, $name, $attrs = NULL)
{
  global $XML_PullParser_DisableNamespacePrefixes, $XML_PullParser_NAMESPACE_DEF;

   if(preg_match('/\|/', $name)) { 
      list($ns,$name) = explode ('|', $name);   
      if(isset($ns)) {
         $attrs['_ns_'] = array($ns=>true);
      }
   }

   // this removes namespace prefixes when namespace support has not been invoked
    if(!$XML_PullParser_NAMESPACE_DEF  && $XML_PullParser_DisableNamespacePrefixes) {
        if(preg_match('/\:/', $name)) { 
            list($ns,$name) = explode (':', $name);
        }

        if($attrs) {
            $tmp = $attrs;
            foreach($tmp as $attr=>$val) {
                if($replaced = preg_replace('/^\w+\:(.*)/', "\\1", $attr)) {
                    unset($attrs[$attr]);
                    $attrs[$replaced] = $val;
                }
            }
        }
   }

        foreach ($this->tags as $tagname) {
            if ($tagname == $name) {
                $tcount = count($this->accumulator);
                if (!isset($this->child_tags[$tagname])) { // prevent 'interleaving' between top_level and child tags
   	    	        $this->top_level_tags[] = array($tcount, 'S__' . $name);                    
                }
                else $this->_escaped_tags[] = array($tcount, 'S__' . $name);
            }
        }




        array_push($this->accumulator, $name,$attrs);
}


/**
 *   PHP XML Callback:  Internal
*/
function _endElement($parser, $name)
{
 global $XML_PullParser_DisableNamespacePrefixes;
 if(preg_match('/\|/', $name)) { 
       list($ns,$name) = explode ('|', $name);
     }
    elseif($XML_PullParser_DisableNamespacePrefixes  && preg_match('/\:/', $name)) { 
       list($ns,$name) = explode (':', $name);
     }


        foreach ($this->tags as $tagname) {
            if ($tagname == $name) {
            if (!isset($this->child_tags[$tagname]) )    
        	    $this->top_level_tags[] = array(count($this->accumulator), 'E__' . $name);
               else
                $this->_escaped_tags[] = array(count($this->accumulator), 'E__' . $name);
            }
        }

        array_push($this->accumulator,"E__" . $name); 
}



function _externalEntityParser($parser, $openEntityNames, $base, $systemId, $publicId)
{
    if ($systemId) {
        if (!list($parser, $fp) = $this->_createParser($systemId)) {
            printf("Could not open entity %s at %s\n", $openEntityNames,
                   $systemId);
            return false;
        }
        xml_set_object($parser, $this);
        while ($data = fread($fp, 4096)) {
            if (!xml_parse($parser, $data, feof($fp))) {
                printf("XML error: %s at line %d while parsing entity %s\n",
                       xml_error_string(xml_get_error_code($parser)),
                       xml_get_current_line_number($parser), $openEntityNames);
                xml_parser_free($parser);
                return false;
            }
        }
	fclose($fp);
        xml_parser_free($parser);
        return true;
    }
    return false;
}

/**
 *   Initialize the XML Parser:  Internal
 *   @param  path to XML file
 *   @return array (resource: xml parser handle, resource: file handle) 
*/
function _createParser($file) 
{
 global $XML_PullParser_NAMESPACE_DEF;

    if($XML_PullParser_NAMESPACE_DEF) {
        $xml_parser = xml_parser_create_ns("ISO-8859-1", '|'); 
    }
    else {
        $xml_parser = xml_parser_create();
    }

    xml_set_element_handler($xml_parser, "_startElement", "_endElement");
    xml_set_character_data_handler ($xml_parser, "_characterData");
    xml_set_external_entity_ref_handler($xml_parser, "_externalEntityParser");

    if (!($fp = fopen($file, "r"))) {
        die("\nCould not open XML input file: $file\n");
   }

    $this->_in_stream = true;
    return array($xml_parser, $fp);
}

/**
 *   Get next chunk of data from XML Parser:  Internal
*/
function _readData() 
{
       if (!isset($this->fp)) {
          $error = new XML_PullParser_Errors(0, "XML file was opened but handle was not found");
          die($error->XML_PullParser_Errors_error());
          return false;
       }

       $eostream = false;
       $this->accumulator = array();       
       $this->top_level_tags = array();
       $this->push_back_stk = array();

       $this->stream = fread($this->fp, $this->read_length);
       if(feof($this->fp)) {
          $eostream =true;
          $this->_in_stream = false;
       }

       if (!xml_parse($this->xml_parser, $this->stream, feof($this->fp))) {
	        if(!$eostream && xml_get_error_code($this->xml_parser)!= XML_ERROR_JUNK_AFTER_DOC_ELEMENT) {
                die(sprintf("XML error: %s at line %d\n",
                        xml_error_string(xml_get_error_code($this->xml_parser)),
                        xml_get_current_line_number($this->xml_parser)));
			}
        }

        return true;
}


// Namespace specific methods

/**
 *  Creates the current namespace definition
 *  
 * It takes a single parameter,  a string consisting of one or more
 * namespace URI's.  They must be exactly as definied in the XML document. If
 * there is a trailing forward slash in the URI, then this must be included. 
 * If more than one namespace is passed in, they must separated by the vertical
 * bar:
 * <pre>   
 *    $parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|"
 *      . "http://room535.org/movies/mov/|http://room535.org/movies/star/");
 *
 * </pre>   
 *  
 * 
 *   This method will return FALSE if namespace support has not been invoked
 *   in advance using {@link XML_PullParser_NamespaceSupport} and will not set the namespace definition;
 *   otherwise it sets the new namespace definition and returns the previously set definition,
 *   which is suitable for passing back into the method;
 *   if there is no previous namespace definition, it sets the definition to <b>$ns</b> and returns True.  
 *
 *
 * @param string $ns 
 * @return mixed 
 *
*/
function XML_PullParser_setCurrentNS($ns) {
  global $XML_PullParser_NAMESPACE_DEF;
  $prev_ns = $this->XML_PullParser_currentNS;

    if(!$XML_PullParser_NAMESPACE_DEF) {
	   $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NS_SUPPORT,
         	"\n\tXML_PullParser_NamespaceSupport must be called before XML_PullParser_resetCurrentElement");  
    	$err->XML_PullParser_Errors_printError();
		return FALSE;
    }

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $ns = strtoupper($ns);
    }

  if(preg_match('/\|/',$ns)) {
      $this->XML_PullParser_currentNS = explode('|', $ns);
  }
  else {
    $this->XML_PullParser_currentNS = $ns;
  }

  if(isset($prev_ns) && $prev_ns) {
    if(is_array($prev_ns)) {
       return implode('|', $prev_ns);
    }
    else return $prev_ns;
  }
  return TRUE;
}


/**
 *  Unsets the current namespace definition 
 *  
 *  When namespace support is in effect and the current namespace definition
 *  is unset, XML_PullParser behaves as though the XML document had no namespaces.
 *  @return string previously set namespace definition  
*/

function XML_PullParser_unsetCurrentNS() {
  $prev_ns = $this->XML_PullParser_currentNS;
  $this->XML_PullParser_currentNS = Null;
  return $prev_ns;
}


/**
 *  Extracts the namespace URI from an internally constructed key for either
 *  attributes or elements
 *  
 *
 *
 *  Element namespaces are held along with attributes in the attribute array assigned to an element:
 *  <pre>   
 *  Array
 *  (
 *     [HTTP://EXAMPLE.COM/DNS.TXT/|IP] => 192.168.10.3
 *     [_ns_] => Array
 *        (
 *            [HTTP://EXAMPLE.COM/DNS.TXT/] => 1
 *        )
 *  ) 
 * </pre> 
 * 
 *  These arrays are returned by {@link XML_PullParser_getAttributes} or can be extracted
 *  from the arrays returned by {@link XML_PullParser_getAttrValues} and {@link XML_PullParser_nextAttr}
 *
 *  <OL>
 *  <LI> If the parameter is a string, this method assumes that it is the internal name of an attribute,
 *  as in the example above: HTTP://EXAMPLE.COM/DNS.TXT/|IP
 *  <LI> If the parameter is an array and $name is not specified, it assumes that   
 *   the element's own namespace is being sought.  This is held in '_ns_'; 
 *  <LI> If the parameter is an array and $name is specified, it looks for
 *  the attribute of that $name. 
 *  </OL>
 *
 *  @param mixed $str the internally constructed attribute name or an attribute array,
 *  @param string $name  optional name of the attribute; this is the unqualified name, i.e. without either
 *  the namespace URI or the namespace prefix
 *
 *  @return string namespace URI or NULL if not found
*/


function XML_PullParser_getNS_URI($str, $name=Null) {

   if(is_array($str)) {
      if(!$name && array_key_exists('_ns_', $str)) {
             $str = $str['_ns_'];
             $str = array_keys($str);  
             if(isset($str[0])) {
               return $str[0];
           }
      }
      elseif($name) {
         if($this->XML_PullParser_isCaseFolded()) {
             $name = strtoupper($name);
         }
         $keys = array_keys($str);
         foreach($keys as $key) {
            if(preg_match('/\|/',$key)) {
                 list($ns,$attr_name) = explode('|', $key);
                 if($attr_name == $name) {
                      return $ns;
                 }
            }
         }
      }
     return Null;
   } 

    $key = NULL; 
    if(preg_match('/\|/',$str)) {
        list($key,$val) = explode('|', $str);
     }
     return $key;
}


/**
 * Gets unqualified attribute name from the internally created attribute key
 *
 * The attribute <b>key</b> is created internally for namespace-qualified attributes
 * from both the attribute name and the namespace
 *  
 * @param string the internally created attribute key for this attribute
 * @return string the attribute name extracted from the key
*/
function XML_PullParser_getNS_AttrName($str) {
    if(!is_string($str)) {
       return Null;       
    }

    $name = NULL; 
    if(preg_match('/\|/',$str)) {
        list($ns,$name) = explode('|', $str);
     }
     return $name;
}

/**
 *  This is used to test whether an element or an attribute falls within the current
 *  namespace definition.
 *
 *  This is mainly for internal use, particularly insofar as it is applied to elements.
 *  But it can be used by the programmer to determine whether an attribute
 *  resides within the current namespace definition.  This can be done first by
 *  extracting the namespace URI from the attribute's name, using {@link XML_PullParser_getNS_URI},
 *  and then passing into _is_current_NS() the URI and the attribute's value 
 *  as key=>value array: 
 *  <pre>
 *     $name = XML_PullParser_getNS_URI($name);
 *     if(_is_current_NS(array($name=>$value)) ) {
 *     }
 * </pre>
 *  @param array $ns_array this is a single element array of the type key=>value,
 *		where the key is the name space string and the value either an attribute value or TRUE		
 *  @return boolean  
*/
function _is_current_NS($ns_array) {

    if(!$this->XML_PullParser_currentNS) {
        return True;
    }

    if(is_array($this->XML_PullParser_currentNS)) {
        foreach($this->XML_PullParser_currentNS as $ns_key) {
            if(array_key_exists($ns_key, $ns_array)) {

                return true;
            }
        }
 }
 else {
    if(array_key_exists($this->XML_PullParser_currentNS, $ns_array)) {
        return true;
    }
 }

 return false;
}


/**
 *  Get the value of an attribute if it falls within the current namespace definition 
 *
 *  This method was designed primarily for internal use but may have applicability
 *  in some scripting situations.  Generally, however, {@link XML_PullParser_getAttrVal}
 *  should be used to get attribute values.
 *
 *  @param string $name  name of the attribute without its namespace qualification
 *  
 *  @param array $attr_array an assocative array consisting of the attribute's name 
 *  and value, formed as follows:
 *  <pre>
 *         attribute-name=>attribute-value 
 *  </pre>
 *  <b>attribute-name</b> is the name supplied by XML_PullParser_NS, which is an
 *  internally constructed key.  The keys can be derived from the attribute arrays supplied
 *  by {@link XML_PullParser_getAttributes}, {@link XML_PullParser_nextAttr}
 *  and {@link XML_PullParser_getAttrValues}
 *
 *  @return string the value of the attribute or NULL if the attribute name is not qualified by a namespace
 *
*/
function XML_PullParser_getAttr_NS($name, $attr_array) {

  list($attr,$val) = each ($attr_array);

  if(is_array($val)) {
      return Null;      
  }

  if(preg_match('/\|/', $attr)) { 
      list($ns,$name_2) = explode ('|', $attr); 

      if(isset($ns) && $name_2 == $name) {
        if($this->_is_current_NS(array($ns=>$val))) {
           return $val;
        }
      }

   }

   return Null;

}


// END Namespace specific methods



function _getCurrentPosition() 
{
    $byte = xml_get_current_byte_index ($this->xml_parser);
    $line = xml_get_current_line_number ($this->xml_parser);
    $col = xml_get_current_column_number ($this->xml_parser);
  
    return array($byte, $line, $col);
}


/**
 *  Converts token returned by {@link _getTokenRaw} to a form compatible with
 *  the tokens returned by {@linkXML_PullParser_getElement}
 *
 *  This method is essentially internal and is called by {@link XML_PullParser_getToken}.
 * 
 *  Unless the raw token is converted, the PullParser data accesors are not available:
 *  XML_PullParser_getAttributes,  XML_PullParser_getText,  
 *  XML_PullParser_getTextStripped, XML_PullParser_getChild,  XML_PullParser_getChildren  
 *	
 *   @param array $token
 *   @return array
*/
function _convertToken($token) 
{
    $end_tags = array();
    foreach ($token as $tag) {
        if (!is_array($tag) && preg_match('/^E__(.*)$/', $tag, $matches)) {
          $end_tags[] = $matches[1];         
     }
  }

        foreach ($end_tags as $etag) {  
            for ($i=0; $i < count($token); $i++) { 
            if (!is_array($token[$i]) && $token[$i] == $etag) {
                $token[$i] = 'S__' . $etag;             
           }
        }
   }
  if (!$this->_aligned($token)) {
    $err = new XML_PullParser_Errors(XML_PullParser_ERROR_MISMATCHED_TAGS);
    $err->XML_PullParser_Errors_printError();
    return false;
  }
  $this->converted_token = $token;
    return $token;
}

// check for misaligned tokens -- see _convertToken
function _aligned($token)
{
  $cnt = count($token);

  if (is_string($token[0]) && is_string($token[$cnt-1])) {
       list($prefix_S,$name_S) = explode("__", $token[0]);
       list($prefix_E,$name_E) = explode("__", $token[$cnt-1]);
       if ($prefix_S != "S" && $prefix_S != "E") {        
               return false;
       }
       return $name_S == $name_E;
                      
  }
  
  return false;
}


/**
 *   XML_PullParser_getToken initializes and returns the next top level element and all of its children for use
 *   with the class data access methods. The top level elements are those delcared in
 *   the tags array: {@link XML_PullParser::$tags}.
 *
 *  This  method is the workhorse of XML_PullParser. It is repeatedly called, most typically
 *  in a while loop, to fetch the next token off the token stack.  Each token consists 
 *  of an element declared in the tags array and all of its dependent child elements.
 *  The tags array is pre-declared in {@link XML_PullParser_declareElements} or passed in through
 *  the constructor.
 *
 *  The companion to this method is {@link XML_PullParser_getElement}, which returns elements declared in 
 *  in the child tags array: {@link XML_PullParser::$child_tags}.
 *
 *  @return array   
 *	an array consisting of the next element in the
 *  token stack and all of its dependent children.
 *  @see XML_PullParser_declareElements
 *
 * 
*/
function XML_PullParser_getToken() 
{

      if(!$resp = $this->_getTokenRaw()) {
      if($resp === FALSE && $this->_in_stream) {        
             $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DATA);          
             return False;
           }
         return Null;
      }

    $this->_processToken();

    return $this->_convertToken($this->current_slice);
}


/**
 *    Returns the next available raw token and initializes a number of internal data structures.
 *    Its return value cannot be used with the PullParser data access functions.  
 *
 *    
 *    For full functionality, use PullParser::XML_PullParser_getToken.  
 *    @see XML_PullParser_getToken
 *
 *    @return array
*/
function _getTokenRaw() 
{
    if (count($this->top_level_tags) <= 1) {

      $last = false;
      $rest = false;
        if (count($this->top_level_tags) == 1) {
           $last = array_shift($this->top_level_tags);  // get the last tag
           $rest = array_slice($this->accumulator,$last[0]); // save the end of acumulator from $last onward
      }
        if (!$resp = $this->_readData()) { 
           if($resp === FALSE && $this->_in_stream) {        
             $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DATA);          
           }
            return false;
        }

        if ($last) {     
         $end =  array_shift($this->top_level_tags);
         $end_stuff = array_slice($this->accumulator, 0, $end[0] +1); 
         $result = array_merge($rest,$end_stuff); 
         $this->push_back_stk[] = Null;
         $this->push_back_stk[] = $result;
         $this->current_slice = $result;
            return $result; 
      }

  }

    if (!count($this->top_level_tags)) return Null;


    $start = array_shift($this->top_level_tags);  
    $end = array_shift($this->top_level_tags);


    $this->push_back_stk[] =  $start;
    $this->push_back_stk[] = $end;
    if ($start == Null) {          
        $this->current_slice = $end;
        return $this->current_slice;
  }

    $len = ($end[0]-$start[0]) + 1;
    $this->current_slice = array_slice($this->accumulator, $start[0], $len);

    return $this->current_slice;
}

/**
 *  Clear the escaped token stack
 *  @see XML_PullParser_getEscapedToken
*/
function XML_PullParser_clearEscapedTokens()
{
  $this->_escaped_tags = array();
}



/** 
 *  Returns a single escaped token on each call.
 *  
 *  An escaped token is an element which is declared in both the $tags
 *  array and the $child_tags array. A separate stack is created for these
 *  tokens. Each time XML_PullParser_getEscapedToken returns a token the
 *  token is popped off the stack, until the stack
 *  is exhausted, at which point it returns Null, making this method suitable for use
 *  in a loop.
 *  
 *  
 *  The stack is persistent. If it is not exhausted and if the file being
 *  processed is larger than {@link $read_length}, tokens will be added to
 *  the stack when the next chunk of the file is parsed.
 *  
 *  To clear the stack at any pont call {@link XML_PullParser_clearEscapedTokens}
 *  
 *  An escaped token can be accessed by XML_PullParser_getEscapedToken at any time,
 *  as long as it is still on the stack.  It can also be accessed in normal 
 *  document order by XML_PullParser_getElement. But escaped tokens are not
 *  returned by XML_PullParser_getToken.  However, if an  escaped element is the child of
 *  the current token, then it can  be accessed in the usual ways, e.g.
 *  {@link XML_PullParser_getChild}.
 *  
 *   
 *     
 *  @return array     
 *  @see $_escaped_tags
 *  @see XML_PullParser_clearEscapedTokens
 *
*/
function XML_PullParser_getEscapedToken()
{

    if(!count($this->_escaped_tags))
            return Null;
   
    $start = array_shift($this->_escaped_tags);  
    $end = array_shift($this->_escaped_tags);

    $len = ($end[0]-$start[0]) + 1;
    return $this->_convertToken (array_slice($this->accumulator, $start[0], $len));
}


/**
 *  Pushes the current token back on the stack so that it can be re-read
 *  
 *
 *  XML_PullParser_clearPbackStack() should be called if XML_PullParser_pushbackToken() 
 *  returns false. XML_PullParser_clearPbackStack() will return the pushed back token
 *  and prevent the possibility of an infinite loop.
 *
 *
 *  @see XML_PullParser_clearPbackStack
 *   
 *  @return bool  
*/  
function XML_PullParser_pushbackToken() 
{
    $end = array_pop($this->push_back_stk);
    $start = array_pop($this->push_back_stk);

    array_unshift($this->top_level_tags,  $end);
    array_unshift($this->top_level_tags,  $start);

    if($start == NULL || $end == NULL) {
        return FALSE;
    }
  
    return TRUE;        
}

/**
 *  @see XML_PullParser_pushbackToken
 *
 *  @return array the current token 
*/
function XML_PullParser_clearPbackStack()
{
    return $this->XML_PullParser_getToken();
}

// Used for formatting of current slice and current tag array
// pre-pends "S__" to start tags which don't have them
// only start tags which are included in either the child array or the 
// $top_level_tags array have the "S__" prepended in _processToken() 
// these would be elements which are children of elements in the child tags array
// which have not been identified as children

function _markUnmarkedStartTags($el) 
{
    $end_tags = array();
    foreach ($this->current_tag_array[$el] as $tag) {
        if (!is_array($tag) && preg_match('/^E__(.*)$/', $tag, $matches)) {
            $end_tags[] = $matches[1];         
     }
  }

    foreach ($end_tags as $etag) {  
            for ($i=0; $i < count($this->current_tag_array[$el]); $i++) { 
            if (!is_array($this->current_tag_array[$el][$i]) && $this->current_tag_array[$el][$i] == $etag) {
                $this->current_tag_array[$el][$i] = 'S__' . $etag;             
           }
        }
   }

}


/**
 *  Retrieves a child element and its dependents from its parent
 *  
 *
 *  This method extracts individual child elements from either $el or, if
 *     $el is not specified, from the {@link $current_element} or, failing that, from the 
 *     current token.
 *
 *  $which specifies which instance of the child element to extract; the instances
 *     are treated as a sequence, in the order of appearance in the element's array,
 *     which follows the order of appearance in the XML document
 *  
 *  if $child is the name of $el and $which == 1, then $el will be returned
 *  
 *  @param string $child    name of the element to be extracted
 *  @param integer $which   (optional) element to be extracted, defaults to 1,the first instance of the child               
 *  @param array  $el       (optional) element from which the child is to be extracted     
 *  @return array   Returns child array if found, or Null if child not found in $el or False 
 *                  if $el was not passed in and no $current_element or current token is found.
 *                  
 *
*/
function XML_PullParser_getChild($child, $which = 1, $el = "") 
{   

    if($el) {
       $from_getChildren = true;
        for($i=0; $i<count($el); $i++) {
            if (is_string($el[$i])) {
                $from_getChildren = false;
                break;
            }
        }

       if ($from_getChildren && $which > 0 and $which <=count($el) ) {
           return $el[$which - 1];
        }
        elseif($from_getChildren) {
            return Null;
        }
    }

    $child_array = array();
    $found = false;
    $n = 0;


    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if ($this->XML_PullParser_isCaseEnfolded()) {
        $child = strtoupper($child);
    }

    if (!$el) {
       $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
       return false;
    }

    for ($i=0; $i<count($el); $i++) {
            if (!is_array($el[$i])
                && preg_match ('/^S__' . $child . '$/', $el[$i])) {

                  $n++;
                    if ($n == $which) 
                       $found = true;                  
                }

            if ($found) {
                array_push($child_array, $el[$i]);
           
            if (!is_array($el[$i])
                  &&  preg_match ('/^E__' . $child . '$/', $el[$i])) 
                        break;
         }
   }

    if (!$found) return Null;
    
    return $child_array;
}


/**
 *  Excludes specified child elements from parent array and returns
 *  resulting array
 * 
 *  If no elements are specified for exclusion, it excludes all
 *  child elements and returns the parent alone, from which all child elements
 *  have been removed.
 *
 *  This method assumes that the first element that it encounters
 *  is the parent and all others are its descendent elements. The descendent
 *  elements may themselves have descendent elements.
 *
 *  If there is more than one top-level element of the 
 *  same name as the parent, these are also included in the returned array.
 *  This might be the case, for instance, in a tokenized array returned originally
 *  by {@link XML_PullParser_getElement}. 
 *  
 *  @param array $parent 
 *  @param mixed $args  names of elements to be excluded, can be either array or variable length list of strings
 *  @return array
*/

function XML_PullParser_childXCL($parent, $args="") 
{        
    $xcl = "";
    $arg_list = Null;               // get list of exclusions from either array or variable parameter list
    if ($args && !is_array($args)) {
	$arg_list = func_get_args();
        array_shift($arg_list);
    }
    elseif ($args && is_array($args)) {
    	$arg_list = $args;
    }

    $c=0;
    if (is_array($arg_list) && ($c = count($arg_list))) {
    	for ($i = 0; $i < $c; $i++) {
            $xcl .= $arg_list[$i] . '|';           // create pipe separated list of names
     	}
    }

    $xcl=rtrim($xcl,'|');
    if ($this->XML_PullParser_isCaseEnfolded()) {
       $xcl = strtoupper($xcl);
    }
  
    $name = "";    // get toplevel element
    if (is_string($parent[0]) && preg_match('/S__(.*)/',$parent[0], $matches)) {            
             $name = $matches[1];
     }
     else return Null;

    $count= 0;
    $child_elements = array(); 

    foreach($parent as $element) {
        if (is_string($element) && preg_match('/S__(.*)/',$element, $matches)
                                                 && preg_match("/($xcl)/", $element)) {  
            if (!array_key_exists($matches[1],$child_elements)) {
                   $child_elements[$matches[1]] = array("start"=>array(), "end"=>array());
            }
            $child_elements[$matches[1]]['start'][] = $count;     
      }
        if (is_string($element) && preg_match('/E__(.*)/',$element, $matches) 
                                                    &&  preg_match("/($xcl)/", $element)) {            
            $child_elements[$matches[1]]['end'][] = $count;
      }
        $count++;
    }

    $temp = $parent;
    foreach($child_elements as $el => $positions) {    
        if($el == $name) continue;    // do not exclude topmost element
        $temp = $this->_nullify($temp, $positions);
    }


    $temp_array = array();    // create new arrray which omits excluded array elements
    foreach($temp as $el) {
        if($el) {
            $temp_array[] = $el;
        }
    }

     return $temp_array;
}



/**
 *
 *  Helper function to {@link XML_PullParser_childXCL}
 *
 *
*/
function _nullify($temp, $pos)
{
// sets unwanted elements to empty string

  $start = $pos['start']; // each of these is an a array holding array indexes of 
  $end = $pos['end'];     // elements for exclusion; there's a start and end position for each of the
                          // elements to be excluded
  for($i=0; $i<count($start); $i++) {
      for($j=$start[$i]; $j <= $end[$i];  $j++) {
           $temp[$j] = '';
      }
  }
  return $temp;
}



/**
 * 
 * 
 * This method will extract all the children named by $child from $el.  If
 * $el is not specified, then the {@link $current_element} is used.  If the {@link $current_element}
 * is not set, because XML_PullParser_getElement has not been called, then it
 * searches the current token returned by XML_PullParser_getToken.
 *
 *    @param string $child   name of the child element to be searched for
 *    @param array $el       optional array holding the child elements
 * 
 * @return array  Returns requested array or False if <b>!$el</b>
*/
function XML_PullParser_getChildren($child, $el = "") 
{  
    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;

    if (!$el) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
        return false;
    }

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $child = strtoupper($child);
    }

    $temp_array = array();
    $index = -1;
    $in_child = false;
 
    foreach ($el as $elem) {
        if (!is_array($elem) && preg_match('/^' . $elem . '$/',  'E__' . $child)) {
           $in_child = false;
            array_push($temp_array[$index], $elem);  // get End tag
    }
        if (!is_array($elem) && preg_match('/^' . $elem . '$/',  'S__' . $child)) {
          $in_child = true;
          $index++; 
          $temp_array[$index] = array();	  
    } 

    if ($in_child)
        array_push($temp_array[$index], $elem);
  }

    return $temp_array;
}


/**
 *  Fetches children from parent using a string to specify the parent element.
 *
 *  See {@linkXML_PullParser_getChildren} for the method which fetches children from a parent specified as array
 *
 *  This method uses either $current_element or current token as the array from which to
 *  derive the child elements.  If $current_element is not set then the current token is used.
 *
 *  @param string $name   name of the children sought
 *  @param string $el     name of the parent element where children reside
 *  @return array         the named children of $el and all their dependents
 *      
 *  @see XML_PullParser_getChildren
 *
*/
function XML_PullParser_getChildrenFromName($name, $el)
{

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $name = strtoupper($name);
        $el = strtoupper($el);
    }

      $children = array();

      $n=1;
      
      while ($child = $this->XML_PullParser_getChild($el,$n)) {
          $c =  $this->XML_PullParser_getChildren($name, $child); 
          if($c === false) {
             $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN, "Return failed in XML_PullParser_getChildren");                  
             return false;
          }
          foreach($c as $element) {
              $children[] = $element;
          }

          $n++;
      }

     if($child === false) {
             $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN, "Return failed in XML_PullParser_getChild");     
             return false;
     }

    return $children;
}


/**
 *  Gets an array of strings consisting of the character data specified by the parameter $el,
 *  where $el is either a string naming the element or an array holding the element.
 *
 *  The return value is a numerically indexed array of strings which reflects the structure of the element
 *  referenced.  If the element specified by $el is, for instance, a structure such as
 *   <pre>
 *          &lt;Movies>
 *             &lt;Movie>
 *               &lt;Title>Gone With The wind&lt;/Title>
 *               &lt;date>1939&lt;/date>
 *               &lt;leading_lady>Vivien Leigh&lt;/leading_lady>  
 *               &lt;leading_man>Clark Gable&lt;/leading_man>  
 *               &lt;/Movie>
 *             &lt;Movie>
 *               &lt;Title>How Green Was My Valley&lt;/Title>
 *               &lt;date>1941&lt;/date>
 *               &lt;leading_lady>Maureen O'Hara&lt;/leading_lady>  
 *                          . 
 *             &lt;/Movie>
 *               &lt;Title>Jurassic Park&lt;/Title>
 *                          .
 *                          . 
 *             &lt;/Movie>
 *          &lt;/Movies>     
 *  </pre>
 *
 *  XML_PullParser_getTextArray("Title") will return an  array of titles.  
 *   <pre>
 * 
 *        Array
 *          (
 *              [0] => Gone With The Wind
 *              [1] => How Green Was My Valley
 *              [2] => Jurassic Park
 *          )
 * 
 *  </pre>
 *
 *  But XML_PullParser_getTextArray("Movies") will return an  array consisting of all the character data
 *  between the <Movies> and </Movies>: 
 *   <pre>
 *
 *        Array
 *          (
 *              [0] => Gone With The Wind
 *              [1] => 1939
 *              [2] => Vivien Leigh
 *              [3  => Clark Gable  
 *              [4] => How Green Was My Valley
 *              [5] => 1941 
 *                        .
 *                        .
 *              [8] => Jurassic Park
 *                        .
 *                        .
 *          )
 *
 *  </pre>
 *
 *  Because this method uses XML_PullParser_getTextStripped to retrieve the character data,
 *  all character data is returned, including character data 
 *  from dependent child elements. 
 *  
 *  It is useful to call {@link XML_PullParser_excludeBlanks}, otherwise the array returned
 *  will include empty elements where they appear in the XML.
 *
 *  If $el is a string, it searches the {@link $current_element} for the specified element
 *  and failing that the current token.  If $el is an array, the array should be specific to
 *  the text required, i.e. a container consisting of Start and End tags within which
 *  the text data resides. 
 *
 *  @see XML_PullParser_getTextStripped
 *  @see XML_PullParser_excludeBlanks
 *
 *  @param mixed $el   the name of the element or an array holding the element
 *  @return arrray     list of the texts found in each element, empty array if none are found,
 *                       or FALSE if no containing array is found
*/
function XML_PullParser_getTextArray($el)
{
  $strings = array();
        if(!is_array($el)) {                  
            $tmp_array = $this->XML_PullParser_getChildren($el);        
        }
        else  {
            $old_delimiter =  $this->XML_PullParser_setDelimiter(';;');  
            $result = $this->XML_PullParser_getTextStripped($el);
            $strings = explode(';;',$result);  

            $this->XML_PullParser_setDelimiter($old_delimiter);
            return $strings;
        }

        if(!$tmp_array) {
           if ($tmp_array === False) {
                $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN); 
           }
           return $tmp_array;
        }

        $old_delimiter =  $this->XML_PullParser_setDelimiter(';;');  
        foreach($tmp_array as $child) {      
                  $str = $this->XML_PullParser_getTextStripped($child);
                  if(preg_match('/;;/',$str)) {
                      $elems = explode(';;',$str);              
                      foreach($elems as $elem) {
                          $elem = trim($elem);
                          if($elem) { 
                             array_push($strings, $elem);
                          }
                      }
                  }
                  else {
                     $str = trim($str);
                     if($str) { 
                        array_push($strings, $str);
                     }
                  }
        }
        $this->XML_PullParser_setDelimiter($old_delimiter);

    return $strings;

}

    /**
     *    Get character data from the specified element.       
     *
     *    If no parameters are passed to this method, it is assumed that the subject 
     *    of the search defaults either to {@link $current_element}, or if that has not been set, then
     *    to the current token. All the character data of the of the default array is returned.  This is
     *    in keeping with rule #1 below, where <b>$which</b> = 0 and <b>$el</b> is an array.  (It is also
     *    implied by rule #5.)
     *
     *  
     *  <OL>
     *   <LI> If $el is an array and $which==0, the array is passed to {@link XML_PullParser_getTextStripped}
     *               and all character data enclosed by the parent START and END tags is returned;<br>
     *               [A.] if the element has child elements with text, that text, too, will be returned.              
     *                <br>
     *               [B.] if there is more than one element of the same name bound into the array, 
     *                all character data from all elements of the same  name will be returned.  
     *                <br><br>  
     *   <LI> If $el is an array and $which > 0, the array is passed to {@link XML_PullParser_getTextArray}
     *                 and the requested string is returned, using $which as an index into the array of strings returned 
     *                 by {@link XML_PullParser_getTextArray}.  This array includes the text of parent and all descendents.
     *                 
     *                 To exclude descendents from this array 
     *                 set <b>$xcl</b> to <b>true.</b> Then the <i>$which_th</i> string will be selected only
     *                 from elements named $el and not from any of its descendents. 
     *                  <br>
     *                 It is often advisable to call {@link XML_PullParser_excludeBlanks} in advance.
     *                 <br><br>
     *   <LI> If $el is a string and the name of a child element, then either the {@link $current_element},
     *               or if that's not set, the current token, is searched and the character data returned
     *               depends on the value of $which: <br>
     *                [A.] if $which has a value > 0, then the character data from
     *                    the <i>$which_th</i> instance of $el is returned but not the character
     *                    data of its children;<br>
     *                [B.] if $which retains its optional value of zero, then
     *                     the character data of all elements named $el is returned
     *                     but not the character data of their children<br><br>
     *                     
     *    
     *   <LI>  (Since release 1.2.1) If $el is a string and is the name of the default token, then
     *         the behavior is the same as when $el is an array. <br><br>
     *
     *   <LI> If $el is Null, then the character data of the default token is returned,
     *          including that of child elements.<br><br>      
     *   <LI>  If $el is an array, or the name of the default token (see point 4 above), then the result
     *          can be filtered through {@link XML_PullParser_Childxcl} by setting $xcl to TRUE.
     *         
     *        <br><br>
     *   <LI>  Returns NULL or an Empty String if text is not found in $el        <br><br>
     *   <LI>  Returns FALSE if $el does not resolve to an array and neither $current_element 
     *         nor current token is found
     *  </OL>
     *
     *    All requests to this function are preprocessed here and ultimately passed on to
     *    {@link XML_PullParser_getTextStripped}, which means that its output is subject to the CDATA modifiers:
     *    {@link XML_PullParser_excludeBlanks}, {@link XML_PullParser_trimCdata}, 
     *    and {@link XML_PullParser_excludeBlanksStrict}. 
     *      
     *    @param mixed $el  optional name of element or a tokenized array
     *    @param integer $which position of child element within the parent (default = 0) 
     *    @param boolean $xcl (default=false) when set to true the array $el or the default token is
     *                       filtered through {@link XML_PullParser_Childxcl()} 
     *       
     *    @return mixed
    */
    function XML_PullParser_getText($el="", $which=0, $xcl=false)
    {  

        if ($el && is_array($el)) {

            if($xcl) {
                    $el = $this->XML_PullParser_Childxcl($el);
            }

           if ($which) {
                $strings = $this->XML_PullParser_getTextArray($el);
                if (count($strings)) {
                    if (isset($strings[$which-1])) {
                        return $strings[$which-1];
                    }
                    else {
                        return Null;
                    }
                }
                else return ""; 
            }
            else {  
                return $this->XML_PullParser_getTextStripped($el);     
            }
        }

        // from here on down, $el is a string, the name of an element

        if ($this->XML_PullParser_isCaseFolded()) {
            $el = strtoupper($el);
        }


        $cur_el = $this->current_element;
        if (!$cur_el) $cur_el = $this->converted_token;
        if (!$cur_el) {
            $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);
            return FALSE;
        }

        else {
            $name = $this->XML_PullParser_getElementName($cur_el);
            if($xcl) {
                $cur_el = $this->XML_PullParser_Childxcl($cur_el);
            }
           if ($name == $el && !$which) {
                return $this->XML_PullParser_getTextStripped($cur_el);                         
            }
            elseif ($name == $el && $which) {        
                $strings = $this->XML_PullParser_getTextArray($cur_el);
                if (count($strings)) {
                    if (isset($strings[$which-1])) {
                        return $strings[$which-1];
                    }
                    else {
                        return Null;
                    }
                }
            }
        }
              


        if ($el && $which > 0) {
            if (!$tmp_array = $this->XML_PullParser_getChild($el, $which)) {
                if ($tmp_array === FALSE) {                                // no current token or $current_element 
                    $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN);
                    return FALSE;
                }
                return $tmp_array;                              //  empty array or NULL returned from _getChild
            }    
            $tmp_array=$this->XML_PullParser_childXCL($tmp_array);
            return $this->XML_PullParser_getTextStripped($tmp_array);     
        }


        if ($el) {
            if (!$tmp_array = $this->XML_PullParser_getChildren($el)) {  // get children from $current_element or 
                if ($tmp_array === FALSE) {                                // current token, or False or empty array
                    $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN);
                    return FALSE;
                }
                else { 
                    return Null;                                     
                }
            }
            $merged = array();                                // flatten the array returned from getChildren
            for ($i=0; $i<count($tmp_array); $i++) {
                    $merged= array_merge($merged,$tmp_array[$i]);
            }
            $tmp_array=$this->XML_PullParser_childXCL($merged);    // exclude all elments that are not $el
            return $this->XML_PullParser_getTextStripped($tmp_array); // return stripped text    
        }

        $retval = $this->XML_PullParser_getTextStripped();

        if ($retval === FALSE) {
            $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN);
        }

    
        return $retval;
        //    return $this->XML_PullParser_getTextStripped();     

 
    }




/**
 *  Returns an associative array of attribute-name/attribute-value pairs found in the specified element.   
 *
 *  Example:
 * <pre>
 *  Array
 *  (
 *      [IP2] => 192.168.0.1
 *      [TECH] => tech@example.net
 *      [IP3] => 192.168.0.2
 *      [IP4] => 192.168.0.3
 *  )
 * </pre>
 *   
 *  When namespace support is not in effect, it is possible to extract individual 
 *  names/values from members of this array, using the standard PHP 
 *  constructs, <b>each</b> and </b>foreach</b>;  or, where $name is known,
 *  it is possible to get the value:  $value = $array[$name].
 *  It is safest to use {@link XML_PullParser_getAttrVal}, which 
 *  guarantees the current case folding setting. 
 * 
 *  When namespace support is invoked, {@link XML_PullParser_getAttrVal} should always be used, 
 *  because attribute names are re-formatted into keys with the namespace URI's.
 *
 *   @return array     Returns an associative array of name/value pairs where the
 *                     keys are the attribute names. If $which is an integer,
 *                     the result is from the <i>$which_th</i> instance of the element $el.
 *                     If $which is set to false then it returns all the attributes 
 *                     found in $el and its child elements. In the latter case, if more
 *                     than one attribute has the same name, there will be duplicate keys
 *                     and only the last of the duplicates is returned because of the nature of associative arrays.
 *                     Returns Null if no attributes are found. See {@link XML_PullParser_getAttrValues}
 *                     for a function which guarantees duplicate names. 
 *  
 *  
 *
 *   @param mixed $name    name of the element to be searched, can be the name of $el itself.    $name
 *   can also be an array returned from one of the tokenizing functions. e.g. XML_PullParser_getElement,
 *   XML_PullParser_getChild, XML_PullParser_getToken. In this case, the tokenized array will be used for $el 
 *   and the name of its topmost element will be used for $name.  This feature has particular functionality
 *   when used in a loop with {@link  XML_PullParser_nextElement}, enabling the return value from
 *   XML_PullParser_nextElement to be passed into XML_PullParser_getAttributes as $name. 
 *
 *   @param mixed $which   (optional) integer or boolean.  If $which is an integer, the function looks
 *   for the $which_th element of $name.              
 *   If set to false, $name is ignored, and all attributes in $el are returned. 
 *   But $name must still passed in as a Null parameter. 
 *  <br> NOTE: The false option is not suitable for cases where there is more than one attribute of the same name.
 *
 *   @param array $el  (optional) the element where $name is found 
 *   If $el is Null, the function first tries {@link $current_element}, then the current token.  $el is
 *   ignored if $name is an array.
 *   
 *
 *
*/
function XML_PullParser_getAttributes($name, $which = 1, $el="") 
{
    if(is_array($name)) {
       $el = $name;
       $name = $this->XML_PullParser_getElementName($el);    
    }
    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if (!$el) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);  
        return false;
    }

    if(!$this->XML_PullParser_getElementName($el)) {
        if(is_string($el)) {
           $msg = "Array parameter \"$el\" is not a token";
        }
        else {
           $msg = "Array parameter is not a token";
        }
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_BAD_PARAM, $msg); 
        return false;
    }

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $name = strtoupper($name);
    }

    $attr_array = array();
   
    $n = 0;
    $all = false;
    $found = false;

    if (is_bool($which) && ! $which) {  $all = true; $found = true; }


    for ($i=0; $i<count($el); $i++) {

            if (!is_array($el[$i]) && !$all
                && preg_match ('/^S__' . $name . '$/', $el[$i])) {
                  $n++;
                    if ($n == $which) 
                       $found = true;                  
                }
            if ($found) {
                if (is_array($el[$i]) && !array_key_exists('cdata', $el[$i])) {
                    foreach ($el[$i] as $key => $at) {
                       $attr_array[$key] =$at;                    
                   }

                if (! $all) {
                    return $attr_array;
                }
            }
            if ($which && !is_array($el[$i]) &&  preg_match ('/^E__' . $name . '$/', $el[$i])) {
                        break;
                  }
         }
   }

    if (!$found) return Null;

    return $attr_array;
  

}



/**
 *  Designed to work in loops using the internal array created by {@link XML_PullParser_getElement}.
 *  
 *  This function removes each next element from the next element stack, 
 *  and returns it until the stack is exhausted.  The stack
 *  is a copy of {@link $current_element}.
 *  
 *  This is useful only where there is more than one instance of an element:
 *  <pre>
 *	<DNS>
 *	<ENTRY> 
 *	<ipaddress>172.20.19.6</ipaddress> 
 *	<domain>example.com</domain> 
 *	<server ip="192.168.10.1">example_1.com</server> 
 *	<server ip="192.168.10.2">example_2.com</server> 
 *	<server ip="192.168.10.3">example_3.com</server> 
 *	<alias>www.example.com</alias> 
 *	</ENTRY> 
 *	</DNS>
 *  </pre>
 *  
 *  Used in this situation, XML_PullParser_nextElement() will return each next server element in document order,
 *  making it possible to get at the text and the attributes.
 *  Note: This method is not used for accessing child elements of the elements saved by XML_PullParser_getElement.
 *  For that we have to use XML_PullParser_getSequence() or XML_PullParser_getChild(),
 *  or else  include the child elements in the child tags array
 *  
 * The array it returns is a tokenized array that can be passed to the class methods
 * which accept them.  By default, this array is filtered through
 * {@link XML_PullParser_childXCL}, which means that all children of the parent element 
 * are removed. This guarantees that the result returned when requesting text and
 * attributes is for the element named in the parameter to XML_PullParser_getElement.
 * But this also means that it is not suitable for use in applications which need to
 * slurp together text from parent and all its children, as in a marked-up paragraph,
 * since all the mark-up would be deleted in favor of the parent element.
 * 
 * The default behavior can be turned off by passing in a False value as a parameter,
 * in which case the results are not filtered through XML_PullParser_childXCL
 * 
 * The idiom for  its use is:
 *
 *  <pre>
 *   $parser->XML_PullParser_getElement('element_name');
 *    while($next = $parser->XML_PullParser_nextElement()) {
 *         $data = $parser->XML_PullParser_getText($next);           
 *    }
 *  </pre>
 *  
 *  @see $_next_element_array
 *  
 *  @param boolean $xcl defaults to True;  False turns off filtering through XML_PullParser_childXCL
 *  @return array
*/
function XML_PullParser_nextElement($xcl=true)
{

   if(!$this->current_element) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN,
                "\$current_element not found.\nXML_PullParser_getElement was not called or failed to create \$current_element."); 
        return False;
   }

   $temp = array();

   // can't use this because it returns null on empty array value --cdata or attribute-- and exits prematurely:
  //      while($el = array_shift($this->_next_element_array) ) 

  foreach($this->_next_element_array as $next_el) {        // foreach makes copy of the array, so we can 
           $el = array_shift($this->_next_element_array); //  safely shift out values without affecting loop        
           $temp[] = $el;        
           if(count($temp) == 1 && is_string($el)) {
             $el_name = $this->XML_PullParser_getElementName($el);

           }
           
        

        if (is_string($el)) {      
           $name = $this->XML_PullParser_getElementName($el);
           if (!$name) return Null; 
            if (preg_match('/^E__' . $el_name . '/',$el)) {
                if(!$xcl) {
                    return $temp;
                }

                return $this->XML_PullParser_childXCL($temp);

            }
        }
   }

   return Null;
}

/**
 *  Get the next attribute from attribute loop
 *  
 *  
 *  The array returned by this method has this structure: <br>
 *   [0]  element name<br>
 *   [1]  associative array of all the attributes in this element;
 *        the names of the attributes are the keys<br>
 *   [2]  the character data enclosed by the element, if the array
 *    is created by XML_PullParser_setAttrLoop_elcd; Or,
 *     the empty string if created by XML_PullParser_setAttrLoop<br>
 *   
 *  Example:<br>
 *  <server ip="192.168.0.1" tech="tech@footloose.org">
 *              ns1.example.net</server> 
 *
 *  The above yields this array: 
 * <pre>
 *  Array
 *  (
 *   [0] => SERVER
 *   [1] => Array
 *       (
 *           [IP] => 192.168.0.1
 *           [TECH] => tech@example.net
 *       )
 *   [2] => ns1.example.net  <b>OR</b>  ""
 *  )
 * </pre>
 * 
 * Code to use this function:    
 * <pre>
 *    $servers=$parser->XML_PullParser_getElement('server'); 
 *    $attrs = $parser->XML_PullParser_setAttrLoop();
 * 
 *   $n =1;
 *    while($at = $parser->XML_PullParser_nextAttr()) { 
 *       $server_name = $parser->XML_PullParser_getText($servers,$n);
 *       $n++;
 *       echo "$at[0]: $server_name\n";
 *       foreach($at[1] as $attr_name => $attr_value) { 
 *          echo "$attr_name => $attr_value\n";
 *      }      
 *     
 *     }
 * </pre>
 *
 *  @see XML_PullParser_setAttrLoop
 *  @see XML_PullParser_setAttrLoop_elcd
 *  @see XML_PullParser_resetAttrLoopPtr
 *  @see XML_PullParser::$_attr_loop_array
 *  @see XML_PullParser::$_attr_loop_pos
 *  @return array
*/
function XML_PullParser_nextAttr()
{

    if ($this->_attr_loop_pos >= count($this->_attr_loop_array)) {
            return false;
    }

    $attr = $this->_attr_loop_array[$this->_attr_loop_pos];
    $this->_attr_loop_pos++;
    return $attr;    

}


/**
 *
 *   This method does the same thing as XML_PullParser_setAttrLoop_elcd
 *
 *   The one difference is that this method requires that $el be declared in the $child tags array.
 *   XML_PullParser_setAttrLoop_elcd is an improvement on the code in this method and should be
 *   used unless imcompatibilties between the current and the previous version of
 *   XML_PullParser_setAttrLoop_elcd arise.  
 *
 *   @see XML_PullParser_setAttrLoop_elcd
 *   
 *   @return array   
*/

function XML_PullParser_setAttrLoop_cdata($el = "")
{

    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if (!$el) {
       $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
        return false;
    }

 if($el != $this->current_element) 
 {
      $current_element = $this->current_element;
      $tmp_array = $this->XML_PullParser_setAttrLoop($el);

      $elements = array();
      for($i=0; $i<count($tmp_array); $i++) {
           $elements[$tmp_array[$i][0]] = true;
      }

      $new_array = array();
      foreach($elements as $key =>  $val) {
          $this->XML_PullParser_getElement($key);
          if ($tmp = $this->XML_PullParser_setAttrLoop($this->current_element, true)) {
            $new_array = array_merge($new_array,$tmp);         
          }
      }
     $this->XML_PullParser_resetCurrentElement($current_element);   
     $this->_attr_loop_array = $new_array;
     return $this->_attr_loop_array;
 }

  return $this->XML_PullParser_setAttrLoop($this->current_element, true);

}



/**
 *  
 *  XML_PullParser_setAttrLoop_elcd is a wrapper for
 *  XML_PullParser_setAttrLoop. 
 *
 *  This method differs from {@link XML_PullParser_setAttrLoop} in that
 *  it captures the text associated with each element, in addition to
 *  the attribute and element name.  For an illustration of the 
 *  array structure that it creates see {@link XML_PullParser_nextAttr}.
 *  
 * 
 *  Like XML_PullParser_setAttrLoop, this method  uses {@link XML_PullParser_nextAttr}
 *  to loop through the attribute array.  The difference is that in the array
 *  returned by XML_PullParser_nextAttr, the second array element holds
 *  the element's character data instead of the empty string.  For details
 *  see {@link XML_PullParser_nextAttr}.
 *
 *  This is an improvement on the old code for this method, which is still available 
 *  as {@link XML_PullParser_setAttrLoop_cdata}.  
 *  XML_PullParser_setAttrLoop_cdata should be used only if
 *  incompatibilties between the current and the previous version of
 *  XML_PullParser_setAttrLoop_elcd arise.  In that event, please notify
 *  the developer at Myron_Turner_(at)_Shaw_(dot)_ca.
 *
 *  @param array $el  optional tokenized array
 *  @return array
 *
 *  @see $current_element
 *  @see XML_PullParser_nextAttr
 *  @see XML_PullParser_setAttrLoop
 *  @see XML_PullParser_resetAttrLoopPtr
 *
 *
*/
function XML_PullParser_setAttrLoop_elcd($el = "")
{

    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if (!$el) {
       $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
        return false;
    }

 if($el != $this->current_element) 
 {
      $current_element = $this->current_element;
      $tmp_array = $this->XML_PullParser_setAttrLoop($el);

      $elements = array();
      for($i=0; $i<count($tmp_array); $i++) {
           $elements[$tmp_array[$i][0]] = true;
      }

      $new_array = array();
      foreach($elements as $key =>  $val) {         
           $ar = $this->XML_PullParser_tokenFromChildren($key, $el);
           if ($tmp = $this->XML_PullParser_setAttrLoop($ar, true)) {
            $new_array = array_merge($new_array,$tmp);         
          }
      }
     $this->XML_PullParser_resetCurrentElement($current_element);   
     $this->_attr_loop_array = $new_array;
     return $this->_attr_loop_array;
 }

  return $this->XML_PullParser_setAttrLoop($this->current_element, true);

}



/**
 *
 *    Creates an array of all attributes located in $el and its children.
 *    Use XML_PullParser::XML_PullParser_nextAttr() to get the attributes.
 *
 *
 *
 *   
 *    For fuller description and example of use see {@link XML_PullParser_nextAttr}  
 *
 *    @param array $el (optional)    
 *          if $el is not set then it is assumed that subject of the request
 *          is the {@link $current_element} or, lacking that, the current token
 *
 *    @param boolean $assignText (internal)
 *
 *    @return array  accessed through XML_PullParser_nextAttr
 *
 *  
 *    @see XML_PullParser_resetAttrLoopPtr
 *    @see XML_PullParser::$_attr_loop_pos
 *    @see XML_PullParser::$_attr_loop_array
 *    @see XML_PullParser_setAttrLoop_elcd
*/
function XML_PullParser_setAttrLoop($el="", $assignText = false)
{

    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if (!$el) {
       $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
        return false;
    }
   
    $this->_attr_loop_pos = 0;
    $this->_attr_loop_array = array();

    $text_count = 1;  
    $_text = "";

    for ($i=0; $i<count($el); $i++) {
        $attr_array = array();
        if (is_array($el[$i]) && !array_key_exists('cdata', $el[$i])) {
          foreach ($el[$i] as $key => $at) {
          $attr_array[$key] =$at;                    
         }
        }
       if (count($attr_array)) {

             $this->_attr_loop_array[] = array($prev,$attr_array,$_text);
       }
                        // previous array element is always the element which holds attributes
          
        if (is_string($el[$i]) && ($prev = preg_match('/^S__(.*)/',$el[$i], $matches))) {        
               $prev = $matches[1]; 
                   if($assignText) { 
                       $el = $this->XML_PullParser_childXCL($el);
                       $text = $this->XML_PullParser_getText($el, $text_count);                        
                       if($text) {                                                      
                           $_text = $text;                            
                      }
                      else {
                        $_text = "";
                      }
                      $text_count++;
                  }       // if $assignText

        }               // if is_string
        else {          
           $prev = "";
           $_text = ""; 
        }

    }      // for


  if (count($this->_attr_loop_array)) {
        return $this->_attr_loop_array;
  }

  return NULL;
}


/**
 *  Resets attribute loop pointer back to zero, so that the attributes loop
 *  can be re-read, starting at the top
 *
 *   @see XML_PullParser_setAttrLoop 
 *   @see XML_PullParser_nextAttr
 *   @see XML_PullParser::$_attr_loop_pos
 */
function XML_PullParser_resetAttrLoopPtr()
{
      $this->_attr_loop_pos = 0;
}

/**
 * Unsets current element and returns its value;
 *   
 * @return array {@link $current_element }
*/
function XML_PullParser_unsetCurrentElement()
{
  $temp = $this->current_element;
  $this->current_element = array();
  return $temp;
}

/**
 * Sets current element to a new value.
 *   
 * @param array $cur_el
 * @return array  copy of previous current element
 * @see $current_element
*/
function XML_PullParser_resetCurrentElement($cur_el)
{
 $temp = $this->current_element;
 $this->current_element = $cur_el;
 $this->_next_element_array = $cur_el;
 return $temp;
}

/**
 * @return array {@link $current_element}
 *  
 */
function XML_PullParser_getCurrentElement()
{
 return $this->current_element;
}


/**
 *  Get the value of an attribute. 
 *    
 *  This method guarantees conformance with the current case-folding setting.
 *  
 *  <b>It should always be used to access attribute values when namespace support is in effect.</b>
 *
 *  @param string $name  name of the attribute 
 *  @param array $attr_array an assocative array of name/value pairs returned by {@link XML_PullParser_getAttributes}
 *  or derived from {@link XML_PullParser_nextAttr} or {@link XML_PullParser_getAttrValues}
 *
 *  @return string  the requested value for $name or Null if  not found.
 *
*/
function XML_PullParser_getAttrVal($name, $attr_array) {

    if(is_string($name) && $this->XML_PullParser_isCaseEnfolded()) {
        $name = strtoupper($name);
    }

    foreach($attr_array as $key=>$val) {
        if($result = $this->XML_PullParser_getAttr_NS($name, array($key=>$val))) {
            return $result;
        }
    }


    if($this->XML_PullParser_currentNS) {  // looking for a namespace but not found
        return Null;
    }


	   // not looking for a namespace for this attribute
    if (is_array($attr_array) && array_key_exists ($name, $attr_array)) {
        return $attr_array[$name];
    }

    return Null;
}


/**
 *  This method excavates all the attributes of a specified element. 
 *
 *
 * It is guaranteed to return all attribute
 *  names and values even in cases where a parent has more than one child of the same name with same-named
 *  attributes.  For instance:
 *  <pre>
 *       &lt;directory>
 *           &lt;file name = "tutorial.doc" role = "doc" />
 *           &lt;file name = "classes.php" role = "php" />
 *           &lt;file name = "constants.inc" role = "php" />
 *      &lt;/directory>
 *    </pre>   
 *
 *  The above would return the following array:
 * <pre>  
 *	Array
 *	(
 *	    [0] => Array
 *	        (
 *	            [NAME] => tutorial.doc
 *	            [ROLE] => doc
 *	        )
 *
 *	    [1] => Array
 *	        (
 *	            [NAME] => classes.php
 *	            [ROLE] => php
 *	        )
 *
 *	    [2] => Array
 *	        (
 *	            [NAME] => constants.inc
 *	            [ROLE] => php
 *	        )
 *
 *	)
 * </pre>
 *  
 * 
 *  @param array $ar  an associative array holding a single array element in which the key is the name of an
 *          xml child element enclosing any number of attributes and the value is 
 *           either the name of the parent element (string) or a tokenized array which is the parent to $name.
 *           e.g.<pre> array($child=>$parent).</pre> $child and $parent can be the same.   
 *  @return array numerically indexed array of attribute-name/attribute-value pairs.
 *  @see XML_PullParser_setAttrLoop 
*/

function XML_PullParser_getAttrValues($ar)
{


   $errMsg = Null;
    if(!$ar) {
        $errMsg = "(Name=>value) array not passed into XML_PullParser_getAttrValues.";
        if(is_array($ar)) {
           $errMsg .= "\nParameter is empty array.";
        }
       // return False;
    }
    if(!is_array($ar)) {
        $errMsg = "(Name=>value) array required for XML_PullParser_getAttrValues";
    }

   if($errMsg) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_BAD_PARAM, $errMsg); 
        return FALSE;
   }

    list($name, $el) = each($ar);
    if ($this->XML_PullParser_isCaseEnfolded()) {
        $name = strtoupper($name);
        if(is_string($el)) {
            $el = strtoupper($el);
        }
    }


    if(is_array($el)) {
        $children =  $this->XML_PullParser_getChildren($name,$el);  
    }
    else {       
        $children =$this->XML_PullParser_getChildrenFromName($name, $el);
            if (!$children) {
                $tmp =  $this->XML_PullParser_unsetCurrentElement();
                $children =$this->XML_PullParser_getChildrenFromName($name, $el);
                $this->XML_PullParser_resetCurrentElement($tmp);
            if (!$children) {
                $children =  $this->XML_PullParser_getChildren($name); 
                }
            }
    }

    $result = array();

    foreach ($children as $child) { 
        $attrs = $this->XML_PullParser_getAttributes($name,1, $child);
        $result[] = $attrs;        
    }

    return $result;
}

/**
 *  Adds attributes to spans and classes to spans and is used
 *  with {@link XML_PullParser_getTextMarkedUp}
 *
 * This method takes two parameters, $attributes and $markup.
 *  Both are associative arrays. The $markup array is the same
 *  as the {@link XML_PullParser_getCSSSpans} array:
 *  <pre>
 *    array(xml_element =>css_class_name, xml_element =>css_class_name, . . )
 *  </pre>
 *  The $attributes array is an associative array of this format:
 *
 *  <pre>
 *    array(html_attribute =>attribute_value, . . )
 *  </pre>
 *
 *  If the first element in the $markup array has the following form:
 *
 *  <pre>
 *  ("emphasis"=>"bold_text")
 *  </pre>
 *
 *  and if its counterpart in the $attributes array has this format:
 *
 *   <pre>
 *  ("style"=>"font-size: 10pt")
 *  </pre>
 *
 *
 *  The tag would become:   
 *  
 *  <b>
 *      <span class="bold_text" style="font-size: 10pt"> 
 *  </b> 
 *  
 *
 *  The $markup array always defaults to 
 *  <pre>       class="markup"</pre>
 *
 *  The attribute/value pair of the $attributes array can be any valid markup.
 *
 *  The two arrays must be sequentially parallel, so that $markup-1 is
 *  modified by $attributes-1, etc.  There cannot be duplicate keys, since the last
 *  duplicate overwrites the previous.
 *
 *  It returns an array dedicated for use in {@link XML_PullParser_getTextMarkedUp};
 *  it can be combined with arrays returned by  {@link XML_PullParser_getStyledTags}, 
 *  {@link XML_PullParser_getCSSSpans}, and  {@link XML_PullParser_getHTMLTags}.
 *  
 *  
 *  @param array $markup 
 *  @param array $attributes 
 *  @return array  
 * 
 * 
*/

function XML_PullParser_getStyledSpans($markup, $attributes)
{
   $class_array = $this->XML_PullParser_getCSSSpans($markup);

    $attrs = array_keys($attributes);
    $classes = array_keys($class_array);

    for($i=0; $i<count($classes); $i++) {    
        $replacement = '  ' . $attrs[$i] . '="' .  $attributes[$attrs[$i]] . '">';         
        $class_array[$classes[$i]][0] = preg_replace('/>/', $replacement, $class_array[$classes[$i]][0]);          
    }  
 return $class_array; 
}

 
/**
 *  This function is almost identical to {@link XML_PullParser_getStyledSpans},
 *  except that it modifies standard HTML tags, so that one could
 *  convert <pre>&lt;b> <b>to</b> &lt;b class="title"></pre>
 *
 * @return array an array suitable for use with {@link XML_PullParser_getTextMarkedUp} 
 * 
 * @param array $markup associative array 
 * @param array $attributes associative array 
 * @see XML_PullParser_getStyledSpans
 *
 *
*/
function XML_PullParser_getStyledTags($markup, $attributes)
{
   $class_array = $this->XML_PullParser_getHTMLTags($markup);

    $attrs = array_keys($attributes);
    $classes = array_keys($class_array);

    for($i=0; $i<count($classes); $i++) {    
        $replacement = '  ' . $attrs[$i] . '="' .  $attributes[$attrs[$i]] . '">';         
        $class_array[$classes[$i]][0] = preg_replace('/>/', $replacement, $class_array[$classes[$i]][0]);          
    }  

    return $class_array; 
}


/**
 * This takes an associative array of XML element tags and CSS class names
 * and converts it to an array structure suitable for use in
 * {@link XML_PullParser_getTextMarkedUp}:
 *  
 *  <pre>
 *    array(xml_element =>css_class_name, xml_element =>css_class_name, . . )
 *  </pre>
 *
 *  For instance:
 *  <pre>
 *     array("code"=>"code", "emphasis"=>"boldface_italic");
 *   </pre>
 *
 * @param array $markup
 * @return array 
 * @see XML_PullParser_getHTMLTags
 * @see XML_PullParser_getStyledSpans
 *
*/ 
function XML_PullParser_getCSSSpans($markup)
{
     $tmp_array = array();
     foreach($markup as $el => $class) {
        $tmp_array[$el] = array("<span class='$class'>", "</span>");
     }
     return $tmp_array;
}


/**
 * This takes an associative array of XML element tags and HTML tags
 * and converts it to an array structure suitable for use in
 * {@link XML_PullParser_getTextMarkedUp}:
 *
 *  <pre>
 *    array(xml_element =>html_tag_name, xml_element =>html_tag_name, . . )
 *  </pre>
 *
 *  For instance:
 *  <pre>
 *     array("code"=>"code", "emphasis"=>"b", "classname"=>"i");
 *   </pre>
 *
 * @param array $markup
 * @return array 
 * @see XML_PullParser_getCSSSpans
 * @see XML_PullParser_getStyledSpans
*/ 

function XML_PullParser_getHTMLTags($markup)
{
     $tmp_array = array();
     foreach($markup as $el => $tag) {
        $tmp_array[$el] = array("<$tag>","</$tag>");
     }
     return $tmp_array;
}


/**
 *  This method will mark up text, essentially for
 *  redisplay as in HTML, using the $mark_up array
 *  for determining which XML elements are to be 
 *  marked up and how they are to be marked up.
 *  
 *  The $mark_up array should be created using the
 *  functions provided: {@link XML_PullParser_getHTMLTags},
 *  {@link XML_PullParser_getCSSSpans}, {@link XML_PullParser_getStyledSpans},
 *  and {@link XML_PullParser_getStyledTags}.
 *
 * <pre>
 * $mark_up = $parser->XML_PullParser_getCSSSpans(
 * 		array("code"=>"code", "emphasis"=>"emphasis")
 * 	);
 * $mark_up += $parser->XML_PullParser_getHTMLTags(array("classname"=>"b"));
 *             
 * $text = $parser->XML_PullParser_getTextMarkedUp($mark_up);
 * </pre>
 *
 *  NOTE: The tags marked up by this function cannot be empty, i.e. they
 *  must have both an open tag and a closing tag.  
 *
 *  In other respects, this function works essentially the same as
 *  {@link XML_PullParser_getTextStripped} with one difference:
 *  it is not subject to the CDATA modifiers {@link $XML_PullParser_XCLUDE_BLANKS},
 *  {@link $XML_PullParser_XCLUDE_BLANKS_STRICT}, {@link $XML_PullParser_TRIM_CDATA}
 *
 * @return string
 * @param array $mark_up
 * @param mixed $el see {@link XML_PullParser_getTextStripped}
 *
*/ 


function XML_PullParser_getTextMarkedUp($mark_up,$el="") 
{
    if(!$mark_up) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_BAD_PARAM, "Missing markup array"); 
        return FALSE;
    }

    if(is_string($el)) {
        $new_array = array();
        $children = $this->XML_PullParser_getChildren($el);           

        while($children) {
           $tmp = array_shift($children);
           $new_array = array_merge($new_array, $tmp);
        }
        $el = $new_array;
    }


    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if(!$el) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);  
        return False;
    }

    $ulink = "ulink";
    if ($this->XML_PullParser_isCaseEnfolded()) {
        $mark_up = array_change_key_case($mark_up, CASE_UPPER);
         $ulink = "ULINK";
    }

    $pattern = "";
    foreach($mark_up as $tagname => $markups) {
      $pattern .= $tagname . '|';
    }
    
    $pattern .= $ulink;

    $pattern = rtrim($pattern,'|');
    $pattern = '/(' . $pattern . ')/';

    $text = "";
    $prev = Null;
    $link_num = 1;
    foreach ($el as $tag) {
        $otag = "";
        $ctag = "";
        if (is_array($tag) && isset($tag['cdata'])) {    
           if($prev) {           
                if($prev == $ulink) {
                   $temp = $this->XML_PullParser_getChild('ulink', $link_num, $el);
                   $link_array = $this->XML_PullParser_getAttributes($temp,1);
                   $link = $this->XML_PullParser_getAttrVal('url', $link_array); 
                   $otag = '<A href="' . $link . '">';
                   $ctag = '</A>';   
                   $link_num++;
                   $text .= $otag . $tag['cdata'] . $ctag .  $this->_strippedCdataDelimiter;
                }
                else { 
                    $otag = $mark_up[$prev][0];
                    $ctag = $mark_up[$prev][1];
                    $text .= $otag . $tag['cdata'] . $ctag .  $this->_strippedCdataDelimiter;
                }
            }
            else { 
                $text .= $tag['cdata'] . $this->_strippedCdataDelimiter;
            }
        }

    	elseif (is_string($tag) &&  preg_match('/^S__(.*)/',$tag, $matches)) { 
            if(preg_match($pattern, $matches[1])) {
    	        $prev = $matches[1];  
           	}
    	    else {  
    	      $prev = Null;
    	    }    
    	} 
        elseif (is_string($tag) &&  preg_match('/^E__/',$tag)) { 
    	      $prev = Null;
        }


    }

    if ($text) {
        return rtrim($text, $this->_strippedCdataDelimiter);
    }

    return Null;

}
/**
 * 
 * This method is designed to return all the character data contained within
 * the START and END tags of an element, regardless of whether or not the texts
 * are enclosed by child elements.
 *          Example:
 *   <pre>
 *      &lt;News_item>There was a &lt;b>big&lt;/b> rainstorm last night&lt;/News_item>
 *   </pre>
 *          This would resolve to:
 *                   There was a big rainstorm last night
 *
 * The default delimiter which separates the text from contiguous elements is a
 * single space. This can be reset in {@link XML_PullParser_setDelimiter}, making it possible
 * to gobble up the text from a known sequence of elements and split out the results.
 *   <pre>
 *                &lt;maintainer>
 *                   	&lt;user>foo_33&lt;/user>
 *                   	&lt;name>Joe Foo&lt;/name>
 *                   	&lt;email>Joe Foo@shaw.ca&lt;/email>
 *                   	&lt;role>lead&lt;/role>
 *                &lt;/maintainer>
 *  </pre>
 *  <br>
 *  <CODE>$parser->XML_PullParser_setDelimiter(';');
 *  $result = $parser->XML_PullParser_getTextStripped($maintainer);
 *  list($user, $name,$email, $role) = explode(';', $result);
 *  </CODE>
 *  <br>
 *  <b>$result</b> would be:  <pre><b>              foo_33;Joe Foo;Joe;Foo@shaw.ca;lead</b></pre>
 *         
 *
 *    
 *   The text returned from this function is also subject to the CDATA modifiers:
 *   <OL>
 *   <LI> If the package level function {@link XML_PullParser_excludeBlanks} is called with  
 *   a true value, XML_PullParser_getTextStripped will skip over 
 *   instances of character data  which contain only new lines, spaces, tabs, and carriage returns. 
 *   This is aimed at  XML_PullParser_getText and XML_PullParser_getTextArray, where nesting of 
 *   elements and  text can cause instance numbering and array counts to be misaligned.               
 *
 *   <LI> If {@link XML_PullParser_excludeBlanksStrict} is called with a true value, XML_PullParser_getTextStripped
 *   will reject any CDATA packet which does not contain at least one member of the regular
 *   expression character class "\w", which includes [A-Za-z0-9_-]
 *   
 *   <LI>If {@link XML_PullParser_trimCdata} is called with a true value, all CDATA packets will be trimmed
 *   using the PHP trim() function.  
 *  </OL>
 *  
 *
 * @param mixed $el (optional) array or string specifying element to be parsed;  
 *          if $el is not set then it is assumed that subject of the text request
 *          is the {@link $current_element} or, lacking that, the current token
 *
 * @return string
 *         Returns a string concatenated from all the character data enclosed within the
 *         subject element, including the character data enclosed within
 *         its child elements.  Returns Null if not found. White space counts a CDATA,
 *         and will not yield Null, unless XML_PullParser_trimCdata is called.  
 *           
 *       
 * 
*/
function XML_PullParser_getTextStripped($el="") 
{
  global $XML_PullParser_XCLUDE_BLANKS, $XML_PullParser_XCLUDE_BLANKS_STRICT, $XML_PullParser_TRIM_CDATA;

  if(is_string($el)) {
        $new_array = array();
        $children = $this->XML_PullParser_getChildren($el);           
        while($children) {
            $tmp = array_shift($children);
            array_merge($new_array, $tmp);
        }
        $el = $new_array;
  }


    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if(!$el) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
        return false;
    }

    $text = "";
    if($this->XML_PullParser_currentNS) {
        $is_NS = false; 
    }
    else {
       $is_NS = true; 
    }

    foreach ($el as $tag) {
            if (is_string($tag) && preg_match('/S__/',$tag))  {  // identify elements without cdata arrays
                $found = false;                                  // this matters to getTextArray and 
            }                                                    // its dependents


					 // only get text if in Namespace
					 // $is_NS == TRUE if no namespaces are declared
            if (is_array($tag) && isset($tag['cdata']) && $is_NS) { 
                if($XML_PullParser_XCLUDE_BLANKS && preg_match('/^[ \n\t\r\f\e]+$/', $tag['cdata'])) {
                    continue;
                }
                if($XML_PullParser_XCLUDE_BLANKS_STRICT && !preg_match('/[\w]/', $tag['cdata']) ) {
                    continue;
                }

                if($XML_PullParser_TRIM_CDATA) {
                    $tag['cdata'] = trim($tag['cdata']);
                }
                $text .= $tag['cdata'] . $this->_strippedCdataDelimiter;
                $found = true;
               
            }
            elseif(is_array($tag) && isset($tag['_ns_'])) {                    
                     $is_NS = $this->_is_current_NS($tag['_ns_']);
                  
            }
            if (is_string($tag) && preg_match('/E__/',$tag) && !$found) {              
                        $text .= $this->_strippedCdataDelimiter;  // no cdata identified by delimiter
            }
            
  }

    $text = rtrim($text, $this->_strippedCdataDelimiter);    
    if ($text){
        return $text;
    }

    return Null;

}

/**
 *  Sets the delimiter for XML_PullParser_getTextStripped and returns the old delimiter
 *  @see XML_PullParser_getTextStripped 
 *
 *   @param string $delimiter
 *   @return string old $delimiter
 *      
 *
*/
function XML_PullParser_setDelimiter($delimiter)
{
   $old_del = $this->_strippedCdataDelimiter;
   $this->_strippedCdataDelimiter = $delimiter;
   return  $old_del;
}


/**
 * Gets tokenized arrays of elements specified in the $child_tags array.
 *
 * This method is second only to {@link XML_PullParser_getToken} in importance.
 * XML_PullParser_getToken returns elements specified in the tags array: {@link XML_PullParser::$tags},
 * whereas this method returns elements specified in the child tags array: {@link XML_PullParser::$child_tags}.
 * The array it returns consists of all the elements name $el found in 
 * {@link $converted_token}  and all of their dependents.     
 *
 * @param string $el
 * @return array  Returns requested array or False if <b>!$el</b>
 *
 *
*/
function XML_PullParser_getElement($el) 
{
    if(!$this->current_tag_array) {
      $err = new XML_PullParser_Errors(XML_PullParser_ERROR_BAD_INTERNAL_ARRAY,
            "\n\tXML_PullParser_getToken must be called before XML_PullParser_getElement");  
       return False;    
    }

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $el = strtoupper($el); 
    }

            // return False if the array is empty, i.e. the element does not exist 
    if (!isset($this->current_tag_array[$el][0])) 
        return Null;


    $this->_markUnmarkedStartTags($el);

    $this->current_element = $this->current_tag_array[$el];
    $this->_next_element_array = $this->current_element;
    return $this->current_tag_array[$el];
}



/**
 *  An internal function used to initialize a number of internal structures. 
 *  It is called by default by XML_PullParser_getToken.
 *
 *  @return void
 *
*/
 function _processToken() 
{
   $this->current_element = array();
   $this->current_tag_array = array();

    foreach ($this->child_tags as $c_tag => $val) {  
      $this->current_tag_array[$c_tag] =  array();
    }
 
// save the start and end positions of each child tag

    $E_positions = array();
    $S_positions = array();
    $count = 0;
    foreach ($this->current_slice as $element) {   
        if(!is_array($element)) {
            if(array_key_exists($element, $this->current_tag_array)){
                $S_positions[$element][] = $count;
            }
            elseif(preg_match('/E__/', $element)) {
                $el = preg_replace('/E__/',"", $element);
                if(array_key_exists($el, $this->current_tag_array)){
                    $E_positions[$el][] = $count;
                }
            }        
        }
      $count++;
    }
 

// create the current_tag_array 

  foreach($S_positions as $el => $s_pos) {
     while($s_pos) {
         $start = array_shift($s_pos);
         $end = array_shift($E_positions[$el]);       
         for($i=$start; $i<=$end; $i++) {
              if($i == $start) {
                    array_push($this->current_tag_array[$el], 'S__' .$this->current_slice[$i]);
              }
              else {
                    array_push($this->current_tag_array[$el],$this->current_slice[$i]);
              }
         }          
     }
  }

}


/**
 *  get the name of the element array $el or the element name portion of
 *  the internal string representation of the element
 *
 *  @param mixed $el
 *  @return string 
*/
function XML_PullParser_getElementName($el)
{

  if(isset($el) && is_string($el)) {
     if(!preg_match('/^[SE]__\w+/', $el)) {
            return "";
     }
     list($prefix,$name) = explode('__', $el);
     return $name;
  } 

  if(isset($el[0]) && is_string($el[0])) {

          if(!preg_match('/^[SE]__\w+/', $el[0])) {
            return "";
          }
          list($prefix,$name) = explode('__', $el[0]);
          return $name;
  }
  return "";
}

/**
 *  Determine whether element $el is an element of type $name
 *  
 *  @param array $el
 *  @param string $name
 *  @return bool
 *
*/
function XML_PullParser_isTypeOf($name,$el)
{

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $name = strtoupper($name);
    }
   
    if(is_array($el)) {
        $el = $this->XML_PullParser_getElementName($el);
    }
    else {
        return false;
    }

   return ($el == $name);
}

/**
 *  Returns child array if $name is child of $el, 
 *
 *  Used to determine whether $name is a child of $el. The returned array is equivalent
 *  to TRUE.  If $name is not a child of $el then this method returns NULL. 
 *  
 *  <pre>
 *   if($parser->XML_PullParser_isChildOf($name,$el) ) {
 *        // code here
 *   } 
 *   
 * </pre>
 *
 *
 *
 *  $el can be either the name of an element or an array holding the element; if it is the name
 *  of an element then the $current_element is used and lacking that the current token
 *  
 *  Note: this method will also return an array if $name is the name of $el, 
 *
 *  @param string $name
 *  @param mixed $el  
 *  @return mixed  Returns the child array or Null if child not found
*/
function XML_PullParser_isChildOf($name,$el="")
{

    if (is_string($el)) {
        if ($this->XML_PullParser_isCaseEnfolded()) {
         $el = strtoupper($el);
        }
  	    $parent = $this->current_element;
        if (!$parent) $parent = $this->converted_token;
        if(!$parent) {
            $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN);     
            return false;
        }
        $el = $this->XML_PullParser_getChild($name,1,$parent);
        if(!$el) return Null;  
    }

    if ($this->XML_PullParser_isCaseEnfolded()) {
        $name = strtoupper($name);
   }

    $retval = $this->XML_PullParser_getChild($name,1,$el);
    if($retval === FALSE) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_TOKEN,
             "Failed to get token from XML_PullParser_getChild");     
    }
    return $retval;

   // return $this->XML_PullParser_getChild($name,1,$el);
}

function _getCurrentSlice() 
{
    return $this->current_slice;
}


/**
 *  Get listing of all elements in sequence, including those of dependents,
 *  found in the array $el.
 *
 *  if $el is not supplied, then this function looks for the $current_element
 *  and if that's not found it then uses the current token.
 *
 *  
 *  The list consists of an associative array in which the keys are the
 *  names of the elements and the values the sequence number in the
 *  array being scannned.  For instance, if the $current_element is set to
 *  "SERVER", then:
 *
 *  <pre>
 *    Array
 *   (
 *       [0] => Array
 *            (
 *                [SERVER] => 1
 *            )
 *
 *        [1] => Array
 *            (
 *                [SERVER] => 2
 *            )
 *
 *        [2] => Array
 *            (
 *                [SERVER] => 3
 *            )
 *
 *    )
 *
 *  </pre>
 *
 *  This array is used to sequence through elements with functions that
 *  take a position number, for instance {@link XML_PullParser_getText} and
 *  {@link XML_PullParser_getAttributes}.
 *
 *  XML_PullParser_getSequence silently resets the $current_element to $el.
 *  To set it back to its original value after the sequence array has finished its work,
 *  call:<br>  
 *  $parser->XML_PullParser_resetCurrentElement($parser->_save_current_element) <br>
 *  
 *
 *
 *
 *  @param array $el (optional) array to parse, required only if $args is not present
 *  @param mixed $args (optional) variable length list or array of element names to include in returned array;
 *                those not in list will be ignored.  If this parameter is not passed in, then
 *                the sequence array will include all the elements in $el or the default array 
 *  @return array
 *
*/

function XML_PullParser_getSequence($el="", $args="")
{

    if (!$el) $el = $this->current_element;
    if (!$el) $el = $this->converted_token;
    if (!$el) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_NO_DEFAULT_TOKEN); 
        return false;
    }

    if(!is_array($el)) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_BAD_PARAM, "Array required"); 
        return false;
    }

   if(!$this->XML_PullParser_getElementName($el)) {
        $err = new XML_PullParser_Errors(XML_PullParser_ERROR_BAD_PARAM, "Array parameter is not a token"); 
        return false;
   }

    // reset current element to $el and save original for resetting 
    $this->_save_current_element = $this->XML_PullParser_resetCurrentElement($el); 

    $includes = "";
    $arg_list = Null;
    if ($args && !is_array($args)) {
	$arg_list = func_get_args();
        array_shift($arg_list);
    }
    elseif ($args && is_array($args)) {
	    $arg_list = $args;
    }
    $c=0;
    if (is_array($arg_list) && ($c = count($arg_list))) {
    	for ($i = 0; $i < $c; $i++) {
            $includes .= $arg_list[$i] . '|'; 
     	}
    }

    $includes=rtrim($includes,'|');
    if ($this->XML_PullParser_isCaseEnfolded()) {
       $includes = strtoupper($includes);
    }

   // if there are no $includes, $include becomes the empty string: 
   //  a genuine empty pattern . . .will always match --from perlop.html

   $sequence = array();
   $counter = array();
   foreach ($el as $element) {
     if (is_string($element) && preg_match('/S__(.*)/',$element, $matches)) {            
            if (preg_match("/($includes)/", $matches[1])) {
	        $n=1;
        	if (isset($counter[$matches[1]])) {
                    $n=$counter[$matches[1]];  
	        }
        	$sequence[] = array($matches[1] => $n);        
            	$counter[$matches[1]] = $n+1;
            }
     }
   }  

  return $sequence;
}

/**
 *  Removes all blank CDATA array elements from $token
 *
 *  Tests all CDATA packets against the PERL regex '/\w/'
 *  and deletes from the token array any CDATA element which
 *  does not meet this test. 
 *
 *  @return array
 *
*/
function XML_PullParser_deleteBlanks($token) {

  $temp_array = array();

  foreach ($token as $el) {
       if (is_array($el) && array_key_exists('cdata', $el)) {
          $tmp = trim($el['cdata']);
          if(!preg_match('/\w/', $tmp)) {
              continue; 
          } 
       }
        $temp_array[] =$el; 
  }
  return $temp_array;
}

/**
 *  This method converts the array returned by XML_PullParser_getChildren into
 *  a valid tokenized array
 *
 *  It takes either one or two parameters. 
 *  <OL>
 *   <LI>Two Parameters:  the name of a child element and a tokenized array.<br>
 *    In this case it extracts the children from the token using XML_PullParser_getChildren and then converts the
 *    resulting array to a valid token
 *  <LI>One Parameter: an array that has already been processed by XML_PullParser_getChildren.<br>
 *   In this case, it converts this array to a valid token   
 *  </OL>
 *  
 *  
 *
 * @param $child mixed
 * @param $el optional array 
 *
 * @return array 
 */
function XML_PullParser_tokenFromChildren($child, $el="") {
 if(!$child) {
     return Null;
 }

  if(is_string($child)) {
     $children = $this->XML_PullParser_getChildren($child, $el);
  }
 else {
   $children = $child;
 }

  $tmp_array = array();
  for($i=0; $i<count($children); $i++) {
       foreach($children[$i] as $array_el) {
          $tmp_array[] = $array_el;
       }
  }
  return $tmp_array;

}

function _getCurrentTagArray() 
{
    return $this->current_tag_array;
}


/**
 *  The PHP XML parser, by default, converts all tag names to upper case, called case-folding.  
 *  This method returns TRUE if case folding is in effect.  
 *  To put case sensitivity into effect call package-level {@link XML_PullParser_caseSensitive}.
 *
 *  @return boolean
*/
function XML_PullParser_isCaseFolded() 
{
    return (xml_parser_get_option($this->xml_parser, XML_OPTION_CASE_FOLDING) == XML_OPTION_CASE_FOLDING);

}

/**
 *   Legacy method
 *   @see XML_PullParser_isCaseFolded
 *   @return boolean
 *
*/
function XML_PullParser_isCaseEnfolded() 
{
    return (xml_parser_get_option($this->xml_parser, XML_OPTION_CASE_FOLDING) == XML_OPTION_CASE_FOLDING);

}


/**
 *
 *  Free the parser object.
 *  Needed only if restarting the parsing process.
*/
function XML_PullParser_free()
{
     xml_parser_free($this->xml_parser);
     if($this->fp) {
        fclose($this->fp);
     }
}

/**
 *
 * @param 	string $file	       path/to/xml-file
 * @param 	array  $tags   	       array of elements on which to report back via tokens
 * @param   array  $child_tags     array of child elements on which to report 
 *
 *
*/
function XML_PullParser($file, $tags=Null, $child_tags=Null) 
{
    list($parser, $fp) = $this->_createParser($file);   

    global $_XML_PP_tagsArray, $_XML_PP_childtagsArray, $XML_PullParser_readLength, $XML_PullParser_caseSensitive;

    $this->read_length = $XML_PullParser_readLength;

    xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, (!$XML_PullParser_caseSensitive));

    $this->xml_parser = $parser;
    $this->fp = $fp;
    $ready_to_read = true;

    xml_set_object($this->xml_parser, $this);

    if (is_array($tags) && count($tags)) {
      $this->tags = $tags;
    }
    elseif (is_array($_XML_PP_tagsArray) && count($_XML_PP_tagsArray)) {
       $this->tags = $_XML_PP_tagsArray;
    }
    else { 
        $ready_to_read = false;
    }

    if (is_array($child_tags) && count($child_tags)) {
        $this->child_tags = XML_PullParser_declareChildElements($child_tags);
    }
    elseif (is_array($_XML_PP_childtagsArray)) {
        $this->child_tags = $_XML_PP_childtagsArray;  
    }
    else {
        $ready_to_read = false;
    }


    if ($ready_to_read && !$XML_PullParser_caseSensitive) {
        $this->child_tags = array_change_key_case($this->child_tags, CASE_UPPER);
        for($i=0; $i < count($this->tags); $i++) {
             $this->tags[$i] = strtoupper($this->tags[$i]);
        }      
    }

    
    if ($ready_to_read) {
       $this->_readData();
    }
    else {
        echo "You haven't predeclared or passed into the constructor one or both of" .
          " the tags arrays.\n";
        exit;
    }

}


}   // Class Definition Ends




/**
 *
 * @category   XML
 * @package    XML_PullParser
 * @author     Myron Turner <Myron_Turner@shaw.ca>
 * @copyright  2006 Myron Turner
 * @license    http://www.gnu.org/licenses/gpl.txt  GNU General Public License
 * @version    1.3.2
 *
 *  XML_PullParser_doc takes its input from a string.
 *  It extends XML_PullParser, which takes its input from a file.
 *  This is the only difference between the two modules.
 *
*/

//  XML_PullParser_doc  Class definition begins
class XML_PullParser_doc extends XML_PullParser {

/**
 *  Reads and parses the submitted document as a string
 *
 *  @param string $doc 
*/

 function _readData() 
 {
       if (count($this->accumulator)) {
           // already processed -- this avoids junk after document error
           return false;
       }
       $this->accumulator = array();       
       $this->top_level_tags = array();
       $this->push_back_stk = array();

       if (!xml_parse($this->xml_parser, $this->stream, true)) {
            die(sprintf("XML error: %s at line %d\n",
                        xml_error_string(xml_get_error_code($this->xml_parser)),
                        xml_get_current_line_number($this->xml_parser)));
     }
      
        return true;
}

function XML_PullParser_free()
{
     xml_parser_free($this->xml_parser);
}


/**
 *  Creates a parser object to read the document as a single string
 *
 *  @param string $doc 
*/

function _createParser($doc) 
{ 
 global $XML_PullParser_NAMESPACE_DEF;

    if($XML_PullParser_NAMESPACE_DEF) {
        $xml_parser = xml_parser_create_ns("ISO-8859-1", '|'); 
    }
    else {
        $xml_parser = xml_parser_create();
    }

    xml_set_element_handler($xml_parser, "_startElement", "_endElement");
    xml_set_character_data_handler ($xml_parser, "_characterData");

    $this->stream = $doc;

    return array($xml_parser, Null);
}



}


//  XML_PullParser_doc  Class definition ends
