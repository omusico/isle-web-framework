<?php
/* This file can go into either the top level Dokuwiki Driectory or <dokuwki>/lib/exe */


require_once DOKU_INC . "lib/plugins/news/scripts/feedData.php";

class externalNewsFeed extends feedData {
var $ttl;
function externalNewsFeed($outfile=null,$ttl=720) {
    $this->ttl = $ttl;
    parent::feedData();
	$handle = null;
	
		if($outfile) {
			$handle = fopen($outfile,'wb');
			if(!flock($handle,LOCK_EX)) {
			   fclose($handle);
			   return;
			}
		}

		if($handle) {
			fwrite($handle,$this->write_header());
			while($this->feed_data()) {	 
			   fwrite($handle,$this->write_item());
			}
			fwrite($handle,$this->footer());
			flock($handle,LOCK_UN);			
			fclose($handle);
		
		}	
		else {
			echo $this->write_header();
			while($this->feed_data()) {	 
				echo $this->write_item();
			}
		   echo $this->footer();
		}	
		
    }
	

	function write_item() { 

		$src_url=$this->news_feed_url();
		$src_title = $this->title();   ;
		$link = $this->url() . '#' . $this->rss_id() ;	
		$title = $this->title();   	
		$date = $this->date();
		$guid = $link;
		$desc = $this->description();
  
return <<<ITEM
<item>

      <title><![CDATA[$title]]></title>
      <link><![CDATA[$link]]></link>
      <description><![CDATA[$desc]]></description>
      <pubDate>$date</pubDate>
      <guid isPermaLink ="true">$guid</guid>
      <source url="$src_url"><![CDATA[$src_title]]></source>
 </item>      
ITEM;

}


	function write_header () {
    global $lib_exe;
    global $newsFeedURL;	
	$date = $this->news_feed_date();
	 if($lib_exe) {
	     $link = $newsFeedURL;
	 }
	else {
	    $link = $this->news_feed_url();
		}
    $title = $this->channel_title();
	$desc = $this->channel_description();    
return <<<HEAD
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
	<title>$title</title>
	<link>$link</link>
	<description>$desc</description>
	<language>en-us</language>
	<pubDate>$date</pubDate>
HEAD;

	}

	function footer() {
	  return "\n</channel>\n</rss>\n";
	}

}


?>