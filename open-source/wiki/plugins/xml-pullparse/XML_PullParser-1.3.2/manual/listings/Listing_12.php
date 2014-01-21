<?php 

require_once "XML_PullParser.inc";

$doc =
'<ENTRY> 
<ipaddress>172.20.19.6  </ipaddress> 
<domain> example.com  </domain> 
<server ip="192.168.10.1">
example_1.com 
<registrant>mturner.org </registrant>
</server> 
<server ip="192.168.10.2"> example_2.com  </server> 
<server ip="192.168.10.3"> example_3.com  </server> 
<alias> www.example.com  </alias> 
</ENTRY>';
 
   $tags = array("Entry");
   $child_tags = array("server");
   $parser = new XML_PullParser_doc($doc,$tags,$child_tags);

    while($token = $parser->XML_PullParser_getToken())
   { 

     $servers = $parser->XML_PullParser_getElement('server'); 
     $servers = $parser->XML_PullParser_childXCL($servers);   
     $seq =  $parser->XML_PullParser_getSequence($servers); 

      for($i=0; $i  <  count($seq); $i++) {  
         list($server, $which) = each($seq[$i]);  
 
          $name = $parser->XML_PullParser_getText($server,$which);
          echo "Name: $name \n";

          $ip = $parser->XML_PullParser_getAttributes($server,$which);         
          echo "\tIP: " . $parser->XML_PullParser_getAttrVal('ip', $ip) . "\n";
      }        
    }

/*
 Result
Name:
example_1.com


        IP: 192.168.10.1
Name:  example_2.com
        IP: 192.168.10.2
Name:  example_3.com
        IP: 192.168.10.3

*/



?>
