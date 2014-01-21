

<?php
require_once "XML_PullParser_NS.inc";
XML_PullParser_excludeBlanks(true);

echo "<pre>\n";
$tags = array("Movie");
$child_tags = array('date');

$parser = new XML_PullParser_NS("Movies.xml", $tags,$child_tags);

$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|"
  . "http://room535.org/movies/mov/|http://room535.org/movies/star/|http://room535.org/movies/dates/");

echo "\n<h2>\$parser->XML_PullParser_nextElement</h2>\n";

while($token = $parser->XML_PullParser_getToken()) {
     
       $date = $parser->XML_PullParser_getElement('date');

       while($_ns_ = $parser->XML_PullParser_nextElement())
       {

           $attr_array = $parser->XML_PullParser_getAttributes($_ns_);         
           $text = $parser->XML_PullParser_getAttrVal  ('month',$attr_array);
           echo "Month: $text\n";
       }

}

echo "\n</pre>\n";


/*
Result
The scond month is missing because the month for How Green Was My Valley is not
qualfied by a namespace

Month: Apr
Month:
Month: June

*/
?>
