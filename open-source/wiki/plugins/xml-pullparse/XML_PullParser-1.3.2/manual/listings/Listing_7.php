<?php 

require_once "XML_PullParser.inc";


   $child_tags = array();
   $tags = array("ipaddress", "domain");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

   while($token = $parser->XML_PullParser_getToken())
   { 
     if($ip = $parser->XML_PullParser_getText('ipaddress')) {
       echo "IP address: " . $ip ."\n";  
    }
   if($domain = $parser->XML_PullParser_getText('domain')) {
       echo "Domain Name: " . $domain ."\n\n";  
    }
   }
       
 /*
  Result
        IP address: 172.20.19.6
        Domain Name:  example.com
 */


?>
