<?php
   //  Listing 18

    require_once "XML_PullParser.inc";
    
   //  XML_PullParser_excludeBlanks(true);
    
    $tags = array("entry");
    $child_tags = array("server","domain");

    $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);     

    $token = $parser->XML_PullParser_getToken();
    echo $parser->XML_PullParser_getText() . "\n";

    $el = $parser->XML_PullParser_getElement("server");
    echo $parser->XML_PullParser_getText() . "\n";


    $parser->XML_PullParser_getElement("domain");
    echo $parser->XML_PullParser_getText() . "\n";


/*
Result
 
 172.20.19.6
  example.com
  example_1.com
  example_2.com
  example_3.com
  www.example.com

 example_1.com example_2.com example_3.coM
 example.com
*/

?>

