<?php

$topsecret = '
<Confidential_report>
<item>
The company has a ground-breaking new product called <emphasis>Ground-breaker.</emphasis>
</item>
<topsecret>Its formula is H20</topsecret>
<item>We expect to begin selling it by the end of the year.</item>
</Confidential_report>';


require_once "XML_PullParser.inc";

$tags = array("Confidential_report");
$child_tags = array();
XML_PullParser_trimCdata(true);
XML_PullParser_excludeBlanks(true);

$parser = new XML_PullParser_doc($topsecret, $tags, $child_tags);
$token = $parser->XML_PullParser_getToken();
$classified = $parser->XML_PullParser_childXCL($token, "topsecret");

$old_delim = $parser->XML_PullParser_setDelimiter("\n");
echo $parser->XML_PullParser_getTextStripped($classified) . "\n";
$parser->XML_PullParser_setDelimiter($old_delim);


/* Result
        The company has a ground-breaking new product called
        Ground-breaker.
        We expect to begin selling it by the end of the year.
*/
?>
