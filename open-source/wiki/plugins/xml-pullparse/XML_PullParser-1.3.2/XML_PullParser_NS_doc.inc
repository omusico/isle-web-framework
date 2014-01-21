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
 * @package    XML_PullParser_NS
 * @author     Myron Turner <Myron_Turner@shaw.ca>
 * @copyright  2006 Myron Turner
 * @license    http://www.gnu.org/copyleft/gpl.txt   GNU General Public License 
 * @version    1.0.0
 *
*/

require_once "XML_PullParser.inc";

/**
 * XML_PullParser_NS adds namespace support to XML_PullParser.
 *  
 * @package XML_PullParser_NS 
 * @author Myron Turner <Myron_Turner@shaw.ca>
 * @license    http://www.gnu.org/copyleft/gpl.txt   GNU General Public License 
 * @version    1.0.2   
 *
*/

XML_PullParser_NamespaceSupport(true);
class XML_PullParser_NS_doc extends XML_PullParser {
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

    $xml_parser = xml_parser_create_ns("ISO-8859-1", '|'); 

    xml_set_element_handler($xml_parser, "_startElement", "_endElement");
    xml_set_character_data_handler ($xml_parser, "_characterData");

    $this->stream = $doc;

    return array($xml_parser, Null);
}




}



?>
