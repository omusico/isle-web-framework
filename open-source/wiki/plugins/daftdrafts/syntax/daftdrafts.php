<?php
/**
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jon Magne Bøe <jonmagneboe@hotmail.com
 * @author i-net software <tools@inetsoftware.de>
 * @author Gerry Weissbach <gweissbach@inetsoftware.de>
 */

if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_daftdrafts_daftdrafts extends DokuWiki_Syntax_Plugin {

	/**
	* Returns information about this plugin
	*/
	function getInfo(){
		return array (
            'author' => 'Jon Magne Bøe',
            'date' => '2011-11-06',
            'name' => 'DaftDrafts (Syntax Component)',
            'desc' => 'Marks pages as drafts, which hides them from unregistered users.',
            'url' => 'http://www.dokuwiki.org/plugin:daftdrafts',
        );
	}

	function getType(){ return 'substition'; }
	function getPType(){ return 'block'; }
	function getSort(){ return 110; }

	/**
	* Connect pattern to lexer
	*/
	function connectTo($mode){
	  if ($mode == 'base') {
		  $this->Lexer->addSpecialPattern('~~' .$this->getLang('code'). '~~',$mode,'plugin_daftdrafts_daftdrafts');
	  }
	}
	
	/**
	* Handle the match
	*/
	function handle($match, $state, $pos, &$handler){
	  return array('daftdrafts');
	}  

	/**
	*  Render output
	*/
	function render($mode, &$renderer, $data) {
		if ($mode == 'xthml') {
			return true;
		} elseif ($mode == 'metadata') {
			$renderer->meta['type'] = 'daftdrafts';
			return true;
		}
		return false;
	}
}
