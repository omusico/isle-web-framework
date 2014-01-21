<?php
/**
 * texit Rendering Class
 * Copyright (C) 2006   Danjer <danjer@doudouke.org>
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
 * @author Danjer <danjer@doudouke.org>
 * @version v0.2
 * @package TeXitrender
 *
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('PLUGIN_TEXIT')) define('PLUGIN_TEXIT',DOKU_PLUGIN.'texit/');
require_once(PLUGIN_TEXIT.'config.php');
require_once(PLUGIN_TEXIT.'latex.php');

class texitrender_plugin_texit {
  // =======================================================================
  // Variable Definitions
  // =======================================================================
  var $_inputs = NULL;  
  var $_texit_conf;
  var $_p_get_count = 0;
  var $_p_get_parsermodes = null;
  var $_Parser = null;
  var $doc_infos; 
  var $texit;
  /**
   * Initializes the class
   *
   * @param $texit_obj : a texit object (defined in config.php)
   */
  //function texitrender_plugin_texit($pageid = NULL) {
  function __construct($texit_obj) {
    $this->texit = $texit_obj;
    $this->_pageid = cleanID($pageid);
    $this->_doku_file = wikiFN($this->_pageid);
    $this->_texit_conf = &$this->texit->conf;
  }

  /**
   * Main public function:
   *
   * @param $id    : the id of the page
   * @param $basefn: the base page filename
   * @param $destfn: the destination (tex) filename
   */
  function process($basefn, $destfn) {
    // an array with several fields set during document analyzing,
    // useful for rendering.
    // doc_infos is document dependant, so we reinitalize it with default values
    // at each call
    // filling with default values:
    $doc_infos = array(
      'usetablefigure'   => 'off',
      'tablerowlength'   => 80,
      'tablemaxrows'     => 30,
      'wrapcodelength'   => 100,
      'biggesttableword' => 15,
      );
    // TODO: recursion doesn't work with that code (I should use p_locale_latex instead)
    $text = file_get_contents($basefn) or die("can't open file $basefn for reading");
    $parsed = $this->p_render_latex_text($text, $info);
    file_put_contents($destfn, $parsed) or die("can't open file $destfn for writing");
  }

  // ========================================================================
  // public functions
  // ========================================================================

  function add_inputs($data) {
    if (is_null($this->_inputs)) {
      $this->_inputs = $data;
    } else {
      $this->_inputs .= $data;
    }
  } 

  function render_inputs() {
    if (is_null($this->_inputs))
      return '';
    return $this->_inputs;
  }

  function add_data($state, $data) {
    $array = preg_split("/\r?\n/", trim($data));
    $this->remove_outfile();
    if (!is_array($this->_data[$state])) {
      $this->_data[$state] = $array;
      return true;
    }
    array_push($this->_data[$state], $array);
    return false;
  }

  // =========================================================================
  // private method
  // =========================================================================

  function generate_tex() {
    $error = 0;
    $begin = new texitConfig('begin');
    $cmd = new texitConfig('command');
    $doc = new texitConfig('document');
    $foot = new texitConfig('footer');
    $begin_doc = $begin->read();
    if ($begin->is_error())
      $error = 1;
    $cmd_doc .= $cmd->read();
    if ($cmd->is_error())
      $error = 1;
    if (!$error) {
      $info_doc .= $this->generate_latex_info();
    }
    $tex_doc[] = $doc->read();
    if ($doc->is_error())
      $error = 1;
    if (!$error) {
      $latex = $this->p_locale_latex();
      foreach ($latex as $part) {
        array_push($tex_doc, $part);
      }      
    }

  }



  function p_get_instructions(&$text){
    // Dokuwiki Get instruction
    // Original parser use
    //    return p_get_instructions($text); 

    // texit Get instruction with low memory usage
    // Use only one parser object and little bit faster
    return $this->p_get_instructions_texit($text); 
  }


/**
 * turns a page into a list of instructions
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
  function p_get_instructions_texit(&$text){
    if (is_null($this->_p_get_parsermodes)) {
      $this->_p_get_parsermodes = p_get_parsermodes();
      //add modes to parser
    }
    if (is_null($this->_Parser)) {
      $this->_Parser = & new Doku_Parser();
    } 
    // TODO: this leaves room for optimization, as the same
    // handler could be used several times instead of being
    // reinstanciated (as is the case now). The problem is
    // that there is no reset() function on handlers and all
    // attempts to do it by hand failes... patch welcome!
    $this->_Parser->Handler = new Doku_Handler();
    if (count($this->_Parser->modes) == 0) {
      foreach($this->_p_get_parsermodes as $mode){
        $this->_Parser->addMode($mode['mode'],$mode['obj']);
      }
    }
    $p = $this->_Parser->parse($text);
    return $p;
  }

  function p_render_latex_text(& $text, & $info){
//     error_log("p_get_instructions[start]:" . $this->_p_get_count);
    $ins = $this->p_get_instructions($text);

    unset($text);
    $parsed = $this->p_render('latex', $ins, $info);
//     error_log("p_get_instructions[end]:" . $this->_p_get_count++);
    $ins = null;
    return $parsed;
  }

  function p_render_latex($id, & $info){
    $info['current_id'] = $id;
    $filename = wikiFN($id);
    if (!file_exists($filename)) {
      msg("$filename: Not exists", -1);
    }
    if (!is_readable($filename)) {
      msg("$filename: Can't read", -1);
    }
    $text = rawWiki($id);
    $parsed = $this->p_render_latex_text($text, $info);
    return $parsed;
  }

  function p_locale_latex($id=NULL){
    $latex = array();
    $do_recurse = 0;
    $do_recurse_file = 0;
    if (is_null($id)) {
      $id = $this->_pageid;
    }
    //fetch parsed locale
    $latex[] = $this->p_render_latex($id, $this->doc_info);
    //    msg("Memory Sub Usage First: ". memory_get_usage(), -1);
    if ($this->_texit_conf['recurse'] == "on"
    || $this->_texit_conf['recurse'] == "appendix"
    || $this->_texit_conf['recurse'] == "chapter") 
      $do_recurse = 1;
    if ($this->_texit_conf['recurse_file'] == "on") 
      $do_recurse_file = 1;
    if ($do_recurse || $do_recurse_file) {
      if ($this->_texit_conf['recurse'] != "chapter")
    $latex[] = "\n\\appendix\n";
      if (is_array($info['dokulinks']) ) {
        $hash = NULL;
        foreach ( $info['dokulinks'] as $link ) {
          if (!isset($hash[$link['id']]) && $link['id'] != $id) {
            if ($do_recurse 
            && ($link['type'] == 'local' || $link['type'] == 'internal')
            && @file_exists(wikiFN($link['id']))) {
              $subinfo = $this->doc_info;
              error_log("render_link " . $link['id']);
              $latex[] = $this->p_render_latex($link['id'], $subinfo);
              error_log("render_end " . $link['id']);
            }
            if ($do_recurse_file && $link['type'] == 'file' 
            && @file_exists($link['id'])) {
              $subinfo = $this->doc_info;
              $subinfo['current_file_id'] = $link['id'];
              $text = '====== ' . $link['name'] . "======\n";
              $text .= "<file>\n";
              $text .= io_readFile($link['id']);
              $text .= "</file>\n";
              $latex[] = $this->p_render_latex_text($text, $subinfo);
            }
            $hash[$link['id']] = 1;
          }
        }
      }
    }
    return $latex;
  }

  function p_render($mode,$instructions, &$info){
    if(is_null($instructions)) return '';
    //    msg("Memory Usage p_render start: ". memory_get_usage(), -1);
    //    require_once DOKU_INC."inc/parser/$mode.php";
    $rclass = "Doku_Renderer_$mode";
    if ( !class_exists($rclass) ) {
      trigger_error("Unable to resolve render class $rclass",E_USER_ERROR);
    }
    $Renderer = & new $rclass(); #FIXME any way to check for class existance?
    $Renderer->smileys = getSmileys();
    $Renderer->entities = getEntities();
    $Renderer->latexentities = $this->_texit_conf['latexentities'];
    $Renderer->acronyms = getAcronyms();
    $Renderer->interwiki = getInterwiki();
    $Renderer->info = $info;

    // Loop through the instructions
    foreach ( $instructions as $instruction ) {
      // Execute the callback against the Renderer
      call_user_func_array(array(&$Renderer, $instruction[0]),$instruction[1]);
    }
    //set info array
    $info = $Renderer->info;
    //    msg("Memory Usage p_render end: ". memory_get_usage(), -1);
    // Return the output
    return $Renderer->doc;
  }

/**
 * Builds a hash from a configfile
 *
 * If $lower is set to true all hash keys are converted to
 * lower case.
 *
 * Modified to be able to use # character.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
  function confToHash($file,$lower=false) {
    $conf = array();
    $lines = @file( $file );
    if ( !$lines ) return $conf;
    
    foreach ( $lines as $line ) {
      $line = trim($line);
      if(empty($line)) continue;
      $line = preg_split('/\s+/',$line,2);
      // Build the associative array
      if($lower){
    $conf[strtolower($line[0])] = $line[1];
      }else{
    $conf[$line[0]] = $line[1];
      }
    }
    return $conf;
  }

  function buildfilelink($ext, $prefix = '') {
    $ret['id'] = $prefix . $this->_pageid . '.' . $ext;
    $ret['file'] = mediaFN($ret['id']);
    $ret['link'] = ml($ret['id']);
    return $ret;
  }
}

?>
