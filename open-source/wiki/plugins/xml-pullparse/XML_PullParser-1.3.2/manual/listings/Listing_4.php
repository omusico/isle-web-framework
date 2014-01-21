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
            echo "Server Name: " . $parser->XML_PullParser_getText($server) ."\n";  
            $attr_array = $parser->XML_PullParser_getAttributes($server);
            $ip = $parser->XML_PullParser_getAttrVal("ip",$attr_array);
            echo "Server IP: $ip\n";              
        }
    }

/* Result 

    Server Name: example_1.com
    Server IP: 192.168.10.1
    Server Name: example_2.com
    Server IP: 192.168.10.2
    Server Name: example_3.com
    Server IP: 192.168.10.3
*/


?>
