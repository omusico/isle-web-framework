<?php
/**
 * Renderer for handling question mark in internal links
 * @author Myron Turner <turnermm02@shaw.ca>
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';

class renderer_plugin_qlink extends Doku_Renderer_xhtml {

    function getFormat(){
        return 'xhtml';
    }

    function canRender($format) {
        return ($format=='xhtml');
    }

   function internallink($id, $name = null, $search=null,$returnonly=false,$linktype='content') {

        $name =trim($name);
        if(!$name) {            
            $id=trim($id);
            if(preg_match('/(.*?\?)$/',$id,$matches)) {
               $name = $matches[1];
            }
       }
       parent::internallink($id, $name, $search,$returnonly,$linktype);
   }

}

