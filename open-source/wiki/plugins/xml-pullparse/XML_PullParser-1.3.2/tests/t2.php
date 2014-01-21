<?php

require_once 'PHPUnit/TestCase.php'; 
require_once 'XML_PullParser.inc';

$doc=<<<XMLDOC
<DNS>
<ENTRY> 
<ipaddress>172.20.19.6</ipaddress> 
<domain>example.com</domain> 
<server ip="192.168.10.1">example_1.com</server> 
<server ip="192.168.10.2">example_2.com</server> 
<server ip="192.168.10.3">example_3.com</server> 
<alias>www.example.com</alias> 
</ENTRY> 
</DNS>
XMLDOC;


class XML_PUllParser_Test_2 extends PHPUnit_TestCase {
var $parser;
var $tags = array('Entry');
var $child_tags = array();
var $file = 'DNX.xml';


function setUp()    {
    global $doc;
    XML_PullParser_Errors_INI();
    $this->child_tags = array("server","ipaddress", "domain");
    $this->parser = new XML_PullParser_doc($doc,$this->tags,$this->child_tags);   

}


function test_getChild() {
    $token = $this->parser->XML_PullParser_getToken();
    $server = $this->parser->XML_PullParser_getChild('server',1); 

    $this->assertContains('S__SERVER', $server,
        "\nDid not find server START tag in result returned from XML_PullParser_getChild.\n");
}

function test_getChild_False() {

    $server = $this->parser->XML_PullParser_getChild('server',1);   
    $this->assertEquals(False, $server,
        "\nFalse expected when a token is not available for searching in XML_PullParser_getChild.\n");
}

function test_getChild_NULL() {
    $token = $this->parser->XML_PullParser_getToken();
    $server = $this->parser->XML_PullParser_getChild('not_an_element',1);   

    $this->assertEquals(Null, $server,
        "\nNull is expected when child not found in XML_PullParser_getChild.\n");
}


function test_getChild_getChildren_Number() {
      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getChildren('server');
      
      $which=1;
      while($server = $this->parser->XML_PullParser_getChild('server',$which)) {   
         $which++;
     }
 
    $which--; 
    $this->assertEquals(sizeof($servers) , $which,
        "\nNumber of server elements from XML_PullParser_getChildren doesn't match number found by XML_PullParser_getChild.\n");
}

function test_getChild_getChildren_FALSE() {
//      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getChildren('server');
      $this->assertEquals(false , $servers,  "\nShould be False: No token available\n");
}


function test_getChild_getChildren_EmptyArray() {
      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getChildren('not_server');
      $this->assertEquals(array() , $servers,  "\nShould be Empty Array: child not found\n");
}

function test_getText_Elements() {
    
      $token = $this->parser->XML_PullParser_getToken();
      $this->parser->XML_PullParser_getElement('server');    
      $texts = $this->parser->XML_PullParser_getTextArray('server');
      
      $result_array = array();
      $which=1;
      while($server = $this->parser->XML_PullParser_getText('server',$which)) {       
         $result_array[] = $server;
         $which++;
     }

    $this->assertEquals($texts , $result_array);
}

function test_getText_Size() {
    
      $token = $this->parser->XML_PullParser_getToken();
      $this->parser->XML_PullParser_getElement('server');    
      
      $result_array = array();
      $which=1;
      while($server = $this->parser->XML_PullParser_getText('server',$which)) {       
         $result_array[] = $server;
         $which++;
     }

    $this->assertEquals(3 , sizeof($result_array));
}


function test_getText_FALSE_1() {

    $server = $this->parser->XML_PullParser_getText("server");
    $this->assertEquals(false , $server,  "\nShould be False: No token available\n");
}

function test_getText_FALSE_2() {

    $server = $this->parser->XML_PullParser_getText();
    $this->assertEquals(false , $server, "\nShould be False: No token available\n");
}


function test_getText_NULL_1() {
    $token = $this->parser->XML_PullParser_getToken();
    
    $server = $this->parser->XML_PullParser_getText('not_a_valid_element');
    $this->assertEquals(Null, $server, "\nInvalid element without \$which: should return false\n");
}

function test_getText_NULL_2() {
    $token = $this->parser->XML_PullParser_getToken();
    
    $server = $this->parser->XML_PullParser_getText('not_a_valid_element',1);
    $this->assertEquals(NULL, $server,  "\nElement Not Found, using a \$which value: should return Null\n");
}

function test_getText_CDATA() {
    $token = $this->parser->XML_PullParser_getToken();
    
    $server = $this->parser->XML_PullParser_getText('server',2);
    $this->assertEquals('example_2.com', $server, "\nFetched Seecond server text: example_2.com\n");
}

function test_getAttributes() {

      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getElement('server');    

//      $attrs = $this->parser->XML_PullParser_getAttrValues(array('server'=>$servers));

      $which=1;
      while($ip = $this->parser->XML_PullParser_getAttributes('server',$which)) {
         $which++;
     }

    $which--;      
    $this->assertEquals(3, $which, "\nFetched 3 server attributes\n");
}

function test_getAttributes_AttrVal() {

      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getElement('server');    

       $which=2;
       $ip = $this->parser->XML_PullParser_getAttributes('server',$which);
       $ip = $this->parser->XML_PullParser_getAttrVal('ip', $ip);   
      
      $this->assertEquals('192.168.10.2', $ip,
        "\nFetched 2nd server attribute/value: ip=192.168.10.2: \n"
         . "failure could be in either _getAttributes or _getAttrVal\n");
}


function test_getAttributes_NULL() {

      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getElement('server');    

       $which=6;
       if($ip = $this->parser->XML_PullParser_getAttributes('server',$which)) {
           $attr = $this->parser->XML_PullParser_getAttrVal('ip', $ip);   
       }
      
      $this->assertEquals(NULL, $ip, "\nRequested out of range \$which value: should return NULL\n");
}


function test_getAttrVal_NULL() {

      $token = $this->parser->XML_PullParser_getToken();
      $servers = $this->parser->XML_PullParser_getElement('server');    

       $which=1;
       if($ip = $this->parser->XML_PullParser_getAttributes('server',$which)) {
           $attr = $this->parser->XML_PullParser_getAttrVal('ipx', $ip);   
       }

       $this->assertEquals(NULL, $attr, "\nRequested out of range attribute name: should return NULL\n");
}


function test_getAttributes_FALSE() {

       $which=1;
       if($ip = $this->parser->XML_PullParser_getAttributes('server',$which)) {
           $attr = $this->parser->XML_PullParser_getAttrVal('ip', $ip);   
       }

       $this->assertEquals(FALSE, $ip, "\nNo token provided: should return FALSE\n");
}


function test_getAttributes_FALSE_2() {

       $token = $this->parser->XML_PullParser_getToken();
       $entry = $this->parser->XML_PullParser_getElement("entry");      
       $child = $this->parser->XML_PullParser_getText("server",2,$entry);     
       $attr_array = $this->parser->XML_PullParser_getAttributes("server", 2, $child);
 
       $this->assertEquals(FALSE, $attr_array,
                 "\nString passed to XML_PullParser_getAttributes instead of tokenized array: "
                       . "should return FALSE\n");
}


function tearDown() {
   $this->parser->XML_PullParser_free();
}

}
 
?>

<?php
 require_once '/usr/share/pear/PHPUnit.php';
 $suite  = new PHPUnit_TestSuite('XML_PUllParser_Test_2');

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "<pre>";
 }


 echo "----------------------------------------------------\n";
 echo "\tTESTING \$which METHODS\n";
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
