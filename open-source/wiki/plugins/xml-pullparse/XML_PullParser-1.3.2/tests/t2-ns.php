<?php

require_once 'PHPUnit/TestCase.php'; 
require_once 'XML_PullParser.inc';

$doc=<<<XMLDOC
<DNS
  xmlns = "http://example.com/local/" 
  xmlns:dns = "http://example.com/dns.txt/"
  xmlns:dns2 = "http://example.com/dns2.txt/"

>
<ENTRY> 
<dns2:ipaddress>172.20.19.6</dns2:ipaddress> 
<dns:domain>example.com</dns:domain> 
<dns:server dns:ip="192.168.10.1">example_1.com</dns:server> 
<server dns2:ip="192.168.10.2">example_2.com</server> 
<dns:server dns:ip="192.168.10.3">example_3.com</dns:server> 
<alias>www.example.com</alias> 
</ENTRY> 
</DNS>
XMLDOC;



class XML_PullParser_Test_1 extends PHPUnit_TestCase {
var $parser;
var $tags = array('Entry');
var $child_tags = array();

function setUp()    {
   global $doc;
   XML_PullParser_Errors_INI();   
   XML_PullParser_NamespaceSupport(true);
   $this->child_tags = array("ipaddress", "server");
   $this->parser = new XML_PullParser_doc($doc,$this->tags,$this->child_tags);   

   $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns.txt/|http://example.com/local/|http://example.com/dns2.txt/");  
  
}

function test_ATTRIBUTES() {
   $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns.txt/|http://example.com/local/|http://example.com/dns2.txt/");
   $token = $this->parser->XML_PullParser_getToken();

   $result_array = array();
   $elements = array('ipaddress', 'domain', 'server', 'alias');
   foreach($elements as $el) {    
                            // get numerically indexed array of attribute arrays for each named element in $token
       $attr_vals = $this->parser->XML_PullParser_getAttrValues(array($el=>$token)); 
       $cnt = count($attr_vals);
       if($cnt > 1) {  // if there is an attribute other than the one for _ns_ there will be > 1 attributes
           for($i=0; $i<$cnt; $i++) {
              foreach($attr_vals[$i] as $name=>$value) {  // foreach element's attribute array as name=>value
                            // get unqualified attribute name if name is prefixed by namespace
                   if($attr_name = $this->parser->XML_PullParser_getNS_AttrName($name)) {                      
                       $result_array[] = array($attr_name => $value);
                  }
              }
           }
       }
   }


   $this->assertEquals(count($result_array), 3);
   $this->assertEquals(array_key_exists('IP', $result_array[0]), TRUE);
   $this->assertEquals(in_array ('192.168.10.3', $result_array[2]), TRUE);
   

}

function test_getNS_URI() {

   $token = $this->parser->XML_PullParser_getToken();
   $ipaddress =  $this->parser->XML_PullParser_getAttributes('ipaddress');

   $URI_IP = $this->parser->XML_PullParser_getNS_URI($ipaddress);
   
   $domain =  $this->parser->XML_PullParser_getAttributes('domain');
   $URI_domain = $this->parser->XML_PullParser_getNS_URI($domain); 

   $this->assertEquals($URI_domain, 'HTTP://EXAMPLE.COM/DNS.TXT/');
   $this->assertEquals($URI_IP, 'HTTP://EXAMPLE.COM/DNS2.TXT/');

}

/*
   $alias_1 is found because it is in default namespace
   $alias_1 is found because namespaces have been disabled
   They are both the same element, hence $alias_1 == $alias_2
*/
function test_unsetCurrentNS() {

   $token = $this->parser->XML_PullParser_getToken();
   $alias_1 = $this->parser->XML_PullParser_getText('alias');
  

   $this->parser->XML_PullParser_unsetCurrentNS();
   $alias_2 = $this->parser->XML_PullParser_getText('alias');

  
   $this->assertEquals($alias_1, $alias_2);  

}

function test_setNamespaceSupport() {
   echo "This test should print an error messages.  It is part of the Test\n";
   $this->parser->XML_PullParser_unsetCurrentNS();
   XML_PullParser_NamespaceSupport(false);
   $retv = $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns.txt/");    

   if($retv === False) {
        echo "Error check successful\n";
           
   }

     echo "\n";
     $this->assertEquals($retv, FALSE);  

}


/*
<server dns2:ip="192.168.10.2">example_2.com</server> 
<dns:server dns:ip="192.168.10.3">example_3.com</dns:server> 
http://example.com/local/|http://example.com/dns2.txt/
*/

function test_getTextArrayNS() {
   $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns.txt/");

   $token = $this->parser->XML_PullParser_getToken();
   $this->parser->XML_PullParser_getElement('server'); 
   $dns_server =  $this->parser->XML_PullParser_getTextArray('server');

   $this->assertEquals(2, count($dns_server));  
   $this->assertEquals('example_1.com', $dns_server[0]);  
   $this->assertEquals('example_3.com', $dns_server[1]);  

}

function test_setCurrentNS_RETURNVAL() {
   $prev = $this->parser->XML_PullParser_setCurrentNS("http://example.com/dns.txt/");
   $token = $this->parser->XML_PullParser_getToken();
   $prev_2 = $this->parser->XML_PullParser_setCurrentNS($prev);

   $this->assertEquals('HTTP://EXAMPLE.COM/DNS.TXT/|HTTP://EXAMPLE.COM/LOCAL/|HTTP://EXAMPLE.COM/DNS2.TXT/', $prev);  
   $this->assertEquals('HTTP://EXAMPLE.COM/DNS.TXT/', $prev_2);  
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
 echo "\tTESTING NAMESPACES\n";
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

