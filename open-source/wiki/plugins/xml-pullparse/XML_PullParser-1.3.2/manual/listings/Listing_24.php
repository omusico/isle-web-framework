
<?php 

/* 
 *  mixed   XML_PullParser_getText  ([mixed $el = ""], integer $which, [boolean $xcl = false])  
 *    
 * This script demonstrates the differences between the way XML_PullParser_getText behaves
 * when $el is a token and when $el the name of an element (i.e. a string)
 *
 * 1. In the first loop the token derived from XML_PullParser_getToken includes the entire 
 *    XML ENTRY structure, with its two 'name' elements and their children.  When this
 *    token is passed to the method and $which = 0, all the character data from ENTRY
 *    is returned when $xcl=FALSE.  When $xcl=TRUE, XML_PullParser_getText filters
 *    ENTRY through XML_PullParser_childXCL, which deletes all descendent elements from the ENTRY array.
 *    This effectively eliminates all character date from the array. 
 *
 *    When $which is grater than zero, and $xcl=FALSE, and $el is an array, XML_PullParser_getText
 *    returns the character data from the $which_th element in the array: the name of this element
 *    is not examined, only its position in the array.   
 *
 *  2. In the first loop,when the string 'name' is passed in as $el, the default token becomes
 *  the token derived from XML_PullParser_getToken.  Thje default token is searched for the element
 *  'name'. No character data is returned in keeping with rule 3.B. of the class documentation:
 *
 *        if $which retains its optional value of zero, then the character data of all elements
 *        named $el is returned but not the character data of their children
 *  
 *    The value of $xcl plays no role when $el is a string.
 *
 *  3. In the second loop the default token becomes "name", because it is assigned by
 *     the call to XML_PullParer_getElement, which creates a token consisting of all
 *     the name elements and their descendents.  The initial surprise is that 
 *     whether or not $el is a string or an array, the result is the same.  But the reason
 *     for this is found in rule 4 of the class documentation:
 *
 *        If $el is a string and is the name of the default token, then the behavior is
 *        the same as when $el is an array.
 *
 *    In this loop, the default token is the array that represents 'name'; therefore, when $el
 *    is a string with the valule of 'name', XML_PullParser_getToken treats it as an array,
 *    not as a string.
 *
 *
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
   $child_tags = array("name");


    $parser = new XML_PullParser_doc($doc, $tags,$child_tags);
    $entry = $parser->XML_PullParser_getToken(); 

    echo "Using token from XML_PullParser_getToken(), i.e &lt;ENTRY>. . .&lt;/ENTRY>\n";
    printf("%20s  %4s  %-8s  %s\n", "\$el     ", "\$which", "  \$xcl", "Result"); 
    for($i=0; $i<6; $i++) {
        display_result($entry, $i);
        display_result("name", $i);
    }

   echo "\nUsing token from XML_PullParser_getElement('name')\n";
   printf("%20s  %4s  %-8s  %s\n", "\$el     ", "\$which", "  \$xcl", "Result"); 
   $name = $parser->XML_PullParser_getElement('name'); 

    for($i=0; $i<6; $i++) {
        display_result($name, $i);
        display_result("name", $i);
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
   echo "<pre>"; 
 }

?>


