<?php
/**
 *  Plugin Serif:  sets text in serif with optional size in points
 *
 * Syntax: %%text%%, %<n>%text%%
 *    First form will set text in serif; second form will set text in serif at <n> point size
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_serif extends DokuWiki_Syntax_Plugin {


    function getInfo(){
        return array(
            'author' => 'Myron Turner',
            'email'  => '',
            'date'   => '2012-08-19',
            'name'   => 'Serif Plugin',
            'desc'   => 'Set text to Serif with optional size in points',
            'url'    => '',
        );
    }


    function getType(){
        return 'substition';
    }


    function getSort(){
        return  200;
    }


    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('\$\d+\$.*?\$\$',$mode,'plugin_serif');
      $this->Lexer->addSpecialPattern('\$\$.*?\$\$',$mode,'plugin_serif');

    }


    function handle($match, $state, $pos, &$handler){

          if(preg_match('/\$(\d+)\$(.*?)\$\$/', $match,$matches)) {
               return array($state,array($matches[1],$matches[2]));
          }
          $match = str_replace('$',"",$match);
          return array($state,$match);
    }


    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            if(is_array($data[1])) {
               list($size,$text) = $data[1];
               $renderer->doc .= '<span style="font-size: ' .$size . 'pt; font-family: \'Times New Roman\',Georgia,Serif">'. $text .'</span>';
            }
            else $renderer->doc .= '<span style="font-size: 110%; font-family: \'Times New Roman\',Georgia,Serif">'. $data[1] .'</span>';
            return true;
        }
        return false;
    }
}
