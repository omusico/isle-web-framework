<?php
    /**
     *  newfeed plugin  
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)       
     */
     
    // must be run within DokuWiki
    if(!defined('DOKU_INC')) die();
     
    if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
    require_once(DOKU_PLUGIN.'syntax.php');
   

     
    /**
     * All DokuWiki plugins to extend the parser/rendering mechanism
     * need to inherit from this class
     */
    class syntax_plugin_news_button extends DokuWiki_Syntax_Plugin {
        var $helper;  
		
	    function syntax_plugin_news_feed() {
		   //$this->helper =& plugin_load('helper', 'news');
		}		
        /**
         * return some info
         */
        function getInfo(){
            return array(
                'author' => 'Myron Turner',
                'email'  => 'turnermm02@shaw.ca',
                'date'   => '2011-10-18',
                'name'   => 'news Plugin',
                'desc'   => 'creates newsfeed refresh button',
                'url'    => 'http://www.mturner.org',
            );
        }
     
        function getType(){ return 'substition'; }
        
		function getSort(){ return 168; }
        
		function connectTo($mode) {			
		    $this->Lexer->addSpecialPattern('~~NEWS_REFRESH~~',$mode,'plugin_news_button');
		}
     
        /**
         * Handle the match
         */
        function handle($match, $state, $pos, &$handler){
		
	   global $USERINFO;
	   global $ID;
	   global $INFO;
	   
      if(!isset($USERINFO)) return false;      
	  if(isset($INFO['perm']) && $INFO['perm'] < 2) return; 
	  
       $action =  DOKU_URL . 'newsfeed.php';
       $button_name = $this->getLang('btn_generate');
       $button="<form class='button' method='POST' action='$action '>";
       $button .= "<div class='no'><input type='hidden' name='feed' value='refresh' /><input type='hidden' name='feed_ref' value='" . $ID. "' /><input type='submit' value='$button_name' class='button' title='refresh' /></div></form>";     
  
            switch ($state) {
            
			    case DOKU_LEXER_SPECIAL : return array($state, $button);
			  
            }
       
            return false;
        }
     
        /**
         * Create output
         */
        function render($mode, &$renderer, $data) {
		
	    global $USERINFO;
	    global $INFO;
	   
        if(!isset($USERINFO)) return false;        
        if(isset($INFO['perm']) && $INFO['perm'] < 2) return; 
	   
            if($mode == 'xhtml'){
                list($state, $button) = $data;
                switch ($state) {				            
				  case DOKU_LEXER_SPECIAL : 				  
				  $renderer->doc .= $button; 
				  return true;
                }
            }
            return false;
        }
     


}
