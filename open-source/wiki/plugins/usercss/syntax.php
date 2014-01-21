<?php
/**
 * insert information about a template configuration (style.ini)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Randolf Rotta <rrotta@informatik.tu-cottbus.de>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_usercss extends DokuWiki_Syntax_Plugin {
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Randolf Rotta',
            'email'  => 'RRotta@Informatik.TU-Cottbus.DE',
            'date'   => '2009-04-05',
            'name'   => 'output template information',
            'desc'   => 'insert information about a template (style.ini)',
            'url'    => 'http://www.dokuwiki.org/plugin:usercss',
        );
    }

    // where we might be placed
    function getType() {
      return 'substition'; 
    }

    // divs can contain paragraphs and divs
    function getPType() {
      return 'block'; 
    }

    // what we may contain
    function getAllowedTypes() {
      return array();
    }

    function getSort(){
      return 303;
    }

    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('{{tplcssinfo>.+?}}',
				      $mode, 'plugin_usercss');
    }

    function handle($match, $state, $pos, &$handler) {
      // break the pattern up into its constituent parts 
      $match = substr($match, 2, -2); // strip markup
      list($include, $tplname) = explode('>', $match, 2); 
      if (!preg_match('/^[\w\d_]+$/', $tplname)) $tplname="default";
      return array($include, $tplname); 
    }      

    function render($mode, &$renderer, $data) {
      if($mode == 'xhtml'){
	list($inc,$tplname) = $data;
	$file = DOKU_INC.'lib/tpl/'.$tplname.'/style.ini';
	$renderer->code(io_readFile($file), 'ini');
	return true;
      }
      return false;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
