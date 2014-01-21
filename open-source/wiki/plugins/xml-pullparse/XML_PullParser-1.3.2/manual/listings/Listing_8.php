<?php 

require_once "XML_PullParser.inc";


   $child_tags = array();
   $tags = array("ipaddress", "domain");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

   while($token = $parser->XML_PullParser_getToken())
   { 
      if ($parser->XML_PullParser_isTypeOf('ipaddress', $token)) {
          echo "IP address: " . $parser->XML_PullParser_getText('ipaddress') ."\n";  
      }
       else {
          echo "Domain Name: " . $parser->XML_PullParser_getText('domain') ."\n";  
       }
   }
       



?>
