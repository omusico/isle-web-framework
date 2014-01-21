<style type="text/css">
 body, .block, .simpara { position: relative; font-family: sans-serif;  line-height: 1.25; font-size: 11pt;}
 body { left: 20px; }
 .simpara { background-color: #eeeeee; }
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
.navigation, .navigation_2 { font-size: 10pt;; font-weight:bold;}
.navigation_2 { color: #999999; }
.super { font-size: 9pt; vertical-align:super; font-weight:bold; }
.classname { font-weight: bold; font-style: italic; }
td.list_phrase { font-size: 10pt; font-weight: normal; }
</style>

<?php
require_once "XML_PullParser.inc";

if(isset($_GET['fn']))
  $file = $_GET['fn'];
else $file = $argv[1];
if (!$file) exit;

$_USE_article2html_hrefs = TRUE;
$anchor = "";
if(preg_match('/#/', $file)) {  // nothing more has to be, the browser goes to the anchor
   list($file,$anchor) = explode('#',$file);
}
XML_PullParser_setReadLength(3);
getHeader($file);
getBody($file);


function getBody($file) {

    $tags = array("formalpara","blockquote","simpara","indexdiv");
    $child_tags = array("classname", "ulink", "link", "indexentry");
    $parser = new XML_PullParser($file, $tags, $child_tags);
    $token = "";

    while($token=$parser->XML_PullParser_getToken()){

        if($parser->XML_PullParser_isTypeOf("formalpara",$token))
        {
            $td_prev="";  $td_next = ""; $link_text = "";
            if($link= $parser->XML_PullParser_getElement('ulink')) {
                while($link = $parser->XML_PullParser_nextElement()) {
                    $link_text = $parser->XML_PullParser_getText($link);                   
                    $url_array = $parser->XML_PullParser_getAttributes($link);
                    $url = $parser->XML_PullParser_getAttrVal('url',$url_array);  
                    $type = $parser->XML_PullParser_getAttrVal('type',$url_array); 

                    if($type == "next") {
                        $td_next = "<TD align='right' class='navigation'><b>Next: </b>";
                        $td_next .= "<a href='article2html.php?fn=$url' class='navigation'>$link_text</a></td>"; 
                    }
                    if($type == "prev") {
                        $td_prev = "<TD align='left' class='navigation'><b>Prev: </b>";
                        $td_prev .= "<a href='article2html.php?fn=$url'>$link_text</a></td>"; 
                    }
                    
                }

                if($td_prev  || $td_next) {
                    echo "<TABLE width='800'><TR>$td_prev" . $td_next;
                          echo "</table>&nbsp;&nbsp;&nbsp;\n";
                    continue;
                }
                
            }


            $parser->XML_PullParser_resetCurrentElement($token);  //current element has to be restored from ulink

            $mark_up = $parser->XML_PullParser_getCSSSpans(array("code"=>"code", "emphasis"=>"emphasis", "classname"=>"classname",
                                                                     "envar"=>"code", "superscript"=>"super"));
          //  $mark_up += $parser->XML_PullParser_getHTMLTags(array("classname"=>"b"));


            $anchor = ""; 
            if($parser->XML_PullParser_isChildOf('anchor',$token)) {
                $anchor_id =  $parser->XML_PullParser_getAttributes('anchor');
                $anchor_id =  $parser->XML_PullParser_getAttrVal('id', $anchor_id);
                //echo "<A Name='$anchor_id'></a>";  
                $anchor =  "<A Name='$anchor_id'>";  
              
            }

            if($parser->XML_PullParser_isChildOf('simplelist',$token)) {
                $list = $parser->XML_PullParser_getChild('simpleList');
                $attr_array_role =  $parser->XML_PullParser_getAttributes('simpleList');

                if($attr_array_role) {
                   $start_num = $parser->XML_PullParser_getAttrVal('role',$attr_array_role);
                }
                if(!$start_num) {
                    $start_num = 1;
                }
                $which = 1;
                $items = "";

                while($member =  $parser->XML_PullParser_getChild('member',$which,$list)) {
                    $member_xcl =  $parser->XML_PullParser_childXCL($member, 'phrase');
                    $member_text =  $parser->XML_PullParser_getTextMarkedUp($mark_up,$member_xcl);
                    if($phrase =  $parser->XML_PullParser_getChild('phrase',1,$member)) {
                        if($phrase_text =  $parser->XML_PullParser_getTextMarkedUp($mark_up,$phrase)) {                            
                            $member_text .= "\n<table width=600><td class='list_phrase'>" . trim($phrase_text) . "\n</table>\n";
                            rewrite_HTML_Link($member_text);
                        }
                    }
                    $items .= "<LI>". trim($member_text) . "\n";
                    $which++; 
                }               
                
                $title = $parser->XML_PullParser_getTextMarkedUp($mark_up, 'title');
                if($title) { 
                    echo "<br>" . $title ."<br>";
                }

               /*
                  text in this para is assumed to be an intro to the list,
                  which means that any text following the list would be concatenated with the
                  text at top of list and printed at top of list   
                */
                 $para = $parser->XML_PullParser_childXCL($token, 'simpleList', 'title');   //remove simplelist
                 if($list_intro_text = trim($parser->XML_PullParser_getTextMarkedUp($mark_up,$para))) {
                     echo  '<TABLE width="800"><TR><TD class="white_block">' .$list_intro_text ."</table>\n";
                 }
                if($anchor) {
                  echo "$anchor <OL TYPE = '1' START = '$start_num'> </a>";
                }
                 else {
                    echo "<OL TYPE = '1' START = '$start_num'>\n"; 
                 }
                 echo $items;
                 echo "</OL>\n"; 
                 continue;
            }


            if($parser->XML_PullParser_isChildOf('token',$token)) {
                $len = strlen($parser->XML_PullParser_getText('token'));
                $len *= 10;
                $len = round((800 - $len)/2); 
                if($len < 10) $len = 20;
                $len = $len ."px";
                $mark_up +=  $parser->XML_PullParser_getStyledTags(array("token"=>"h4"),
                                       array("style"=>"left:$len"));
            }

            echo '<p><div class="para">';
            if($anchor) echo $anchor . "</a>";

           if($parser->XML_PullParser_isChildOf('title',$token)) {
                $title = $parser->XML_PullParser_getTextMarkedUp($mark_up, 'title');
                if($title) { 
                    echo "<br>" . $title ."<br>";
                }
           }
           if($parser->XML_PullParser_isChildOf('para',$token)) {          

            $text = $parser->XML_PullParser_getTextMarkedUp($mark_up, "para");
            rewrite_HTML_Link($text);
/*

            if(preg_match('/href\s*=\s*".*?\.xml/',$text)) {
               if($text = preg_replace('/href\s*="(.*?)xml/','href="article2html.php?fn=\\1xml',$text)) {
                  echo "\n<!--   REPLACEMENT MADE -->\n";   
               }
            }
*/

            $text = preg_replace('/<\s/', '&lt;', $text);
            $text = preg_replace('/\s>/', '&gt;', $text);
            echo $text;
           }
             echo "</p></div>\n\n";
        }

        elseif($parser->XML_PullParser_isTypeOf("blockquote",$token))
        {

            if($parser->XML_PullParser_isChildOf('anchor',$token)) {
                $anchor_id =  $parser->XML_PullParser_getAttributes('anchor');
                $anchor_id =  $parser->XML_PullParser_getAttrVal('id', $anchor_id);
                echo "<A Name='$anchor_id'>";                
            }

            if ($attr =  $parser->XML_PullParser_getAttributes("blockquote"))
            { 
               $role = $parser->XML_PullParser_getAttrVal('role',$attr);
               if($role == 'box' || $role == 'blank_box') 
                {
                   $bgcolor = '#eeeeee';
                   $class = 'block'; 
                   if($role == 'blank_box') {
                        $bgcolor = '#ffffff';
                        $class = 'white_block';
                   }

                   $mark_up = $parser->XML_PullParser_getCSSSpans(array("code"=>"code", "emphasis"=>"emphasis",
                                    "classname"=>"classname"));
                  // $mark_up += $parser->XML_PullParser_getHTMLTags(array("classname"=>"b"));

                   $title = $parser->XML_PullParser_getText("title"); 

                   $list = $parser->XML_PullParser_getChild('simpleList');
                   $which = 1;
                   $items = "";
                     while($member =  $parser->XML_PullParser_getChild('member',$which,$list)) {
                        $member_text =  $parser->XML_PullParser_getTextMarkedUp($mark_up,$member);
                        rewrite_HTML_Link($member_text);
                        $items .= "<tr><td class='$class'>". trim($member_text) . "</td>\n";
                        $which++; 
                   }
                   echo "<table border=1 bgcolor='" . $bgcolor . "' width = 750 cellpadding ='6'>\n"
                              . "<tr><th align='left' class='$class'>$title"
                           .   $items  
                           . "</table>\n";
                   continue;  
               }
            }
            $tab_x_2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $code = false; 
            echo '<p><div class="block">';  
            if($anchor = $parser->XML_PullParser_getChild("anchor")) {
                 $anchor_id = $parser->XML_PullParser_getAttributes($anchor);
                 echo '<A Name="' . $parser->XML_PullParser_getAttrVal('id',$anchor_id) . '"></A>';   
              }
            
            $title = $parser->XML_PullParser_getText("title");    
            if ($attr =  $parser->XML_PullParser_getAttributes("title")) {            
               $role = $parser->XML_PullParser_getAttrVal('role',$attr);  
               if($role && preg_match('/^code$/i',$role)) {
                    $title = '<span class="code_title" style="font-style:italic;">' . $title . "</span>"; 
                    $code = true;                    
               }
            }
            
            $text = $parser->XML_PullParser_getText("programlisting");
            $text = preg_replace('/<\s/', '&lt;', $text);
            $text = preg_replace('/\s>/', '&gt;', $text);
            if(!$code) { 
                $text = preg_replace('/^[\t ]+/ms',"",$text);   // remove leading spaces
            }
                                                         // replace dummy lines of single dot with bullets
            $text = preg_replace('/^\.\s*$/ms',"$tab_x_2&bull;",$text);
            print "<center><b>$title</b></center>\n"; 

            print "<pre>$text</pre></div>\n";           
        }
        elseif ($parser->XML_PullParser_isTypeOf("indexdiv",$token)) {
            XML_PullParser_excludeBlanks(true);
            XML_PullParser_trimCdata(true);
            $title = $parser->XML_PullParser_getText('title'); 
            echo "\n<P><b>$title</b>\n";
            $entries = $parser->XML_PullParser_getElement('indexentry'); 
            $seq = $parser->XML_PullParser_getSequence();
            $close_prevList = "";
            foreach($seq as $index_entry ) {                     
                    list($index_tag, $index_val) = each($index_entry);
                    if ($text = $parser->XML_PullParser_getText($index_tag, $index_val)) {
                    if($url_array = $parser->XML_PullParser_getAttributes("ULINK", $index_val)) {
                         $url = $parser->XML_PullParser_getAttrVal('url', $url_array);     
                    }
                       if (preg_match('/\w/',$text)) {
                            write_index_element($index_tag, $text, $close_prevList, $url);
                            if(!$close_prevList) {
                                $close_prevList = "</DL>\n";
                            }
                       }
                    }
            }
           echo  $close_prevList;
        }
        if ($parser->XML_PullParser_isTypeOf("simpara",$token))          
        {

            if($attr = $parser->XML_PullParser_getAttributes("simpara", 1)) {
                 $role = $parser->XML_PullParser_getAttrVal('role',$attr);                   
                 if($role == "hr") {
                     echo "<hr width='800' align='left'>\n";
                 }

                 continue;   // this will also skip over the xml contents page which has role ="contents"
            }
            $text = $parser->XML_PullParser_getText();
            $mark_up = $parser->XML_PullParser_getCSSSpans(array("code"=>"code", "emphasis"=>"emphasis", "classname"=>"classname"));
         //   $mark_up += $parser->XML_PullParser_getHTMLTags(array("classname"=>"b"));
             
            $text = $parser->XML_PullParser_getTextMarkedUp($mark_up);

             echo "<table width='750' bgcolor='#eeeeee'><td class='block'><pre>$text</pre></table>";

        }
    }
   
    $parser->XML_PullParser_free();
}




function getHeader($file) {

    $tags = array("title","articleinfo");
    $child_tags = array("");

    $parser = new XML_PullParser($file, $tags, $child_tags);
  
    echo "<div class='header'>";
    while($token=$parser->XML_PullParser_getToken())
    {

      if($parser->XML_PullParser_isTypeOf("title",$token)) {
            $title = $parser->XML_PullParser_getText();
            $subtitle = $parser->XML_PullParser_getAttributes("title");
            echo "<span class='title'>$title</span><br>\n";
            if($subtitle) {
                echo '<span class="subtitle">' . $subtitle['ROLE'] ."</span><br>\n";  
            }
       }

      elseif($parser->XML_PullParser_isTypeOf("articleinfo",$token)) {

            $surname = $parser->XML_PullParser_getText("surname");
            $firstname = $parser->XML_PullParser_getText("firstname");
    	    $version = $parser->XML_PullParser_getText("releaseinfo");

    	    $email = $parser->XML_PullParser_getText("email",1);


            $subtitle_2 = $parser->XML_PullParser_getText("subtitle");
            if($version) {
                echo "<b>$version</b><br>\n";
            }
            echo "<b>$firstname $surname</b><br>\n";
            if($email) {
                echo "<b>$email</b><br><br>\n";
            }
            echo "<span class='subtitle_2'>$subtitle_2</span><br>\n";

       break;
      }

    }

    $parser->XML_PullParser_free();
    echo "</div>";

    echo "<p><div class='block'>";
echo 
'<table width=800 cellpadding = 8><tr><td align = "right"><A href="contents.html" class="navigation_2">Contents</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></table>';

    echo "</div>";

}


function rewrite_HTML_Link(&$text) {
global $_USE_article2html_hrefs;
    if (!$_USE_article2html_hrefs) {
           return;
    }

    if(preg_match('/href\s*=\s*".*?\.xml/',$text)) {
      if($text = preg_replace('/href\s*="(.*?)xml/','href="article2html.php?fn=\\1xml',$text)) {
       echo "\n<!--   REPLACEMENT MADE -->\n";   
      }
     }
}

/*  For the Index */
function  write_index_element($index_tag, $text, $close_prevList, $url = "") {

    switch($index_tag) {
        case "FUNCTION":
        case "VARNAME":
           echo $close_prevList;
           echo "<DL><Dt>$text\n";          
           break; 

        case "ULINK":
          echo "<DD><A href='$url'>$text</a>\n";           
    }
}

function reformat_brackets($text) {
   $text = preg_replace('/</','&lt;',$text);

   $text = preg_replace('/&lt;\s/','&lt;',$text);
   $text = preg_replace('/\s>/','>',$text);
   return $text;
}

function hilight($parser, $name, $text, $otag, $ctag) {

            $hilite_names = array(); 
            $replacements = array();
            $patterns = array();
            $hilite_names = $parser->XML_PullParser_getTextArray($name);

            foreach($hilite_names as $h_name) {
                $replacements[] = $otag .$h_name.$ctag;
             }
            foreach($hilite_names as $h_name) {
                $h_name = preg_replace('/\(/', '\(', $h_name);
                $h_name = preg_replace('/\)/', '\)', $h_name);
                $h_name = preg_replace('/\./', '\.', $h_name);
                $patterns[] = '/\W'.$h_name .'\W/';
              
            }
            
            $text = preg_replace($patterns, $replacements, $text); 

            return $text;
}


?>

