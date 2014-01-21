<?php 

require_once "XML_PullParser.inc";


  $tags = array("entry");
   $child_tags = array("server","ipaddress", "domain");

   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);     

   while($token = $parser->XML_PullParser_getToken())
   { 
     $parser->XML_PullParser_getElement('server');    
     $seq =  $parser->XML_PullParser_getSequence();

     for($i=0; $i  <  count($seq); $i++) {
          list($server, $which) = each($seq[$i]);  

          $name = $parser->XML_PullParser_getText($server,$which);
          echo "Name: $name\n";

          $ip = $parser->XML_PullParser_getAttributes($server,$which);         
          echo "\tIP: " . $parser->XML_PullParser_getAttrVal('ip', $ip) . "\n";
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
