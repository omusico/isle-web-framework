

<?php
require_once "XML_PullParser.inc";
XML_PullParser_excludeBlanks(true);
XML_PullParser_trimCdata(true);

XML_PullParser_excludeBlanksStrict(true);
XML_PullParser_NamespaceSupport(true);
$tags = array("Movies");
$child_tags = array();

$parser = new XML_PullParser("Movies.xml", $tags,$child_tags);
$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|"
  . "http://room535.org/movies/mov/|http://room535.org/movies/star/|http://room535.org/movies/dates/");

while($token = $parser->XML_PullParser_getToken()) {

    $text_array = $parser->XML_PullParser_getTextArray("Title");
    print_r($text_array);

    $text_array = $parser->XML_PullParser_getTextArray('movies');
    print_r($text_array);

 
}

/*
Result
Notice that Sam Neil is missing, after Laura Dern.  This is because his name is not prefixed by 
a defined namespace


Array
(
    [0] => Gone With The wind
    [1] => How Green Was My Valley
    [2] => Jurassic Park
)
Array
(
    [0] => Gone With The wind
    [1] => 1939
    [2] => Vivien Leigh
    [3] => Clark Gable
    [4] => How Green Was My Valley
    [5] => 1941
    [6] => Maureen O'Hara
    [7] => Walter Pidgeon
    [8] => Jurassic Park
    [9] => 1993
    [10] => Laura Dern
)

*/  
   
?>
