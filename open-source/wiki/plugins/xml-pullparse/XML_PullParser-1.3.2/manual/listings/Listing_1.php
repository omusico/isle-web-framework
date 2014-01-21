<?php 

require_once "XML_PullParser.inc";


   $tags = array("Entry");
   $child_tags = array("ipaddress");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

   while($token = $parser->XML_PullParser_getToken())
   { 
       $ipaddress=$parser->XML_PullParser_getElement('ipaddress');       
       echo "IP Adress: " . $parser->XML_PullParser_getText() ."\n";
   }
                    
    /*  Result
        IP Adress: 172.20.19.6
    */	         


?>
