<?php 

require_once "XML_PullParser.inc";


   $child_tags = array();
   $tags = array("ipaddress");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

   while($token = $parser->XML_PullParser_getToken())
   { 
       echo "IP address: " . $parser->XML_PullParser_getText('ipaddress') ."\n";  
   }
       

/*
 Result
 IP address: 172.20.19.6
*/

?>
