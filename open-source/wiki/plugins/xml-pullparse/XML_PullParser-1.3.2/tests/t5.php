<?php

require_once 'PHPUnit/TestCase.php'; 
require_once 'XML_PullParser.inc';

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

$doc_2 = 
'<ENTRY>
   <name>
       <title>+%}</title>
        <firstname>
        </firstname>
        <lastname>Smith</lastname>
    </name>
   <name>
        <title>
</title> 
        <firstname>Jane</firstname>
        <lastname>Jones</lastname>
    </name>

</ENTRY>';


class XML_PUllParser_Test_5 extends PHPUnit_TestCase {
var $parser;
var $tags = array('Entry');
var $child_tags = array("name","firstname","lastname");

function setUp()    {  
    global $doc;    

    XML_PullParser_trimCdata(true);
    XML_PullParser_excludeBlanks(true);

    $this->parser = new XML_PullParser_doc($doc,$this->tags,$this->child_tags);   

}


function test_getText_getToken_0_TRUE() {


 $token = $this->parser->XML_PullParser_getToken();

 $this->assertEquals(null,$this->parser->XML_PullParser_getText($token,0, TRUE));
 $this->assertEquals(null,$this->parser->XML_PullParser_getText('name',0, TRUE));
    
}


function test_getText_getToken_0_FALSE() {
 $token = $this->parser->XML_PullParser_getToken();

 $this->assertRegExp('/Mr\.\s+John\s+Smith\s+Ms\.\s+Jane\s+Jones/',$this->parser->XML_PullParser_getText($token,0, FALSE));
 $this->assertEquals(NULL,$this->parser->XML_PullParser_getText('name',0, FALSE));
}


function test_CDATA_Settings() {
   $this->reInitialize();

   $token = $this->parser->XML_PullParser_getToken();
   $str_3 =   $this->parser->XML_PullParser_getText($token,0, FALSE);
   $this->assertContains('+%}', $str_3);

   XML_PullParser_excludeBlanksStrict(true);
   $str_3 =   $this->parser->XML_PullParser_getText($token,0, FALSE);
   $this->assertNotContains('+%}', $str_3);
}

function test_getText_getToken_STRLEN_FALSE() {

   XML_PullParser_trimCdata(false);
   XML_PullParser_excludeBlanks(false);


   $token = $this->parser->XML_PullParser_getToken();
   $str_1 =   $this->parser->XML_PullParser_getText($token,0, FALSE);

   XML_PullParser_trimCdata(true);
   XML_PullParser_excludeBlanks(true);
   $str_2 =   $this->parser->XML_PullParser_getText($token,0, FALSE);

  $this->assertTrue($str_2 > $str_1);


}



function test_getElement_WHICH_EQUALS_ZERO_NAME() {


$token = $this->parser->XML_PullParser_getToken();
$name = $this->parser->XML_PullParser_getElement('name'); 

   $str_1 =  $this->parser->XML_PullParser_getText($name,0, TRUE);
   $str_2 =  $this->parser->XML_PullParser_getText("name",0, TRUE);
   $str_3 =  $this->parser->XML_PullParser_getText($name,0, FALSE);
   $str_4 =  $this->parser->XML_PullParser_getText("name",0, FALSE);

   $this->assertNull($str_1); 
   $this->assertTrue($str_2 == $str_1 && $str_3 == $str_4); 
}

function test_getElement_WHICH_EQUALS_ZERO_LASTNAME() {

$token = $this->parser->XML_PullParser_getToken();
$name = $this->parser->XML_PullParser_getElement('lastname'); 


   $str_1 =  $this->parser->XML_PullParser_getText($name,0, TRUE);
   $str_2 =  $this->parser->XML_PullParser_getText("lastname",0, TRUE);
   $str_3 =  $this->parser->XML_PullParser_getText($name,0, FALSE);
   $str_4 =  $this->parser->XML_PullParser_getText("lastname",0, FALSE);

   $this->assertTrue($str_2 == $str_1 && $str_2 == $str_3 && $str_4 == $str_1); 
}

function test_getElement_WHICH_EQUALS_ONE() {

  $token = $this->parser->XML_PullParser_getToken();
  $name = $this->parser->XML_PullParser_getElement('lastname'); 

    $str_1 = $this->parser->XML_PullParser_getText($name,1, TRUE);
    $str_2 = $this->parser->XML_PullParser_getText("lastname",1, TRUE);
    $str_3 = $this->parser->XML_PullParser_getText($name,1, FALSE);
    $str_4 = $this->parser->XML_PullParser_getText("lastname",1, FALSE);

   $this->assertTrue($str_2 == $str_1 && $str_2 == $str_3 && $str_4 == $str_1); 
}



function reInitialize() {
   global $doc_2;
   $this->parser->XML_PullParser_free();
   $this->parser = new XML_PullParser_doc($doc_2,$this->tags,$this->child_tags);      
}



function tearDown() {

   $this->parser->XML_PullParser_free();

}


}
 
?>


<?php
 require_once '/usr/share/pear/PHPUnit.php';
 $suite  = new PHPUnit_TestSuite('XML_PUllParser_Test_5');

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "<pre>";
 }


 echo "----------------------------------------------------\n";
 echo "\tTESTING ENHANCED getText METHOD\n";
 echo "----------------------------------------------------\n";

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "</pre>";
 }



 $result = PHPUnit::run($suite);

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo $result->toHTML();
 }
 else { 
  print $result->toString();
 }



?>


