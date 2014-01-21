

<?php
require_once "XML_PullParser_NS.inc";
XML_PullParser_excludeBlanks(true);
XML_PullParser_trimCdata(true);

XML_PullParser_excludeBlanksStrict(true);

$tags = array("Movie");
$child_tags = array('date','title');

$parser = new XML_PullParser_NS("Movies.xml", $tags,$child_tags);

$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|"
  . "http://room535.org/movies/mov/|http://room535.org/movies/star/|http://room535.org/movies/dates/");

echo "\n<h2>XML_PullParser_getAttrValues</h2>\n";

while($token = $parser->XML_PullParser_getToken()) {

      $title =  $parser->XML_PullParser_getText('title');  
      $year = $parser->XML_PullParser_getText('date');

      $attr_vals =  $parser->XML_PullParser_getAttrValues(array('date'=>$token));

      $month = $parser->XML_PullParser_getAttrVal  ('month',$attr_vals[0]);
    
      $day = $parser->XML_PullParser_getAttrVal  ('day',$attr_vals[0]);
      echo "$title "; 
      echo "$month $day $year\n";

}

/*
Result
Notice that there is no month or day for How Green Was My Valley.  This is because
they are not qualfied by a namespace

Gone With The wind Apr 25 1939
How Green Was My Valley   1941
Jurassic Park June 15 1993

*/ 
?>
