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
    class syntax_plugin_news_feed extends DokuWiki_Syntax_Plugin {
        var $helper;  
		
	    function syntax_plugin_news_feed() {
		   $this->helper =& plugin_load('helper', 'news');
		}		
        /**
         * return some info
         */
        function getInfo(){
            return array(
                'author' => 'Myron Turner',
                'email'  => 'turnermm02@shaw.ca',
                'date'   => '2010-03-19',
                'name'   => 'news Plugin',
                'desc'   => 'identifies newsfeed page',
                'url'    => 'http://www.mturner.org',
            );
        }
     
        function getType(){ return 'substition'; }
        
		function getSort(){ return 168; }
        
		function connectTo($mode) {			
		    $this->Lexer->addSpecialPattern('~~NEWSFEED.*?~~',$mode,'plugin_news_feed');
		}
     
        /**
         * Handle the match
         */
        function handle($match, $state, $pos, &$handler){
        
            $match=substr($match,11,-2);
			if($match) {
			   $match = trim($match);
			}			
            switch ($state) {
            
			    case DOKU_LEXER_SPECIAL : return array($state, $match);
			  
            }
       
            return false;
        }
     
        /**
         * Create output
         */
        function render($mode, &$renderer, $data) {
		if (empty($data)) return false;
		    global $ID;
            if($mode == 'xhtml'){
                list($state, $match) = $data;
                switch ($state) {				            
				  case DOKU_LEXER_SPECIAL : 				  
				  $this->helper->setUpdate($match);
				  $metafile = metaFN('newsfeed:timestamp', '.meta');
				  io_saveFile($metafile,time());				 
				  $renderer->doc .= ""; 
				  
				  break;  ;
				
                }
                return true;
            }
            return false;
        }
     


}
