<?php
/**
 * TeXit-Plugin: Parses TeXit-blocks in xhtml mode
 * Copyright (C) 2007 Danjer
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 *
 * @author     Danjer <danjer@doudouke.org>
 * @date       2007-02-11
 */
 

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_PLUGIN.'texit/texitrender.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_texit extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    public function getType(){
      return 'protected';
    }

    /**
     * @return string Paragraph type
     */
//    public function getPType() {
//        return 'normal'; // let's keep the default value
//    }

    /**
     * Where to sort in?
     */
    public function getSort(){
      return 100;
    }

  /**
   * Connect pattern to lexer
   */
  function connectTo($mode) {
    $this->Lexer->addEntryPattern('<texit(?=.*\x3C/texit\x3E)', $mode,
				  'plugin_texit');
  }

  function postConnect() {
    $this->Lexer->addExitPattern('</texit>','plugin_texit');
  }

  /**
   * Handle the match
   */
  function handle($match, $state, $pos, &$handler){
    //print_r(array('match' => $match, 'state' => $state, "pos" => $pos, "handler" => $handler));
    //print "<br>";
    if ($state == DOKU_LEXER_UNMATCHED) {
      
      $matches = preg_split('/>/u',$match,2);
      $matches[0] = trim($matches[0]);
      if ( trim($matches[0]) == '' ) {
	$matches[0] = NULL;
      }
      return array($state,$matches[0], $matches[1],$pos);
    }
    
    return array($state,'',$match,$pos);
  }

  /**
   * Create output
   */
  function render($mode, &$renderer, $data) {
    global $ID;
    list($state, $substate, $match, $pos) = $data;
    if (!isset($this->_texit)) {
      if (!$this->configloaded) { 
	$this->loadConfig(); 
      }
      $this->_texit = new texitrender_plugin_texit($ID);
    }
    if($mode == 'xhtml'){
      $renderer->info['cache'] = $this->_texit->docache();
      if ($state == DOKU_LEXER_EXIT) {
	return TRUE;
      }
      if ($state != DOKU_LEXER_UNMATCHED) {
	return FALSE;
      }
      switch ($substate) {
      case 'info':
	if ($this->_texit->add_data($substate, $match)) {
	  $renderer->doc .= $this->_texit->render() . '<p>';
	}
	break;
      case 'footer':
      case 'begin':
      case 'document':
      case 'command':
      default:
	break;
      }
      return TRUE;
    }
    if($mode == 'latex'){
      if ($state == DOKU_LEXER_EXIT) {
	return TRUE;
      }
      if ($state != DOKU_LEXER_UNMATCHED) {
	return FALSE;
      }
      if (!isset($substate)) {
	$renderer->put($match);
      }
      return TRUE;
    }
    return FALSE;
  } 
}
?>
