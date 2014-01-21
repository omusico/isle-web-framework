<?php
$doc = 
'<ARTICLE>
 <formalpara role="list"><title><emphasis>Class Utilities</emphasis></title>
   <para>
   <simplelist>
   <member>
        bool XML_PullParser_pushbackToken ()
   </member>
   <member>
       array XML_PullParser_clearPbackStack ()
   </member>

   <member>
        void XML_PullParser_free ()
   </member>
   <member>
        array XML_PullParser_getCurrentElement ()
   </member>
   <member>
        array XML_PullParser_resetCurrentElement (array $cur_el)
   </member>
   <member>
        array XML_PullParser_unsetCurrentElement ()
   </member>
   <member>
       boolean XML_PullParser_isCaseFolded ()
   </member>
   <member>
	string XML_PullParser_setDelimiter (string $delimiter)
   </member>

    </simplelist>
    </para></formalpara>


 <formalpara role="list"><title><emphasis>Class Utilities</emphasis></title>
   <para>
   <simplelist>
   <member>
        bool XML_PullParser_pushbackToken ()
   </member>
   <member>
       array XML_PullParser_clearPbackStack ()
   </member>

   <member>
        void XML_PullParser_free ()
   </member>
   <member>
        array XML_PullParser_getCurrentElement ()
   </member>
   <member>
        array XML_PullParser_resetCurrentElement (array $cur_el)
   </member>
   <member>
        array XML_PullParser_unsetCurrentElement ()
   </member>
   <member>
       boolean XML_PullParser_isCaseFolded ()
   </member>
   <member>
	string XML_PullParser_setDelimiter (string $delimiter)
   </member>

    </simplelist>
    </para></formalpara>

   <formalpara role="list"><title><emphasis>Errors Functions</emphasis></title>
   <para>
   <simplelist>
   <member>
    string XML_PullParser_Errors_errMsg  ()
   </member>
   <member>
     string XML_PullParser_Errors_getUserDefined  (mixed $obj)
   </member>
   <member>
     void XML_PullParser_Errors_INI  ()
   </member>
   <member>
    integer XML_PullParser_Errors_Num  ()
   </member>
   <member>
    XML_PullParser_Errors XML_PullParser_Errors_Ref  ()
   </member>
   <member>
    void  XML_PullParser_Errors_Trace  ()
   </member>
   <member>
     XML_PullParser_Errors XML_PullParser_Errors_userDefined  (string $msg)
   </member>
    </simplelist>
    </para></formalpara>
</ARTICLE>';


$header = <<<HEADER
<!DOCTYPE article PUBLIC "-//Norman Walsh//DTD DocBk XML V3.1.4//EN"
"http://fedora.gemini.ca/XML/docbook-4.3/docbookx.dtd">
<article>
  <title role="A token-based interface to the PHP expat XML library">XML_PullParser</title>
   <articleinfo>
    <subtitle>Index of Functions and Terms</subtitle> 
      <releaseinfo>version 1.0</releaseinfo>
      <author><surname>Turner</surname><firstname>Myron</firstname></author>
   </articleinfo> 
  <formalpara><title></title><para></para></formalpara>
  <index>
   <title></title>


HEADER;


$glossary_terms = array (
"tokenizers" => "tokeniz",
"case folding" => "fold",
"\$tags" => "tags",
"\$child_tags" => "child_tags",
"\$converted token" => "converted_token",
"\$current_element" => "current_element",
'current token' => "current\s+token",
"\$which" => "\$which",
"document order" => "document\s+order",
"sequencing" => "sequenc",
"delimiter" => "delimiter"
);

$gloss_lookup = array();
$glossary = array();

$pattern = "";
foreach($glossary_terms as $term => $pat) {
  $pattern .= $pat . '|';
  $gloss_lookup[$pat] = $term;
}

$pattern = rtrim($pattern,'|');
$pattern = "/($pattern)/i";


$constants = array(
"XML_PullParser_ERROR_BAD_INTERNAL_ARRAY",
"XML_PullParser_ERROR_BAD_PARAM",
"XML_PullParser_ERROR_DEF",
"XML_PullParser_ERROR_MISMATCHED_TAGS",
"XML_PullParser_ERROR_NO_DATA",
"XML_PullParser_ERROR_NO_DEFAULT_TOKEN",
"XML_PullParser_ERROR_NO_TOKEN",
"XML_PullParser_ERROR_SYSTEM",
"XML_PullParser_ERROR_USER_DEFINED",
"XML_PullParser_ERROR_NS_SUPPORT"
);

$constants_array = array();

$constants_pattern = "";
foreach($constants as $c) {
  $constants_pattern .= $c . '|';
}


$constants_pattern = rtrim($constants_pattern,'|');
$constants_pattern = "/($constants_pattern)/i";


require_once "XML_PullParser.inc";


$fn_names = array();
$file_names = array();
$file_names_index = 0;
$xml_outfile = "XML_PullParser_index.xml";

get_array();
file_loop();


$handle = openOutputFile();
writeHeader($handle);



writeText($handle, "<indexdiv><title>Index of Defined Constants</title>\n");
ksort($constants_array);
foreach($constants_array as $const => $val) {
  $val = array_unique($val);
  $citation_str = ""; 
  foreach ($val as $index) {
    $citation_str .= formatSectionCitations ($file_names[$index]);       
  }
  writeEntry($handle,  $const, $citation_str,"varname");
   
}
writeText($handle, "</indexdiv>\n");
writeText($handle, "<formalpara><title></title><para><![CDATA[ <BR /> ]]></para></formalpara>");

writeText($handle, "<indexdiv><title>Method and Function Index</title>\n");
ksort($fn_names);
foreach($fn_names as $fn => $val) {
  $val = array_unique($val);
  $citation_str = ""; 
  foreach ($val as $index) {
    $citation_str .= formatSectionCitations ($file_names[$index]);       
  }
  writeEntry($handle, "XML_PullParser" . $fn, $citation_str,"function");
   
}
writeText($handle, "</indexdiv>\n");
writeText($handle, "<formalpara><title></title><para><![CDATA[ <BR /> ]]></para></formalpara>");


writeText($handle, "\n<indexdiv><title>Index of Variables and Terms</title>\n");
ksort($glossary);
foreach($glossary as $term => $val) {
  $val = array_unique($val);
  $citation_str = ""; 
  foreach ($val as $index) {
    $citation_str .= formatSectionCitations ($file_names[$index]);       
  }
  $citation_str = rtrim($citation_str,',');
  writeEntry($handle, $term, $citation_str,  "varname");
}
writeText($handle,"</indexdiv>");

writeFooter($handle);


// -------------------END MAIN -------------------------


function get_array() {

XML_PullParser_excludeBlanks(true);
XML_PullParser_trimCdata(true);

global $doc, $fn_names;

    $tags = array("simplelist");
    $child_tags = array('member');
    $parser = new XML_PullParser_doc($doc, $tags, $child_tags);
    
    while($token = $parser->XML_PullParser_getToken()) {
         $parser->XML_PullParser_getElement('member');
         $which = 1;
          while($member = $parser->XML_PullParser_getText('member',$which)) {             
              if (preg_match('/XML_PullParser(_\w+)\W/', $member, $matches)) {
                    $fn_names[$matches[1]] = array();
              }
              $which++;  
          }
    }
   $parser->XML_PullParser_free();
}



function file_loop() {
  global $file_names, $file_names_index, $xml_outfile;

    if ($handle = opendir('.')) {
        while (false !== ($file = readdir($handle))) {
            if (!is_dir($file)  && preg_match('/\.xml/', $file)) {            
                if($file == $xml_outfile) {
                     continue;
                }
                $file_names[$file_names_index] = $file;
                //$file_names_index++;
                parse_files($file);   
		$file_names_index++;             
            }
        }
        closedir($handle);
    }

}

function parse_files($file) {

    global $pattern, $constants_pattern;

    XML_PullParser_excludeBlanks(true);
    XML_PullParser_trimCdata(true);

    $tags = array("formalpara","blockquote","simpara");
    $child_tags = array('programlisting',"title","para","simplelist","member");
    $parser = new XML_PullParser($file, $tags, $child_tags);
    XML_PullParser_excludeBlanks(true);
    XML_PullParser_trimCdata(true);

    $token = "";

    while ($token=$parser->XML_PullParser_getToken()) {

        if($parser->XML_PullParser_isTypeOf("formalpara",$token))
        {    
              $title = $parser->XML_PullParser_getElement('title');
              $title = $parser->XML_PullParser_getText($title);
              if($title) {
                 if (preg_match_all('/XML_PullParser(_\w+)/', $title, $matches)) {              
                  index_functions($matches[1]);
                 }
              }

             $para = $parser->XML_PullParser_getElement('para');
             if ($text = $parser->XML_PullParser_getText($para)) {
                 if (preg_match_all($constants_pattern, $text, $matches)) {              
                  index_constants($matches[1]);

                 }
                 elseif (preg_match_all('/XML_PullParser(_\w+)/', $text, $matches)) {              
                  index_functions($matches[1]);
                 }
                 elseif (preg_match_all($pattern, $text, $matches)) {              
                  index_terms($matches[1]);
                 }

             }
        }
        elseif($parser->XML_PullParser_isTypeOf("blockquote",$token))
        {
              $title = $parser->XML_PullParser_getText('title');
              if($title) {                 
                 if (preg_match_all('/XML_PullParser(_\w+)/', $title, $matches)) {
                  index_functions($matches[1]);
                 }
              }          

        }
        elseif($parser->XML_PullParser_isTypeOf("simpara",$token))
        {
          $text = $parser->XML_PullParser_getText($token);
          if (preg_match_all($constants_pattern, $text, $matches)) {              
                  index_constants($matches[1]);

          }

          elseif (preg_match_all('/XML_PullParser(_\w+)/', $text, $matches)) {  
              index_functions($matches[1]);               
          }
        }
    }

   $parser->XML_PullParser_free();
  
}



function index_constants($matches) {
  global $constants_array, $file_names_index;
   foreach($matches as $const) {
     $constants_array[$const][] = $file_names_index;  
   }
}

function index_terms($matches) {
  global $file_names_index, $gloss_lookup, $glossary ;

  foreach($matches as $pat) {
    $pat = strtolower($pat);
    $pat = trim($pat);
    if(preg_match('/current/', $pat)) { 
          $pat = 'current\s+token';
    }
    if(preg_match('/document/', $pat)) { 
          $pat = 'document\s+order';
    }

    $term = $gloss_lookup[$pat];
    $glossary[$term][] = $file_names_index;
  }

}


function index_functions($matches) {
  global $fn_names, $file_names_index;

  foreach($matches as $func) {
     $fn_names[$func][] = $file_names_index;  
  }

}

function openOutputFile() {
  global $xml_outfile;

    if (!$handle = fopen($xml_outfile, 'w')) {
         echo "Cannot open file ($xml_outfile)";
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


function writeFooter($handle) {

global $xml_outfile;

 if (fwrite($handle, "\n</index>\n</article>\n") === FALSE) {
     echo "Cannot write to file ($xml_outfile)";
     exit;
  }
 fclose($handle);
}

function formatSectionCitations($section) {
  return "\t<ulink type = 'index' url = '$section'>$section</ulink>,\n";
}

function writeEntry($handle,$term, $sections, $type) {

$sections = trim($sections,"\n,");

$term = "<$type>$term</$type>";
$entry=<<<ENTRY
<indexentry>
  <primaryie>$term,
  $sections
 </primaryie>
</indexentry>

ENTRY;
   writeText($handle, $entry);
}

function writeText($handle, $text) {
    global $xml_outfile;
    if (fwrite($handle, $text . "\n") === FALSE) {
       echo "Cannot write to file ($xml_outfile)";
       exit;
    }
}


?>
