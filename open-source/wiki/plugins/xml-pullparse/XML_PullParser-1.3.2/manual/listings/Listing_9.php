<?php 

require_once "XML_PullParser.inc";


  $tags = array("entry");
  $child_tags = array("server","ipaddress", "domain");
  $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);     
  while($token = $parser->XML_PullParser_getToken())
  { 
      $parser->XML_PullParser_getElement('server');    
      $which=1;
      while($server = $parser->XML_PullParser_getText('server',$which)) {
         $ip = $parser->XML_PullParser_getAttributes('server',$which);
         echo "Name: $server\n";
         echo "\tIP: " . $parser->XML_PullParser_getAttrVal('ip', $ip) . "\n";   
         $which++;
     }
  }
/*
  Result
    Name:  example_1.com
        IP: 192.168.10.1
    Name:  example_2.com
        IP: 192.168.10.2
    Name:  example_3.com
        IP: 192.168.10.3
*/
        



?>
