

<?php
require_once "XML_PullParser_NS.inc";
XML_PullParser_excludeBlanks(true);

echo "<pre>\n";
$tags = array("Movie");
$child_tags = array('date');

$parser = new XML_PullParser_NS("Movies.xml", $tags,$child_tags);

$parser->XML_PullParser_setCurrentNS("http://room535.org/movies/xtitle/|"
  . "http://room535.org/movies/mov/|http://room535.org/movies/star/|http://room535.org/movies/dates/");




while($token = $parser->XML_PullParser_getToken()) {

         // since XML_PullParser_getElement has not been called 
         // XML_PullParser_setAttrLoop_elcd will use $token
       $attr_vals = $parser->XML_PullParser_setAttrLoop_elcd(); 
       while($at = $parser->XML_PullParser_nextAttr()) { 
       if($at[2]) {      
          echo "$at[0]: $at[2]\n";
       }
       foreach($at[1] as $attr_name => $attr_value) {
           $name = "";
          if(preg_match('/DAY/i',$attr_name)) {
            $name = "day";
          }
          if(preg_match('/month/i',$attr_name)) {
            $name = "month";
         }
         
         if($name) {
            echo "$name:  " . $parser->XML_PullParser_getAttrVal($name, $at[1]) . "\n";
         }
         if($at[0] == 'LEADING_MAN') {
               echo "\n";
         }
      }
      
     }

}





echo "\n</pre>\n";


?>
