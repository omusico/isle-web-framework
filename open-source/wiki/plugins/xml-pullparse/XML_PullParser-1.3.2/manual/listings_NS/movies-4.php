

<?php
require_once "XML_PullParser_NS.inc";
XML_PullParser_excludeBlanks(true);
XML_PullParser_trimCdata(true);

XML_PullParser_excludeBlanksStrict(true);

$tags = array("Movie");
$child_tags = array();

$parser = new XML_PullParser_NS("Movies.xml", $tags,$child_tags);

$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|"
  . "http://room535.org/movies/mov/|http://room535.org/movies/star/|http://room535.org/movies/dates/");


echo "<pre>\n<h2>XML_PullParser_getAttrValues</h2>\n";
while($token = $parser->XML_PullParser_getToken()) {
     
     $attr_vals =  $parser->XML_PullParser_setAttrLoop_elcd($token);

       while($_ns_ = $parser->XML_PullParser_nextAttr())
       {
    

          if($_ns_[0] == 'DATE') { 
             $month = $parser->XML_PullParser_getAttrVal('month', $_ns_[1]);
             $day = $parser->XML_PullParser_getAttrVal('day', $_ns_[1]);
             echo $_ns_[0] . " $month $day " . $_ns_[2] . "\n";
          }
          else {
	          echo $_ns_[0] . " " . $_ns_[2] . "\n";   
          }
       }

   echo "\n";
}
echo "</pre>\n";

/*
Result
This missing items in these results occur where there is no namespace qualifying the element or attribute.

MOVIE
TITLE Gone With The wind
DATE Apr 25 1939
LEADING_LADY Vivien Leigh
LEADING_MAN Clark Gable

MOVIE
TITLE How Green Was My Valley
DATE   1941
LEADING_LADY Maureen O'Hara
LEADING_MAN Walter Pidgeon

MOVIE
TITLE Jurassic Park
DATE June 15 1993
LEADING_LADY Laura Dern
LEADING_MAN


*/
?>
