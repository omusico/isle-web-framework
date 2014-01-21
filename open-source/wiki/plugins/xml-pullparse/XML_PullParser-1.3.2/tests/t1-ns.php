<?php

require_once 'PHPUnit/TestCase.php'; 
require_once 'XML_PullParser.inc';

$doc=<<<XMLDOC
<DNS>
<ENTRY
  xmlns:dns = "http://example.com/dns.txt/"
  xmlns:dns2 = "http://example.com/dns2.txt/"
> 
<dns2:ipaddress>172.20.19.6</dns2:ipaddress> 
<domain>example.com</domain> 
<server ip="192.168.10.1">example_1.com</server> 
<server ip="192.168.10.2">example_2.com</server> 
<dns:server dns:ip="192.168.10.3">example_3.com</dns:server> 
<alias>www.example.com</alias> 
</ENTRY> 
</DNS>
XMLDOC;

//XML_PullParser_setCurrentNS

class XML_PullParser_Test_1 extends PHPUnit_TestCase {
var $parser;
var $tags = array('Entry');
var $child_tags = array();
var $file = 'DNX.xml';
var $id;

function setUp()    {
   global $doc;
   XML_PullParser_Errors_INI();   
   XML_PullParser_NamespaceSupport(true);
   $this->child_tags = array("ipaddress", "server");
   $this->parser = new XML_PullParser_doc($doc,$this->tags,$this->child_tags);   

   $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns.txt/");
   $this->id = $this->parser;
}

function test_childXCL() {

   $token = $this->parser->XML_PullParser_getToken();
   $excl = $this->parser->XML_PullParser_childXCL($token, "server");
   $servers = $this->parser->XML_PullParser_getChildren('server',$excl);

   $this->assertEquals(array(), $servers, "\nShould not be any server elements in token\n");
}

function test_getTokenSizeOf()    {

   $token = $this->parser->XML_PullParser_getToken();
   $token = $this->parser->XML_PullParser_deleteBlanks($token); 

   $this->assertEquals(27, sizeof($token));
}

function test_getChildren_Servers() {
   $token = $this->parser->XML_PullParser_getToken();
   $servers = $this->parser->XML_PullParser_getChildren('server',$token);
   $this->assertEquals(3, sizeof($servers), "Checking Number of servers found by  XML_PullParser_getChildren in Token\n");
}

function test_getChildren_EmptyArray() {
   $token = $this->parser->XML_PullParser_getToken();
   $servers = $this->parser->XML_PullParser_getChildren('not_an_element',$token);
   $this->assertEquals(array(), $servers, "Should be Empty Array\n");
}


function test_getChildrenFromName_FALSE() {
//   $token = $this->parser->XML_PullParser_getToken();
   $servers = $this->parser->XML_PullParser_getChildrenFromName('server','Entry');
   $this->assertEquals($servers , FALSE, "Checking for FALSE on failure of XML_PullParser_getChildrenFromName\n");
}

function test_getChildrenFromName_Servers() {
   $token = $this->parser->XML_PullParser_getToken();
   $servers = $this->parser->XML_PullParser_getChildrenFromName('server','Entry');
   $this->assertEquals(3, sizeof($servers), "Checking Number of servers found by  XML_PullParser_getChildrenFromName in Token\n");
}


function test_getChildrenFromName_EmptyArray() {
   $token = $this->parser->XML_PullParser_getToken();
   $servers = $this->parser->XML_PullParser_getChildrenFromName('server_3','Entry');
   $this->assertEquals(array(),$servers, "Empty Array expected if children not found\n");
}


function test_getChildren_False() {
   $servers = $this->parser->XML_PullParser_getChildren('server');
   print_r($servers);
   $this->assertEquals(False, $servers);
}

function test_getElement_False()    {
   $ipaddress=$this->parser->XML_PullParser_getElement('ipaddress'); 
   $this->assertEquals($ipaddress, FALSE, "Failed to return FALSE when current element or current token not available\n");
}

function test_getElementSizeOf()    {
   $this->parser->XML_PullParser_getToken();
   $ipaddress=$this->parser->XML_PullParser_getElement('ipaddress'); 
   $this->assertEquals(4, sizeof($ipaddress));
}

function test_getElement_getText() {

   $this->parser->XML_PullParser_getToken();
   $ipaddress=$this->parser->XML_PullParser_getElement('ipaddress');    
   $this->assertEquals(trim($this->parser->XML_PullParser_getText()), "");   
   $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns2.txt/");
   $this->assertEquals($this->parser->XML_PullParser_getText(), '172.20.19.6');   
}


function test_nextElement() {
   $token = $this->parser->XML_PullParser_getToken(); 

   $el = $this->parser->XML_PullParser_getElement('server');

   $next = $this->parser->XML_PullParser_nextElement();
   list($key, $value) = each($next[2]);      

   $this->assertEquals('cdata', $key, "\nSecond array element of \$next should contain array with 'cdata' as its key\n");   
}


function test_nextElement_NULL() {
   $token = $this->parser->XML_PullParser_getToken();

   $this->parser->XML_PullParser_getElement('server');
   while($next = $this->parser->XML_PullParser_nextElement()) { }

  $this->assertEquals(NULL, $next, "\nNext Element should return NULL when its stack is exhausted\n");   
}


function test_noTokenAvailable() {
   while($token = $this->parser->XML_PullParser_getToken()) {
   }

   $token = $this->parser->XML_PullParser_getToken();
   $token = $this->parser->XML_PullParser_getToken();
   $token = $this->parser->XML_PullParser_getToken();


   $this->assertEquals(NULL, $token, "\nNull Should be returned unless  there is a read error\n");   
}


function testIdRef() {
    $this->assertEquals($this->parser, $this->id);
}

function tearDown() {

   $this->parser->XML_PullParser_free();
}

}
 
?>

<?php
 require_once '/usr/share/pear/PHPUnit.php';

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "<pre>";
 }

 echo "----------------------------------------------------\n";
 echo "\tTESTING TOKEN RETURNING METHODS WITH NAMESPACES\n";
 echo "----------------------------------------------------\n";

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "</pre>";
 }


 $suite  = new PHPUnit_TestSuite('XML_PullParser_Test_1');
 $result = PHPUnit::run($suite);

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
    echo "<DIV style = 'font-size: 10pt; font-weight: bold;'>";
    echo $result->toHtml();
   echo "</div>";
 }
  else {
    print $result->toString();
  }


?>

