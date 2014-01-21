<?php 

require_once "XML_PullParser.inc";


   XML_PullParser_trimCdata(true);
   $tags = array("Entry");
   $child_tags = array("server");
   $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

    while($token = $parser->XML_PullParser_getToken())
    { 
      $parser->XML_PullParser_getElement('server');    
      $n=1;
      while($server = $parser->XML_PullParser_getText('server',$n)) {
          $ip = $parser->XML_PullParser_getAttributes('server',$n);
          echo "Name: $server\n";
         echo "\tIP: " . $parser->XML_PullParser_getAttrVal('ip', $ip)  . "\n";

          $n++;
      }
        
    }

/*
 Result 
        Name: example_1.com
                IP: 192.168.10.1
        Name:  example_2.com
                IP: 192.168.10.2
        Name:  example_3.com
                IP: 192.168.10.3
*/



?>
