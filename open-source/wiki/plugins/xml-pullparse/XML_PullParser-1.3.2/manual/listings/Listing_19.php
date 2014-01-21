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


   $parser = new XML_PullParser_doc($doc,array("Entry"),array());     
   while($token = $parser->XML_PullParser_getToken()) {
    //    $attributes = $parser->XML_PullParser_getAttrValues(array("server"=>"Entry"));
        $attributes = $parser->XML_PullParser_getAttrValues(array("server"=>$token));
        foreach($attributes as $attr) {
            foreach($attr as $attr_name => $attr_value) {
                echo "$attr_name => $attr_value\n";
            }
          echo "\n";
        }
    }

/*
  Result
        IP => 192.168.10.1
        REGISTRANT => example.com

        IP => 192.168.10.2

        IP => 192.168.10.3
*/



?>
