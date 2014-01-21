<?php

require_once 'PHPUnit/TestCase.php'; 
require_once 'XML_PullParser.inc';

$doc['dns']=<<<DNSDOC
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
DNSDOC;

$doc['movies']=<<<DOCMOVIES
<Movies
 xmlns = "http://example.com/local/" 
>
 <Movie>
    <Title>Gone With The wind</Title>
    <date>1939</date>
    <leading_lady>Vivien Leigh</leading_lady>
    <leading_man>Clark Gable</leading_man>
 </Movie>

  <Movie>
    <Title>How Green Was My Valley</Title>
    <date>1941</date>
    <leading_lady>Maureen O'Hara</leading_lady>
    <leading_man>Walter Pidgeon</leading_man>
 </Movie>

 <Movie>
 <Title>Jurassic Park</Title>
    <date>1993</date>
    <leading_lady>Laura Dern</leading_lady>
    <leading_man>Sam Neil</leading_man>
 </Movie>
</Movies>
DOCMOVIES;

$doc['topsecret']=<<<TOPSECRET
<Confidential_report
  xmlns = "http://example.com/local/"  
>
<item>
The company has a ground-breaking new product called <emphasis>Ground-breaker.</emphasis>
</item>
<topsecret>Its formula is H20</topsecret>
<item>We expect to begin selling it by the end of the year.</item>
</Confidential_report>
TOPSECRET;


class XML_PUllParser_Test_3 extends PHPUnit_TestCase {
var $parser;
var $tags = array('Entry');
var $child_tags = array();

function setUp()    {  
    global $doc;
    $this->child_tags = array("server","ipaddress", "domain");
    XML_PullParser_NamespaceSupport(true);
    $this->parser = new XML_PullParser_doc($doc['dns'],$this->tags,$this->child_tags);   
    $this->parser->XML_PullParser_setCurrentNS("http://example.com/local/|http://example.com/dns.txt/");

}


function test_getTextArray_EMPTY_ARRAY() {
   $this->reInitialize('movies');
   $token = $this->parser->XML_PullParser_getToken(); 
    $text_array = $this->parser->XML_PullParser_getTextArray('title_2');
    $this->assertEquals(array(), $text_array, "\nEmpty array expected when no cdata is found\n");
}

function test_getTextArray_FALSE() {
   $this->reInitialize('movies');
//    $token = $this->parser->XML_PullParser_getToken(); 

    $text_array = $this->parser->XML_PullParser_getTextArray("Title");
    $this->assertEquals(False, $text_array, "\nExpected FALSE: no token supplied for search\n");
}


function test_getTextArray_NUM_ELEMENTS() {
   $this->reInitialize('movies');
    $token = $this->parser->XML_PullParser_getToken(); 

    $text_array = $this->parser->XML_PullParser_getTextArray("Title");
    $this->assertEquals(3, sizeof($text_array));
}

function test_getTextArray_CDATA() {
  $this->reInitialize('movies');
    $token = $this->parser->XML_PullParser_getToken(); 
//print_r($token);
    $text_array = $this->parser->XML_PullParser_getTextArray("Title");
//print_r($text_array);

    $this->assertContains('Jurassic Park', $text_array,
       "\nDid not find title 'Jurassic Park' in Array returned from XML_PullParser_getTextArray.\n");
}


function test_getTextMarkedUp_FALSE_1() {
  $this->reInitialize('topsecret');

  XML_PullParser_trimCdata(true);
  XML_PullParser_excludeBlanks(true);

 // $token = $this->parser->XML_PullParser_getToken();

   $mark_up = $this->parser->XML_PullParser_getCSSSpans(array("topsecret"=>"topsecret"));
   $text = $this->parser->XML_PullParser_getTextMarkedUp($mark_up);

   $this->assertEquals(FALSE, $text,
              "\nFALSE expected when no token is available to XML_PullParser_getTextMarkedUp\n"); 


}


function test_getTextMarkedUp_FALSE_2() {
  $this->reInitialize('topsecret');

  XML_PullParser_trimCdata(true);
  XML_PullParser_excludeBlanks(true);

   $token = $this->parser->XML_PullParser_getToken();
  
   $text = $this->parser->XML_PullParser_getTextMarkedUp(array());

   $this->assertEquals(FALSE, $text,
              "\nFALSE expected when \$markup parameter is not passed to XML_PullParser_getTextMarkedUp\n"); 


}


function test_getTextMarkedUp_CDATA() {
  $this->reInitialize('topsecret');

  XML_PullParser_trimCdata(true);
  XML_PullParser_excludeBlanks(true);

  $token = $this->parser->XML_PullParser_getToken();
  $mark_up = $this->parser->XML_PullParser_getCSSSpans(array("topsecret"=>"topsecret"));
  $mark_up += $this->parser->XML_PullParser_getHTMLTags(array("emphasis"=>"b"));
  $text = $this->parser->XML_PullParser_getTextMarkedUp($mark_up);

  $this->assertRegExp("/class\s*=\s*'topsecret'/", $text,
       "\nDid not find class='topsecret' in string returned from XML_PullParser_getTextMarkedUp.\n");
  $this->assertContains('<b>', $text,
       "\nDid not HTML bold tag in string returned from XML_PullParser_getTextMarkedUp.\n");

}


function test_getTextStripped_NULL() {
  $this->reInitialize('topsecret');

  XML_PullParser_trimCdata(true);
  XML_PullParser_excludeBlanks(true);

  $token = $this->parser->XML_PullParser_getToken();
  $excl = $this->parser->XML_PullParser_childXCL($token);

  $text = $this->parser->XML_PullParser_getTextStripped($excl);
 
  $this->assertEquals(NULL, $text,
         "\n NULL expected when no CDATA is found in array passed to XML_PullParser_getTextStripped\n"); 

}

function test_getTextStripped_FALSE() {
  $this->reInitialize('topsecret');

//  $token = $this->parser->XML_PullParser_getToken();
  $text = $this->parser->XML_PullParser_getTextStripped();

  $this->assertEquals(FALSE, $text,
         "\nFALSE expected when no token is available to XML_PullParser_getTextStripped\n"); 

}

function test_getTextStripped() {
  $this->reInitialize('topsecret');

  XML_PullParser_trimCdata(true);
  XML_PullParser_excludeBlanks(true);

  $token = $this->parser->XML_PullParser_getToken();
  $text = $this->parser->XML_PullParser_getTextStripped();

  $this->assertContains('H20', $text,
         "\nSubstring 'H20' not found in string returned from XML_PullParser_getTextStripped\n"); 

}

function test_getTextStripped_DELIMITER_ARRAY() {
  $this->reInitialize('topsecret');

  XML_PullParser_trimCdata(true);
  XML_PullParser_excludeBlanks(true);

  $token = $this->parser->XML_PullParser_getToken();


  $old_delim = $this->parser->XML_PullParser_setDelimiter(";;");
  $text_array = explode(';;', $this->parser->XML_PullParser_getTextStripped());
 
  $this->parser->XML_PullParser_setDelimiter($old_delim);
  
  $this->assertContains('H20', $text_array[2], 
                "\nSubstring 'H20' not found in delimited string returned from XML_PullParser_getTextStripped\n");

}

function test_getAttrValues_NUMBER() {

   $token = $this->parser->XML_PullParser_getToken();
   $param['server'] = $token;
   $attr_array = $this->parser->XML_PullParser_getAttrValues($param); 
   $this->assertEquals(3, sizeof($attr_array),
       "\nExpecting 3 elements in array returned by XML_PullParser_getAttrValues\n");
}


function test_getAttrValues_FALSE() {

   $token = $this->parser->XML_PullParser_getToken();

   $attr_array = $this->parser->XML_PullParser_getAttrValues(array()); 
   $this->assertEquals(FALSE, $attr_array,
       "\nExpecting FALSE when empty parameter passed into XML_PullParser_getAttrValues\n");
}


function test_getAttrValues_CDATA() {

   $token = $this->parser->XML_PullParser_getToken();
   $param['server'] = $token;
   $attr_array = $this->parser->XML_PullParser_getAttrValues($param); 

   $this->assertContains('192.168.10.1', $attr_array[0],
       "\nExpecting '192.168.10.1' in first element of array returned by XML_PullParser_getAttrValues\n");
}




function test_nextAttr_FALSE() {

   // $this->parser->XML_PullParser_getToken();
   // $servers=$this->parser->XML_PullParser_getElement('server');
    $attr_array = $this->parser->XML_PullParser_setAttrLoop();

    $this->assertEquals(FALSE, $attr_array,
       "\nExpecting FALSE: no tokens to search for XML_PullParser_setAttrLoop\n");
}



/*
  The default namespace will contribute one attribute to the attributes array,
  the '_ns_' attribute
*/
function test_nextAttr_NULL() {

    $token = $this->parser->XML_PullParser_getToken();
    
    $servers=$this->parser->XML_PullParser_getElement('domain');
    $attr_array = $this->parser->XML_PullParser_setAttrLoop();

    $this->assertEquals(1, count($attr_array[0][1]));
    $this->assertEquals(True, array_key_exists('_ns_',$attr_array[0][1]));
}


function test_nextAttr_NUMBER() {

    $this->parser->XML_PullParser_getToken();
    $servers=$this->parser->XML_PullParser_getElement('server');
    $attr_array = $this->parser->XML_PullParser_setAttrLoop();

    $this->assertEquals(3, sizeof($attr_array),
       "\nExpecting 3 elements in array returned by XML_PullParser_setAttrLoop\n");
}


function test_nextAttr_elcd() {

    $this->parser->XML_PullParser_getToken();
    $this->parser->XML_PullParser_getElement('server');
    $this->parser->XML_PullParser_setAttrLoop_elcd();

    $at = $this->parser->XML_PullParser_nextAttr();

    $this->assertEquals($at[0],'SERVER');
    $this->assertEquals($at[2],'example_1.com');
    $this->assertEquals('192.168.10.1',$at[1]['HTTP://EXAMPLE.COM/DNS.TXT/|IP']);
}


function reInitialize($which) {
   global $doc;
   $this->parser->XML_PullParser_free();

   $tags['movies'] = array("Movies");
   $child_tags['movies'] = array();
   $tags['topsecret'] = array("Confidential_report");
   $child_tags['topsecret'] = array();
   XML_PullParser_NamespaceSupport(true);
   $this->parser = new XML_PullParser_doc($doc[$which], $tags[$which],$child_tags[$which]);
   $this->parser->XML_PullParser_setCurrentNS("http://example.com/local/");
}


function tearDown() {

   XML_PullParser_trimCdata(false);
   XML_PullParser_excludeBlanks(false);

   $this->parser->XML_PullParser_free();

}


}
 
?>


<?php
 require_once '/usr/share/pear/PHPUnit.php';
 $suite  = new PHPUnit_TestSuite('XML_PUllParser_Test_3');

 if (isset($_SERVER['REMOTE_ADDR']) || $argc > 1) {
   echo "<pre>";
 }


 echo "----------------------------------------------------\n";
 echo "\tTESTING MISC TEXT AND ATTRIBUTE METHODS\n";
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


