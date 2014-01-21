<?php
$doc = 
'<DNS>
<ENTRY> 
<ipaddress type="primary">172.20.19.6 </ipaddress> 
<server ip="192.168.10.1" registrant="example.com"> example_1.com </server> 
<server ip="192.168.10.2"> example_2.com </server> 
<server ip="192.168.10.3"> example_3.com </server> 
<alias> www.example.com </alias> 
</ENTRY> 

</DNS>
';


    require_once "XML_PullParser.inc";


   $parser = new XML_PullParser_doc($doc,array("Entry"),array('server'));     

   while($token = $parser->XML_PullParser_getToken())
    {
       $parser->XML_PullParser_getElement('server');   
       $parser->XML_PullParser_setAttrLoop();

       while($attr = $parser->XML_PullParser_nextAttr()) {
           foreach($attr[1] as $attr_name => $attr_value) {
                echo "$attr[0]: $attr_name => $attr_value\n";
            }
        echo "\n";  
        }
    }


//      echo XML_PullParser_Errors_Trace() . "\n";
/*
    Result
        SERVER: IP => 192.168.10.1
        SERVER: REGISTRANT => example.com

        SERVER: IP => 192.168.10.2

        SERVER: IP => 192.168.10.3
*/

?>
