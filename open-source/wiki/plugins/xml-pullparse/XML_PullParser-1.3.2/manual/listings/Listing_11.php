<?php 

require_once "XML_PullParser.inc";

    $tags = array("Entry");
    $child_tags = array();
    $parser = new XML_PullParser("DNS.xml",$tags,$child_tags);

    while($token = $parser->XML_PullParser_getToken())
    { 

      $seq =  $parser->XML_PullParser_getSequence();

       for($i=0; $i  <  count($seq); $i++) {  
          list($element, $which) = each($seq[$i]);  

          switch($element) {
            case 'IPADDRESS':
                echo "$element: $which\n";
                echo $parser->XML_PullParser_getText($element,$which) . "\n";
                break;
            case 'SERVER':
                echo "$element: $which\n";
                echo $parser->XML_PullParser_getText($element,$which) . "\n";
                $ip = $parser->XML_PullParser_getAttributes($element,$which);         
                echo "\tIP: " . $parser->XML_PullParser_getAttrVal('ip', $ip) . "\n";
                break;
            case 'DOMAIN':
                echo "$element: $which\n";
                echo $parser->XML_PullParser_getText($element,$which) . "\n";
                break;
            case 'ALIAS':
                echo "$element: $which\n";
                echo $parser->XML_PullParser_getText($element,$which) . "\n";
                break;
            default:
                echo "default: $element: $which\n";
                
         }
      }
        
   }

/* Result
    default: ENTRY: 1
    IPADDRESS: 1
    172.20.19.6
    DOMAIN: 1
    example.com
    SERVER: 1
    example_1.com
            IP: 192.168.10.1
    SERVER: 2
    example_2.com
            IP: 192.168.10.2
    SERVER: 3
    example_3.com
            IP: 192.168.10.3
    ALIAS: 1
    www.example.com
 */

?>
