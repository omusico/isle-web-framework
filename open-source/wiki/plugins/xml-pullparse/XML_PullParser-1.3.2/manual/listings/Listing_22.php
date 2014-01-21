<?php
require_once "XML_PullParser.inc";

    XML_PullParser_excludeBlanks(true);
    XML_PullParser_trimCdata(true);

    $tags = array("DNS");
    $child_tags = array("Entry");
    $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);
     

     while($token = $parser->XML_PullParser_getToken()) 
     {

     $entry = $parser->XML_PullParser_getElement("entry");      
     $child = $parser->XML_PullParser_getChild("server",2,$entry);     
     $child = $parser->XML_PullParser_getText("server",2,$entry);     
     echo "$child\n";
     $attr_array = $parser->XML_PullParser_getAttributes("server", 2, $child);
     if($attr_array) {
     foreach($attr_array as $n => $v) {
            echo "$n -- $v\n";
      }
     }
     elseif($attr_array === FALSE) {
       $err = XML_PullParser_Errors_userDefined("Bad Attributes array");
       echo XML_PullParser_Errors_getUserDefined($err) . "\n";
       echo XML_PullParser_Errors_Trace() . "\n";
       exit;
     }
 
    
  }


/* Result
example_2.com
User defined error: Bad Attributes array
Line: 26, Top Level

------Error Trace------------
ERROR:  User defined error: Bad Attributes array
Line: 26, Top Level
Error Number: 7  (XML_PullParser_ERROR_USER_DEFINED)

ERROR:  Missing or Wrong Parameter: Array parameter "example_2.com" is not a token
Line: 2068, function: XML_PullParser_getAttributes
Error Number: 5  (XML_PullParser_ERROR_BAD_PARAM)

-----End Trace:-------------

*/
?>
