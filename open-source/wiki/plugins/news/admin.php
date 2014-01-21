<?php
/**
 * Admin panel for news feed plugin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
 */
 if(!defined('DOKU_INC')) die();
require_once DOKU_INC . "lib/plugins/news/scripts/rss.php";
 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_news extends DokuWiki_Admin_Plugin {

    var $output = '';
	var $helper;
	var $pagedata;
	var $prev_deleted = "";
    var $is_prev_deleted = array();
	
	
  	function admin_plugin_news() {
		   $this->helper =& plugin_load('helper', 'news');	          		   
		   $this->pagedata = $this->helper->_readFile(metaFN('newsfeed:pagedata', '.ser'),true);    
	}		
    /**
     * handle user request
     */
    function handle() {
      
      if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

      $this->output = '';
      if (!checkSecurityToken()) return;
      if (!is_array($_REQUEST['cmd'])) return;
      
      // verify valid values
      switch (key($_REQUEST['cmd'])) {
        case 'prune' :  
		   $this->prune(); 
		   break;
		case 'confirm' :  
		   $this->confirm();		  
		   break;
        case 'restore' :  		
		   $this->is_prev_deleted =array();
		   $this->prev_deleted = "";
		   return;
		 case 'generate':
           $this->output=$this->generate();
		   return;
           		   
		 
      }   
	  
          $deleted = array();
		  if(isset($_REQUEST['delete']) && $_REQUEST['delete']) {
			$deletes = $_REQUEST['delete'];		  
			$deleted = array_keys($deletes);	
		  }
		  
		 
		  if($_REQUEST['prev_del']) {  
		     $prev_deleted = $_REQUEST['prev_del'];
			 $prev_deleted = explode(',',$prev_deleted);
			 $prev_deleted = array_merge($prev_deleted, $deleted);
			 $prev_deleted = array_unique($prev_deleted);			 
			 $this->prev_deleted = implode(",", $prev_deleted);
			 $this->is_prev_deleted = $prev_deleted;
		  }
         // $this->output=$this->pagedata;
    }
 
    /**
     * output appropriate html
     */
    function html() {
	
echo <<<SCRIPTTEXT
  <style type="text/css">
   td.right { padding-right: 20px; }
 </style>

  <script type='text/javascript'>
  //<![CDATA[
  
  function newshandler() {
 
      var prev_del = document.getElementById('prev_del');
      var inputs = document.getElementsByTagName('input');
	  var prev = "";
	  // var teststr = prev_del.value;
	  
	  for(var i=0; i<inputs.length;i++) {
	   if(inputs[i].type == 'checkbox') {	 
              if(inputs[i].checked  && inputs[i].name.match(/delete/))  {	
			      prev += inputs[i].value + ',';
				 }
		 }		
	  }	  
	 
	  if(prev_del.value && prev) {
	     prev_del.value += ',' + prev;
	  }
	  else if (prev) prev_del.value = prev;
	  prev_del.value = prev_del.value.replace(/\s*,$/,"");
	  
	  
  }
  function confirm_del() {
       
      if(window.confirm('The deleted feeds will be removed from the current news feed.')) return true;
	  return false;
  }
  //]]>
  </script>
SCRIPTTEXT;

      ptln('<div style="width:90%;margin:auto;"><p>' . $this->getLang('instructions') . '</p></div>');     
      ptln('<form action="'.wl($ID).'" method="post" name="news_data" onsubmit="newshandler(this);">');          
      ptln('  <input type="hidden" name="do"   value="admin" />');
      ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
	  ptln('  <input type="hidden" name="prev_del" id ="prev_del" value="' .$this->prev_deleted. '" />');
      formSecurityToken();

      ptln('  <input type="submit" name="cmd[prune]"  value="'.$this->getLang('btn_prune').'" />');
      ptln('  <input type="submit" name="cmd[restore]"  value="'.$this->getLang('btn_restore').'" />');
	  ptln('  <input type="submit" name="cmd[confirm]" onclick="return confirm_del();" value="'.$this->getLang('btn_confirm').'" />');
	  ptln('  <input type="submit" name="cmd[generate]" value="'.$this->getLang('btn_generate').'" />');	  
	  ptln('<div id="pagedata_news"><br />');
	  $this->table_header();	 		
			foreach($this->pagedata as $md5=>$pageinfo) {
			   $this->pagedata_row($md5,$pageinfo);
			}
	
	  $this->table_footer(); 
	  ptln('</div>'); 
      ptln('</form>');
      
	  if($this->output) {
		  ptln('<p><pre>');	  
		  echo print_r($this->output,true);
		  ptln('</pre></p>');	  	  
     }
	 }
	 
	 function table_header() {
	    ptln('<table cellspacing="8">');
		$theader = $this->theader("Delete") . $this->theader("Page") . $this->theader("GM Time") . 
		            $this->theader("Local Time");
        ptln("<tr>$theader</tr>");		
	 }
	 function table_footer() {
	    ptln('</table>');
	 }
	 
	 function pagedata_row($md5,$info) {
	   static $inx = 0;
	   
	   if(in_array($md5,$this->is_prev_deleted)) return;
	   
	   $type = 'delete';
	   $cb_id =$type . '_' . $inx;
		ptln('<tr>');
				
		$row = '<td align="center">' . "<input type = 'checkbox'  id='$cb_id'  name ='" . $type . "[$md5]' value = '$md5'>" .'</td>';
		$row .= $this->cell($info['id'] ) .  $this->cell($info['gmtime']) .  
		       $this->cell(date('r',$info['time']));
	    ptln($row);		   
		ptln('</td></tr>');
		
		$index++;
	 }
	 function cell($data="") {
	     return "<td class='right'>$data</td>";
	 }
	 function theader($name="") {
	
	    return "<th align='center'>$name</th>";  
	 }
	 
	 function prune() {
	     
		  
		  $deletes = $_REQUEST['delete'];
		  $deleted = array_keys($deletes);		  
		 
		  foreach($deleted as $d) {
			  unset($this->pagedata[$d]);
		  }
		  
	 }
	 
	 function confirm() {

		 $deleted = explode(',',$_REQUEST['prev_del']);
		  foreach($deleted as $d) {
			  unset($this->pagedata[$d]);
		  }
          $this->helper->_writeFile(metaFN('newsfeed:pagedata', '.ser'),$this->pagedata,true);      
		  
  		  foreach($deleted as $d) {
		     $file = metaFN("newsfeed:$d", '.gz');
			 if(file_exists($file)) {
			    @unlink($file);			   
			 }
		  }
        	  
	 }
	 
	 function generate() {	  
		    $create_time = 0;
			global $newsChannelTitle;
            global $newsChannelDescription;	
			$newsChannelTitle=$this->getConf('title');
			$newsChannelDescription=$this->getConf('desc');
			$ttl = $this->getConf('ttl');
	        $xml_file = DOKU_INC . 'news_feed.xml';
            $current_time = time();
			
	 		new externalNewsFeed($xml_file,$ttl);	

			if(@file_exists($xml_file)) {
			     $create_time= filectime($xml_file);	             
	         }
			if($create_time >= $current_time) {
			   return 'Feed generated: ' . date('r',$create_time) ;
			}
			else {
			   return "A new feed may not have been generated.  Check $xml_file.";
			}
	 }
}
