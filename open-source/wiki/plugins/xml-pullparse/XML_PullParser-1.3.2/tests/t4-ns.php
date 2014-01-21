<?php

require_once 'PHPUnit/TestCase.php'; 
require_once 'XML_PullParser.inc';

$doc_DNS=<<<XMLDOC
<DNS
  xmlns = "http://example.com/local/"  
  xmlns:dns = "http://example.com/dns.txt/"

>
<ENTRY> 
<ipaddress>172.20.19.6</ipaddress> 
<domain>example.com</domain> 
<server dns:ip="192.168.10.1">example_1.com</server> 
<server dns:ip="192.168.10.2">example_2.com</server> 
<server dns:ip="192.168.10.3">example_3.com</server> 
<alias>www.example.com</alias> 
</ENTRY> 
</DNS>
XMLDOC;

$doc_VEHICLES=<<<VEHICLES
<vehicles
  xmlns = "http://example.com/local/"  
  xmlns:details = "http://example.com/details.txt/"
>
  <vehicle details:year="2004" details:make="Acura" details:model="3.2TL">
    <mileage>13495</mileage>
    <color>green</color>
    <price>33900</price>
    <carfax buyback="no" />
  </vehicle>

  <vehicle details:year="2005" details:make="Acura" details:model="3.2TL">
    <mileage>07541</mileage>
    <color>white</color>
    <price>33900</price>
    <carfax buyback="yes" />
  </vehicle>

  <vehicle details:year="2004" details:make="Acura" details:model="3.2TL">
    <mileage>18753</mileage>
    <color>white</color>
    <price>32900</price>
    <carfax buyback="yes" />
  </vehicle>
</vehicles>
VEHICLES;

class XML_PUllParser_Test_4 extends PHPUnit_TestCase {
var $parser;
var $tags = array('Entry');
var $child_tags = array();


function setUp()    {
   global $doc_DNS;
   XML_PullParser_Errors_INI();
   $this->child_tags = array("ipaddress", "server");
   XML_PullParser_NamespaceSupport(true);
   $this->parser = new XML_PullParser_doc($doc_DNS,$this->tags,$this->child_tags);      
   $this->parser->XML_PullParser_setCurrentNS("http://example.com/local/|http://example.com/dns.txt/");


}


function test_nextElement_FALSE() {
    $this->parser->XML_PullParser_getElement("server");  
    $next = $this->parser->XML_PullParser_nextElement();
    $this->assertEquals(FALSE, $next,"Failed to return FALSE when \$current_element not available\n");
}


function test_nextElement() {
    $this->parser->XML_PullParser_getToken();  
    $this->parser->XML_PullParser_getElement("server");  
    $next = $this->parser->XML_PullParser_nextElement();
    $this->assertEquals('S__SERVER', $next[0], "Failed to return next token: SERVER\n");
}

function test_nextElement_FALSE_2() {
    $this->parser->XML_PullParser_getToken();  
    $this->parser->XML_PullParser_getElement("not_an_element");  
    $next = $this->parser->XML_PullParser_nextElement();
    $this->assertEquals(False, $next, "Failed to return FALSE where no \$current_element is available\n");
}

function test_getAttrValues_ERROR() {
 $this->parser->XML_PullParser_getToken();  
 $servers = $this->parser->XML_PullParser_getElement("server");  
 $attrs = $this->parser->XML_PullParser_getAttrValues(array());

 $this->assertEquals(XML_PullParser_Errors_Num(), XML_PullParser_ERROR_BAD_PARAM, "Expected XML_PullParser_ERROR_BAD_PARAM\n");

}



function test_setAttrLoops_ARRAY_COUNT() {
    $this->vehicles_ini();
    $token = $this->parser->XML_PullParser_getToken();

    $attr_array = $this->parser->XML_PullParser_setAttrLoop_elcd($token);
    $attr_array_2 = $this->parser->XML_PullParser_setAttrLoop($token);
    $this->assertEquals(count($attr_array) , count($attr_array_2), 
                   "XML_PullParser_setAttrLoop_elcd array and XML_PullParser_setAttrLoop array should be same size\n");

}


function test_isChildOf_NULL() {
   $this->vehicles_ini(array("vehicle"), array('color'));

   $token = $this->parser->XML_PullParser_getToken();
   $result = $this->parser->XML_PullParser_isChildOf("color_1");  
  

  $this->assertEquals(NULL, $result, "Null should be returne if not childOf\n");

}

function test_isChildOf() {

  $this->vehicles_ini(array("vehicle"), array('color'));

   $token = $this->parser->XML_PullParser_getToken();
   $result = array();

   $result[] = $this->parser->XML_PullParser_isChildOf("color");
   $result[] = $this->parser->XML_PullParser_isChildOf("color", $token);
   $result[] = $this->parser->XML_PullParser_isChildOf("color", "vehicle");

   $cur_el = $this->parser->XML_PullParser_getElement("color");  
   $result[] = $this->parser->XML_PullParser_isChildOf("color");
   $result[] = $this->parser->XML_PullParser_isChildOf("color", $cur_el);  
   $ar = $this->parser->XML_PullParser_tokenFromChildren  ($result);


   $this->assertEquals(20, sizeof($ar), "Five color elements should yield 5*4 array elements\n");
}

function test_getSequence_COMPARE_ARRAYS() {
    $this->vehicles_ini(array("vehicle"), array('color'));

    while($token = $this->parser->XML_PullParser_getToken()) {
         $current_el = $this->parser->XML_PullParser_getElement("color");  
         $color = $this->parser->XML_PullParser_getText();
         if($color == "white") {      
               $sequence[] = $this->parser->XML_PullParser_getSequence($token); 
         }
    }

    $this->assertEquals(count($sequence[0]), count($sequence[1]), "Sequence arrays should be same size\n");

}


function test_getSequence_ARRAYSIZE() {
    $this->vehicles_ini(array("vehicle"), array('color'));

    while($token = $this->parser->XML_PullParser_getToken()) {
         $current_el = $this->parser->XML_PullParser_getElement("color");  
         $color = $this->parser->XML_PullParser_getText();
         if($color == "green") {      
               $sequence = $this->parser->XML_PullParser_getSequence($token); 
               break; 
         }
    }

    $this->assertEquals(5, count($sequence), "Array size should be 5\n");

}

function test_getElementName_VEHICLES() {
 $this->vehicles_ini(array("vehicle"), array('color'));
 $token = $this->parser->XML_PullParser_getToken();
 $cur_el = $this->parser->XML_PullParser_getElement('color');

 $str = "";
 $str .= $this->parser->XML_PullParser_getElementName($token);
 $str .= '|';
 $str .= $this->parser->XML_PullParser_getElementName($cur_el);

 $this->assertEquals('VEHICLE|COLOR', $str, "\n");
}


function test_getElementName_DNS() {
 
 $token = $this->parser->XML_PullParser_getToken();
 $cur_el = $this->parser->XML_PullParser_getElement('server');

 $str = "";
 $str .= $this->parser->XML_PullParser_getElementName($token);
 $str .= '|';
 $str .= $this->parser->XML_PullParser_getElementName($cur_el);

 $this->assertEquals('ENTRY|SERVER', $str, "\n");


}

function test_getElementName_STRING() {

 $str = "";
 $str .= $this->parser->XML_PullParser_getElementName('S__START');
 $str .= '|';
 $str .= $this->parser->XML_PullParser_getElementName('E__END');

  $this->assertEquals('START|END', $str, "\n");
 
}

function vehicles_ini($tags="",$child_tags="") {

global $doc_VEHICLES;

   $this->child_tags = array();    

    $this->parser->XML_PullParser_free();
    if($tags) {
       $this->tags = $tags;
    }
    else {
        $this->tags =array("vehicles");
    }
    
   if($child_tags) {
        $this->child_tags = $child_tags;
    }


    $this->parser = new XML_PullParser_doc($doc_VEHICLES, $this->tags, $this->child_tags);
   $this->parser->XML_PullParser_setCurrentNS("http://example.com/local/|http://example.com/details.txt/");



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
 echo "\tTESTING TOKEN RETURNING METHODS\n";
 echo "----------------------------------------------------\n";

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "</pre>";
 }


 $suite  = new PHPUnit_TestSuite('XML_PUllParser_Test_4');
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

