<?php
    /**
     *  news item
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Myron Turner <turnermm02@shaw.ca>
     */
     
    // must be run within DokuWiki
    if(!defined('DOKU_INC')) die();
     
    if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
    require_once(DOKU_PLUGIN.'syntax.php');
     
    /**
     * All DokuWiki plugins to extend the parser/rendering mechanism
     * need to inherit from this class
     */
    class syntax_plugin_news_item extends DokuWiki_Syntax_Plugin {
        var $rss_index = 0;
        /**
         * return some info
         */
        function getInfo(){
            return array(
                'author' => 'Myron Turner',
                'email'  => 'turnermm02@shaw.ca',
                'date'   => '2010-03-19',
                'name'   => 'news Plugin',
                'desc'   => 'identifies news item and creates html anchor to item',
                'url'    => 'http://www.mturner.org',
            );
        }
     
        function getType(){ return 'formatting'; }
		function getPType(){ return 'stack';}
	    function accepts($mode) {
			if ($mode == substr(get_class($this), 7)) return true;
			return parent::accepts($mode);
		}
		
        function getAllowedTypes() { return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs'); }
        
		function getSort(){ return 168; }
        
		function connectTo($mode) {
			$this->Lexer->addEntryPattern('<news.*?>(?=.*?</news>)',$mode,'plugin_news_item'); 
			}
			
        function postConnect() { $this->Lexer->addExitPattern('</news>','plugin_news_item'); }
     
     
        /**
         * Handle the match
         */
        function handle($match, $state, $pos, &$handler){
        
        
            switch ($state) {
                case DOKU_LEXER_ENTER : return array($state, "");   
			  			 
				case DOKU_LEXER_UNMATCHED:
				/*  From Wrap plugin
				/* @author: Anika Henke <anika@selfthinker.org> 
				*/ 
					// check if $match is a == header ==
					$headerMatch = preg_grep('/([ \t]*={2,}[^\n]+={2,}[ \t]*(?=))/msSi', array($match));
					if (empty($headerMatch)) {
						$handler->_addCall('cdata', array($match), $pos);
					} else {
						// if it's a == header ==, use the core header() renderer
						// (copied from core header() in inc/parser/handler.php)
						$title = trim($match);
						$level = 7 - strspn($title,'=');
						if($level < 1) $level = 1;
						$title = trim($title,'=');
						$title = trim($title);

						$handler->_addCall('header',array($title,$level,$pos), $pos);
					}
					return false;
			  

                case DOKU_LEXER_EXIT :       return array($state, '');
			    case DOKU_LEXER_SPECIAL :       return array($state, '');
			  
            }
       
            return false;
        }
     
        /**
         * Create output
         */
        function render($mode, &$renderer, $data) {
		if (empty($data)) return false;
            if($mode == 'xhtml'){
                list($state, $match) = $data;
                switch ($state) {
				
                  case DOKU_LEXER_ENTER : 
				    $this->rss_index++;
				    $renderer->doc .= "<a name='rss_" . $this->rss_index . "'>&nbsp;</a>";
                   	break;				 
                  case DOKU_LEXER_EXIT :
				  case DOKU_LEXER_SPECIAL :  
   				    $renderer->doc .= ""; break;  ;
                }
                return true;
            }
            return false;
        }
     


}
