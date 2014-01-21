<?php
global $lib_exe;
$lib_exe = false;
if(strpos(dirname(__FILE__), 'lib/exe')) {
   define("INC_DIR", "../../inc");   
   if(!isset($_SERVER['DOCUMENT_ROOT']) || (isset($_SERVER['DOCUMENT_ROOT']) && ! $_SERVER['DOCUMENT_ROOT'])) {
      define("DOKU_URL", 'http://myexample.com/');
   }
   $lib_exe = true;
}
else {
  define("INC_DIR", "./inc"); 
 }

require_once  INC_DIR . '/init.php';
require_once DOKU_INC . "lib/plugins/news/scripts/rss.php";
global $conf;
global $newsChannelTitle;
global $newsChannelDescription;	
global $newsFeedURL;
$newsFeedURL = "";
$refresh=false;
if(isset($_POST) && isset($_POST['feed']) && $_POST['feed']=='refresh') {
  $refresh = true;
}

$minute = 60;
$default_ttl = 720*$minute;  
$ttl = 0; 
$filetime = 0;
$curent_time = time();
$xml_file = DOKU_INC . 'news_feed.xml';

	if(isset($conf['plugin']['news'])) {
		if(isset($conf['plugin']['news']['ttl'])) {
			$ttl = $conf['plugin']['news']['ttl'];
			if($ttl) $ttl *= $minute;
		}
		if(isset($conf['plugin']['news']['title'])) {
			$newsChannelTitle = $conf['plugin']['news']['title'];
		}
		if(isset($conf['plugin']['news']['desc'])) {
			$newsChannelDescription = $conf['plugin']['news']['desc'];
		}
	 	if(isset($conf['plugin']['news']['url'])) {
			$newsFeedURL = $conf['plugin']['news']['url'];			
		}	
	}
		
	if(!$ttl) $ttl = $default_ttl;
    if(@file_exists($xml_file)) {
	    $filetime = filemtime($xml_file);
	}
	
	$time_elapsed = ($curent_time - $filetime);
	if($time_elapsed >= $ttl || $lib_exe || $refresh) {
		new externalNewsFeed($xml_file,$ttl/$minute);	
		if($lib_exe) {
		    chmod($xml_file, 0666);
		}
	}
	if(!$lib_exe && ! $refresh) {
	  readfile($xml_file);
	  }
	  
	if($refresh) {
			if(@file_exists($xml_file)) {
				$create_time= filectime($xml_file);
				if($create_time >= $current_time) {
				  echo '<span style="font-size: 11pt">';
				   echo 'Feed generated: ' . date('r',$create_time) , '</span><br/>';
				}
				else echo "New Feed may not have been not created<br />";
			}

			$id = $_POST['feed_ref'];
			$ret_url = DOKU_URL . 'doku.php?id=' . $id;
			echo '<br /><a href ="' . $ret_url . '" style="font-size: 12pt;color:#8cacbb">Return to ' . $id . '</a>';
	}


 exit;

?>