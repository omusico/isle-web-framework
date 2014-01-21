

<?php
require_once "XML_PullParser_NS.inc";
XML_PullParser_excludeBlanks(true);

echo "<pre>\n";
$tags = array("Movie");
$child_tags = array('date');

$parser = new XML_PullParser_NS("Movies.xml", $tags,$child_tags);

$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|"
  . "http://room535.org/movies/mov/|http://room535.org/movies/star/|http://room535.org/movies/dates/");


while($token = $parser->XML_PullParser_getToken()) {
     
       $title = $parser->XML_PullParser_getText('title');       
       $leading_man = $parser->XML_PullParser_getText('leading_man');       
       $leading_lady = $parser->XML_PullParser_getText('leading_lady');       


       $year = $parser->XML_PullParser_getText('date');

       $attr_array = $parser->XML_PullParser_getAttributes('date');         
       $month = $parser->XML_PullParser_getAttrVal('month',$attr_array);
       $day = $parser->XML_PullParser_getAttrVal('day',$attr_array);

       echo "Title: $title\n";
       echo "Date: $month $day $year\n";
       echo "Leading Lady: $leading_lady\n";
       if($leading_man) {
        echo "Leading Man: $leading_man\n";
       }
       echo "\n\n";
exit;
}

echo "\n</pre>\n";


/*
Result

Title: Gone With The wind
Date: Apr 25 1939
Leading Lady: Vivien Leigh
Leading Man: Clark Gable


Title: How Green Was My Valley
Date:   1941
Leading Lady: Maureen O'Hara
Leading Man: Walter Pidgeon


Title: Jurassic Park
Date: June 15 1993
Leading Lady: Laura Dern

*/
?>
