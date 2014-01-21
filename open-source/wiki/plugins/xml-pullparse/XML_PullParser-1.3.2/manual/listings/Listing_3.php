<?php 

require_once "XML_PullParser.inc";

   $tags = array("Entry");
   $child_tags = array("server");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

   while($token = $parser->XML_PullParser_getToken())
   {
       $parser->XML_PullParser_getElement('server');    
       while($server = $parser->XML_PullParser_nextElement()) 
       {        
           $attr_array = $parser->XML_PullParser_getAttributes($server);
           $ip = $parser->XML_PullParser_getAttrVal("ip",$attr_array);
           echo "Server IP: $ip\n";  
       }
      echo "\n";
   }

/* Result 
    Server IP: 192.168.0.1
    Server IP: 192.168.0.2
    Server IP: 192.168.0.3
    Server IP: 192.168.0.4
    
*/


?>
