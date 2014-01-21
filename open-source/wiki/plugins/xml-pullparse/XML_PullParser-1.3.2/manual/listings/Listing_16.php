<?php 

require_once "XML_PullParser.inc";


  $tags = array('para');
  $child_tags = array();
  $parser = new XML_PullParser("List.xml", $tags, $child_tags);

                   $parser->XML_PullParser_getToken();  
                   $list = $parser->XML_PullParser_getChild('simpleList');

                   $which = 1;
                   $items = "";
                   echo " < OL>\n"; 
                     while($member =  $parser->XML_PullParser_getChild('member',$which,$list)) {
                        $member_text =  $parser->XML_PullParser_getText($member);
                        $items .= " < LI>". trim($member_text) . "\n";
                        $which++; 
                   }
                   echo $items;
                   echo " < /OL>\n"; 

/*  Result: Child token $member returned by XML_PullParser_getChild
    [8] => S__MEMBER
    [9] => Array
        (
        )

    [10] => Array
        (
            [cdata] =>
        array XML_PullParser_getChild (string $child, [integer $which = 1], [array $el = ""])

        )

    [11] => E__MEMBER
*/




?>
