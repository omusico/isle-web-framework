<?php

require_once "XML_PullParser.inc";
$doc=
'<?xml version="1.0" ?>
<keymap>
  <desc lang="En" />
  <sort type="non" />
  <mappings>
    <map src="b" dest="2" />
    <map src="&lt;" dest="6" /> 
    <map src="bc" dest="$1" />
    <map src="e" dest="3" />
    <map src="abd" dest="*" />
    <map src="ad" dest="@" />  
    <map src="abd" dest="*" />
    <map src="ad" dest="@" />
  </mappings>
</keymap>';

echo "\$which technique\n";
   $tags = array("keymap");
   $child_tags = array("map");
   $parser = new XML_PullParser_doc($doc,$tags,$child_tags);
   $token = $parser->XML_PullParser_getToken();
   $which=1;
   $maps = $parser->XML_PullParser_getElement('map');
   while($map = $parser->XML_PullParser_getAttributes($maps, $which)) {
          echo "src => " . $map['SRC'] ."\n";  // attribute names must use caps since case-folding is in effect
          echo "dest => " . $map['DEST'] ."\n\n";
          $which++;
   }

echo "\nXML_PullParser_nextElement technique\n";   
   while($server = $parser->XML_PullParser_nextElement())
       {
           $attr_array = $parser->XML_PullParser_getAttributes($server);
           $src = $parser->XML_PullParser_getAttrVal("src",$attr_array); // method takes case-folding into account
           $dest = $parser->XML_PullParser_getAttrVal("dest",$attr_array);
           echo "src: $src, dest: $dest\n";  
     }
      echo "\n";
   
?>
