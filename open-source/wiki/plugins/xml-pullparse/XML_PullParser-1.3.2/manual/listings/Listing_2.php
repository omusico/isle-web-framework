<?php 

require_once "XML_PullParser.inc";

   $tags = array("Entry");
   $child_tags = array("server");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

   while($token = $parser->XML_PullParser_getToken())
   { 
       $dns_servers=$parser->XML_PullParser_getElement('server');    
       while($server = $parser->XML_PullParser_nextElement()) 
       {        
           echo "Server: " . $parser->XML_PullParser_getText($server) ."\n";
       }
   }
    /*  Result
        Server: example_1.com
        Server: example_2.com
        Server: example_3.com
    */	         
	

?>
