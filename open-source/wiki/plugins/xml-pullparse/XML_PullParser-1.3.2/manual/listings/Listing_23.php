
<?php 

/* 
 *  mixed   XML_PullParser_getText  ([mixed $el = ""], integer $which, [boolean $xcl = false])      
 *  
 * This script demonstrates several features of XML_PullParser_getText, when its $el
 * parameter is a string, i.e. the name of an element.  
 * 
 *    1. If $el is the name of an element and $which > 0,       
 *       it will always return the character data from the named 
 *       element in the $which_th position in the token that is currently
 *       set as the default token.
 *    2. If the $which value is zero, then it will return the character data
 *       from all the elements named $el.
 *    3. The $xcl value does not affect the result if the element parameter is a string.
 *       (This is because the $xcl paramter plays a part only when the element 
 *       parameter is an array.)    
*/
require_once "XML_PullParser.inc";
$doc = <<<XMLDOC
<ENTRY>
   <name>
        <title>Mr.               </title> 
        <firstname>John</firstname>
        <lastname>Smith</lastname>
    </name>
   <name>
        <title>     Ms.</title> 
        <firstname>Jane</firstname>
        <lastname>Jones</lastname>
    </name>
</ENTRY>
XMLDOC;
  
 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "<pre>"; 
   echo preg_replace('/</','&lt;',$doc);
   echo "\n<hr>\n";
  }
   XML_PullParser_excludeBlanks(true);
   XML_PullParser_trimCdata(true);
   $tags = array("entry");
   $child_tags = array("name","lastname");


    $parser = new XML_PullParser_doc($doc, $tags,$child_tags);
    $entry = $parser->XML_PullParser_getToken();   // the default token here will be $entry

    echo "Getting lastname\n";

    echo "Using token from XML_PullParser_getToken(), i.e &lt;ENTRY>. . .&lt;/ENTRY>\n";
    printf("%20s  %4s  %-8s  %s\n", "\$el     ", "\$which", "  \$xcl", "Result");   
    for($i=0; $i<4; $i++) {
        display_result("lastname", $i);
    }


   echo "\nUsing token from XML_PullParser_getElement('name')\n"; 
    printf("%20s  %4s  %-8s  %s\n", "\$el     ", "\$which", "  \$xcl", "Result");   

      /* the default token will be $parser->current_element, which will be an array
         consisting of 'name' elements and their descendents 
     */
   $parser->XML_PullParser_getElement('name');
    for($i=0; $i<4; $i++) {
        display_result("lastname", $i);
    }

   echo "\nUsing token from XML_PullParser_getElement('lastname')\n";
    printf("%20s  %4s  %-8s  %s\n", "\$el     ", "\$which", "  \$xcl", "Result");   

      /* the default token will be $parser->current_element, which will be an array
         consisting of 'lastname' elements -- unlike 'name' it has no descendents 
     */ 

    $last_name =  $parser->XML_PullParser_getElement('lastname');          
    for($i=0; $i<4; $i++) {
        display_result("lastname", $i);
    }



function display_result($el, $which) {
    global $parser;

    $type = "";
    if(is_array($el)) {
        $token = $parser->XML_PullParser_getElementName($el);
        $type = "token($token)";
    }
    else {
        $name = $el;
        $type = "string($name)";
    }

     $result = $parser->XML_PullParser_getText($el,$which, TRUE);
     printf("%20s  %4d  %8s    %s\n", $type, $which, "TRUE", $result);   

     $result = $parser->XML_PullParser_getText($el,$which, FALSE);
     printf("%20s  %4d  %8s    %s\n", $type, $which, "FALSE", $result);   

}

   if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
        echo "</pre>";
   }

?>

