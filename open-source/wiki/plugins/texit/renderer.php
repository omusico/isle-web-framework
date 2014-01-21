<?php
/**
 * Renderer for Dokutexit output
 * Copyright (C) ???? Harry Fuecks, Andreas Gohr
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
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
//require_once DOKU_INC.'inc/parser/xhtml.php';
//require_once DOKU_INC.'inc/parser/renderer.php';
require_once DOKU_INC.'lib/plugins/texit/latex.php';
require_once DOKU_INC.'lib/plugins/texit/texitrender.php';
/**
 * The Renderer
 */
class renderer_plugin_texit extends Doku_Renderer {
    var $info = array(
        'cache' => false, // may the rendered result cached?
        'toc'   => false, // render the TOC?
    );

    /**
     * the format we produce
     */
    function getFormat(){
        // this should be 'texit' usally, but we inherit from the xhtml renderer
        // and produce XHTML as well, so we can gain magically compatibility
        // by saying we're the 'xhtml' renderer here.
        return 'texit';
    }


    /**
     * Initialize the rendering
     */
    function document_start() {
      global $ID;
      
      $this->id  = $ID;
      if (!isset($this->_texit)) {
	if (!$this->configloaded) { 
	  $this->loadConfig(); 
	}
	$this->_texit = new texitrender_plugin_texit($this->id);
	$info = array();
	if (preg_match("/<texit info>(.*?)<\/texit>/", 
		       str_replace("\n", '\n', rawWiki($this->id)), 
		       $info, PREG_OFFSET_CAPTURE)) {
	  $this->_texit->add_data('info', 
				  str_replace('\n', "\n", $info[0][0]));
	} else {
	  echo "error preg_match";
	}
 	if ($_REQUEST['texit_type'] == 'zip')
 	  $this->_texit->_texit_conf['zipsources'] = true;
	if ($this->_texit->generate('pdf')) {
	  $filename = null;
	  switch ($_REQUEST['texit_type']) {
	  case 'zip':
	    if (is_readable($this->_texit->zip['file'])) {
	      $filename = $this->_texit->zip['file'];
	      header('Content-Type: application/zip');
	    }
	    break;
	  case 'pdf':
	  default:
	    if (is_readable($this->_texit->pdf['file'])) {
	      $filename = $this->_texit->pdf['file'];
	      header('Content-Type: application/pdf');
	    }
	    break;
	  }
	  $hdr = "Content-Disposition: attachment;";
	  $hdr .= "filename=".basename($filename).";";
	  header($hdr);
	  header("Content-Transfer-Encoding: binary");
	  header("Content-Length: ".filesize($filename));
	  readfile("$filename"); 
	  die;
	}
      }
    }
}
