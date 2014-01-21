<?php
/*
 * DokuWiki skype plugin
 * 2011 Zahno Silvan
 * Usage:
 *
 * {{skype>username,function}} 
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the LGNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * LGNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the LGNU Lesser General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_skype extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Zahno Silvan',
            'email'  => 'zaswiki@gmail.com',
            'date'   => '2012-10-22',
            'name'   => 'Skype Plugin',
            'desc'   => 'Skype Button',
            'url'    => 'http://zawiki.dyndns.org/doku.php/tschinz:dw_skype',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 299;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{skype>.*?\}\}',$mode,'plugin_skype');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        switch ($state) {
          case DOKU_LEXER_ENTER :
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
            break;
          case DOKU_LEXER_EXIT :
            break;
          case DOKU_LEXER_SPECIAL :
            return $match;
            break;
        }
        return array();
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            $options['function']  = $this->getConf('function'); // default value
            $options['size']      = $this->getConf('size');
            $options['content']   = $this->getConf('content');
            $options['style']     = $this->getConf('style');

           // strip {{skype> from start
           $data     = substr($data,8);
           // strip }} from end
           $data     = substr($data,0,-2);
      
           //get function and username
           $var1='';
           $var2='';
           list($var1, $var2) = explode(',', $data, 2);
      
           if ($var1 == 'chat' or $var1 == 'add' or $var1 == 'call' or $var1 == 'userinfo' or $var1 == 'voicemail' or $var1 == 'sendfile')
           {
               $options['function'] = $var1;
               $data = $var2;
           }
           elseif ($var2 == 'chat' or $var2 == 'add' or $var2 == 'call' or $var2 == 'userinfo' or $var2 == 'voicemail' or $var2 == 'sendfile')
           {
               $options['function'] = $var2;
               $data = $var1;
           }
           else
           {
               $data = $var1;
           }
      
           if (empty($data))
           {
               $renderer->doc .= 'No skype name given';
               return true;
           }

           $code = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>';
           $code .= '<a href="skype:'.$data.'?'.$options['function'].'">';

           if($options['style'] == 'balloon') {
               $code .= '<img src="http://mystatus.skype.com/balloon/'.$data.'" style="border: none;" width="150" height="60" alt="My status" /></a>';
           }
           elseif($options['content'] == 'icon+text') {
               if($options['size'] == 'big'){
                   $code .= '<img src="http://mystatus.skype.com/bigclassic/'.$data.'" style="border: none;" width="182" height="44" alt="My status" /></a>';
                   }
               else {
                   $code .= '<img src="http://mystatus.skype.com/smallclassic/'.$data.'" style="border: none;" width="114" height="20" alt="My status" /></a>';
               }
           }
           else {
               if($options['size'] == 'big') {
                   $code .= '<img src="http://mystatus.skype.com/mediumicon/'.$data.'" style="border: none;" width="26" height="26" alt="My status" /></a>';
               }
               else {
                   $code .= '<img src="http://mystatus.skype.com/smallicon/'.$data.'" style="border: none;" width="16" height="16" alt="My status" /></a>';
               }
           }
           
           $renderer->doc .= $code;

           return true;
        }
    return false;
    }
}
