<?php
require_once "XML_PullParser_NS_doc.inc";
XML_PullParser_excludeBlanks(true);
XML_PullParser_trimCdata(true);

XML_PullParser_excludeBlanksStrict(true);

$doc = <<<DOC
<Movies
 xmlns = "http://fedora.gemini.ca/local/"
 xmlns:mov = "http://room535.org/movies/mov/"
 xmlns:star = "http://room535.org/movies/star/"
 xmlns:title = "http://room535.org/movies/title/"
 xmlns:date = "http://room535.org/movies/dates/"
>
 <Movie>
    <title:Title>Gone With The wind</title:Title>
    <date:date date:day="25" date:month="Apr">1939</date:date>
    <star:leading_lady>Vivien Leigh</star:leading_lady>
    <star:leading_man>Clark Gable</star:leading_man>
 </Movie>

  <mov:Movie>
    <title:Title>How Green Was My Valley</title:Title>
    <date:date day = "2" month="May">1941</date:date>
    <star:leading_lady>Maureen O'Hara</star:leading_lady>
    <star:leading_man>Walter Pidgeon</star:leading_man>
 </mov:Movie>

 <Movie>
 <title:Title>Jurassic Park</title:Title>
    <date:date date:day="15" date:month="June">1993</date:date>
    <star:leading_lady>Laura Dern</star:leading_lady>
    <leading_man>Sam Neil</leading_man>
 </Movie>
</Movies>
DOC;




$tags = array("Movie");
$child_tags = array();

//XML_PullParser_NamespaceSupport(true);
$parser = new XML_PullParser_NS_doc($doc, $tags,$child_tags);

$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/title/|http://fedora.gemini.ca/local/|"
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
This result gets Sam Neil in Jurassic Park because, as opposed to movies-5.php, it includes
the default namespace in the namespace definition


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
LEADING_MAN Sam Neil

*/
?>
