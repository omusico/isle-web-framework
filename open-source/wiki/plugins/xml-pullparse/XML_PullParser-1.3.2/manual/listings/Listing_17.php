<?php 

require_once "XML_PullParser.inc";


    $tags = array("Movies");
    $child_tags = array();

    $parser = new XML_PullParser("Movies.xml", $tags,$child_tags);

    $token = $parser->XML_PullParser_getToken(); 

    $text_array = $parser->XML_PullParser_getTextArray("Title");
    print_r($text_array);

/*
 Result
     Array
    (
        [0] => Gone With The wind
        [1] => How Green Was My Valley
        [2] => Jurassic Park
    )
*/



?>
