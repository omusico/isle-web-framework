<?php
$version = file('version.xml');
preg_match('/>(.*?)</', $version[0], $matches);

$VERSION = $matches[1];


$HTML_Header = <<<HTMLHEADER
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<Title>XML_PullParser -- Contents</Title>
<style type="text/css">
 body, .block, .simpara { position: relative; font-family: sans-serif;  line-height: 1.25; font-size: 11pt;}
 body { left: 20px; }
.header, .block { width:800px; background-color: #eeeeee; }
.white_block { width:800px; background-color: #ffffff; font-size: 11pt;}
.header { padding: 6px; }
.section_2, .section_1, .para { width:800px; }
.subtitle { font-size: 12pt; line-height: 1.25;}
.title, .subtitle_2 { font-size: 13pt; font-weight: bold; line-height: 2;}
 pre, .token, h4 { position: relative; font-family: serif; left: 20px; font-weight:bold; font-size: 11pt; }
.code { font-weight: bold; font-family: monospace; font-size: 11pt; }
.emphasis { font-weight: bold; }
.code_title { color: #0066cc;  font-family: sans-serif; font-size: 12pt;}
.token, h4 { font-size: 11pt; }
.navigation { font-size: 10pt;; font-weight:bold;}
.super { font-size: 9pt; vertical-align:super; font-weight:bold; }
</style>

</HEAD>
<BODY>
<div class='header'><span class='title'>XML_PullParser</span><br>
<span class="subtitle">A token-based interface to the PHP expat XML library</span><br>
<b>$VERSION</b><br>
<b>Myron Turner</b><br>
<b>Contact: Myron_Turner@shaw.ca</b><br>
<b>Web site: <a href="http://www.mturner.org/XML_PullParser/">http://www.mturner.org/XML_PullParser/</a></b><br><br>
</div>
<p>
<div>
<H2>Contents</H2>

HTMLHEADER;
echo $HTML_Header;
?>

<?php

$header = <<<HEADER
<!DOCTYPE article PUBLIC "-//Norman Walsh//DTD DocBk XML V3.1.4//EN"
"http://fedora.gemini.ca/XML/docbook-4.3/docbookx.dtd">
<article>
  <title role="A token-based interface to the PHP expat XML library">XML_PullParser</title>
   <articleinfo>
    <subtitle>Table of Contents</subtitle> 
      <releaseinfo>version 1.0</releaseinfo>
      <author><surname>Turner</surname><firstname>Myron</firstname></author>
   </articleinfo> 
  <formalpara><title></title><para></para></formalpara>

  <toc>
   <title>XML_PullParser Contents</title>
   <tocpart>

HEADER;

require_once "XML_PullParser.inc";

$files = array();

$file = "synopsis.xml";

$xml_outfile = "XML_PullParser_contents.xml";
$html_outfile = "html_contents.html";

$handle = openOutputFile($xml_outfile);
$html_handle = openOutputFile($html_outfile);

writeHeader($handle);
writeText($html_handle, $HTML_Header);

echo "<A href = \"article2html.php?fn=synopsis.xml\">Synopsis</A><br>\n";
writeEntry($handle, "synopsis.xml","Synopsis");
writeHTMLEntry($html_handle, "synopsis.xml","Synopsis");

while ($file = toc($file)) { }

writeFooter($handle, $xml_outfile);
writeHTMLFooter($html_handle, $html_outfile);

function toc($file) {
global $handle, $html_handle;

    global $files;
    $tags = array("para");
    $child_tags = array("ulink");

     
    $parser = new XML_PullParser($file,$tags,$child_tags);     
    $next_url = "";
    $prev_url = "";
    while($token = $parser->XML_PullParser_getToken())
    { 
            if($link= $parser->XML_PullParser_getElement('ulink')) {
                while($link = $parser->XML_PullParser_nextElement()) {

                    $url_array = $parser->XML_PullParser_getAttributes($link);
                    $url = $parser->XML_PullParser_getAttrVal('url',$url_array);  
                    $link_text = trim($parser->XML_PullParser_getText($link));                   
                    $type = $parser->XML_PullParser_getAttrVal('type',$url_array); 

                    if($type == "next") {                        
                        echo "<A href=\"article2html.php?fn=$url\">$link_text</A><br>\n";
                        writeEntry($handle, "$url",$link_text);
                        writeHTMLEntry($html_handle, "$url",$link_text);
                        $next_url = $url;
                        $files[$next_url]  = true;
                    }
                    if($type == "prev") {
                     //  echo "Prev:  $link_text\n";
                    }
                    
                }
                
            }        
    }



   $parser->XML_PullParser_free();
   return $next_url;

}

function openOutputFile($outfile) {
    if (!$handle = fopen($outfile, 'w')) {
         echo "Cannot open file ($outfile)";
         exit;
    }
    return $handle;
}

function writeHeader($handle) {
global $xml_outfile, $header;
  
 if (fwrite($handle, $header . "\n") === FALSE) {
     echo "Cannot write to file ($xml_outfile)";
     exit;
  }

}

function writeHTMLFooter($handle,$outfile) {

 if (fwrite($handle, "\n</</body>\n") === FALSE) {
     echo "Cannot write to file ($outfile)";
     exit;
  }
 fclose($handle);

}
function writeFooter($handle,$outfile) {


 if (fwrite($handle, "\n</tocpart>\n</toc>\n</article>\n") === FALSE) {
     echo "Cannot write to file ($outfile)";
     exit;
  }
 fclose($handle);
}


function writeHTMLEntry($handle,$url,$link_text) {

   $url = preg_replace('/xml$/', 'html', $url); 
   $entry =  "<A href=\"$url\">$link_text</A><br>";
   writeText($handle, $entry);
}

function writeEntry($handle,$url,$link_text) {
   $entry =  "<tocentry><ulink url=\"$url\">$link_text</ulink></tocentry>";
   writeText($handle, $entry);
}

function writeText($handle, $text) {

    if (fwrite($handle, $text . "\n") === FALSE) {
       echo "Cannot write to file\n";
       exit;
    }
}
?>
</div>
</BODY>
</HTML>



