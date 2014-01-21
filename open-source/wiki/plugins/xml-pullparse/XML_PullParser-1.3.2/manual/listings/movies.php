

<?php
require_once "XML_PullParser.inc";

XML_PullParser_excludeBlanksStrict(true);

$tags = array("Movies");
$child_tags = array();

$parser = new XML_PullParser("Movies.xml", $tags,$child_tags);

$token = $parser->XML_PullParser_getToken(); 

$text_array = $parser->XML_PullParser_getTextArray("Title");
print_r($text_array);

$text_array = $parser->XML_PullParser_getTextArray('movies');
print_r($text_array);
?>
