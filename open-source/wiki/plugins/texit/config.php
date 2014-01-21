<?php
/**
 * texit multifunction Class
 * Copyright (C) 2013   Elie Roux <elie.roux@telecom-bretagne.eu>
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
 */
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('PLUGIN_TEXIT')) define('PLUGIN_TEXIT',DOKU_PLUGIN.'texit/');
if(!defined('PLUGIN_TEXIT_CONF')) define('PLUGIN_TEXIT_CONF',PLUGIN_TEXIT.'conf/');
require_once(PLUGIN_TEXIT.'texitrender.php');

class config_plugin_texit {
  var $id;
  var $ns;
  var $namespace_mode;
  var $nsbpc;
  var $conf;
  var $mediadir;
  var $texitdir;
  var $prfix;
  var $all_files;
  var $texit_render_obj; // not initialized by constructor, done only if needed
  var $bibfn;
 /*
  * I didn't use a helper plugin because I needed a constructor.
  * This basically sets up the environment by computing base the filenames, etc.
  *
  */
  function __construct($id, $namespace_mode, $conf, $nsbpc_obj) {
    $this->id = cleanID($id);
    $this->ns = getNS(cleanID($id));
    $this->namespace_mode = $namespace_mode;
    $this->nsbpc = $nsbpc_obj;
    $this->conf = $conf;
    $this->set_prefix();
    $this->_set_texit_dir();
    $this->_set_media_dir();
    $this->bibfn = $this->generate_bib();
    $this->get_all_files();
    $this->conf['latexentities'] = false; // we generate it at compile time
    $this->texit_render_obj = false;
  }
 /*
  * This function sets $this->latexentities to an array where keys are
  * the initial characters and values are the characters escaped in 
  * LaTeX (ex: _ => \_). It gets in by calling conftohash on conf/entities.cfg
  * in the plugin's directory.
  */
  function get_entities() {
    $basefn = PLUGIN_TEXIT_CONF.'entities.cfg';
    return $this->confToHash($basefn);
  }

  /**
  * Builds a hash from a configfile
  *
  * If $lower is set to true all hash keys are converted to
  * lower case.
  *
  * This is a modified version of Dokuwiki's function
  * that doesn't consider # as a comment character (we
  * need it for LaTeX entities).
  */
  function confToHash($file) {
    $conf = array();
    $lines = @file( $file );
    if ( !$lines ) return false;
    foreach ( $lines as $line ) {
      $line = trim($line);
      if(empty($line)) continue;
      $line = preg_split('/[\s\t]+/',$line,2);
      // Build the associative array
      $conf[$line[0]] = $line[1];
    }
    return $conf;
  }

 /*
  * This function (eventually) generates the file texit.bib, in the directory
  * of the namespace pointed by the "reference-db-enable" configuration option
  * of the refnotes plugin.
  *
  * To do so, it merges:
  *   - the BibTeX parts of all pages in the refnotes's database namespace
  *       ("refnotes" by default)
  *   - the conf/bibliography.bib in the texit plugin directory
  */
  function generate_bib() {
    global $conf;
    $bibtext = ''; // we merge all the files in this string
    $basefn = PLUGIN_TEXIT_CONF.'bibliography.bib';
    if (!is_callable("refnotes_configuration::getSetting")) {
      // case where refnotes isn't available. In this case the
      // file to include is just $basefn.
      return $basefn;  
    }
    // code coming from refnotes' syntax.php
    $refnotes_nsdir = refnotes_configuration::getSetting('reference-db-namespace');
    $refnotes_nsdir = str_replace(':', '/', $refnotes_nsdir);
    $refnotes_nsdir = trim($refnotes_nsdir, '/ ');
    $destfn = $conf['datadir'].'/'.$refnotes_nsdir.'/texit.bib';
    $all_refnotes_pages = Array();
    $opts = array('listdirs'  => false,
      'listfiles' => true,
      'pagesonly' => true,
      'skipacl'   => false, // to check for read right
      'sneakyacl' => true,
      'showhidden'=> false,
      );
    // we cannot use $opts in search_list or in search_namespaces, see
    // https://bugs.dokuwiki.org/index.php?do=details&task_id=2858
    search($all_refnotes_pages,$conf['datadir'],'search_universal',$opts,$refnotes_nsdir);
    // now all_refnotes_pages contains all the configuration pages of refnotes, that
    // we'll have to merge...
    // First step here is to see if we need to recompile anything:
    if (is_readable($destfn)) {
      // if the file is readable, then it might be up-to-date?
      $needsupdate = false;
      if (is_readable($basefn)) {
        $needsupdate = $this->_needs_update($basefn, $destfn);
      }
      foreach ($all_refnotes_pages as $page) {
        // A problem here: if the refnote page doesn't contain
        // any bibtex code, the update will take place anyway,
        // but it doesn't sound critical.
        if ($this->_needs_update(wikiFN($page['id']), $destfn)) {
          $needsupdate = true;
        }
      }
      // if the file doesn't need update, we just return.
      if (!$needsupdate) {
        return $destfn;
      }
    }
    if (is_readable($basefn)) {
      $bibtext = file_get_contents($basefn);
    }
    foreach($all_refnotes_pages as $page) {
      $fn = wikiFN($page['id']);
      $bibtext .= $this->parse_refnotes_page($fn);
    }
    if (empty($bibtext)) {
      return false;
    }
    file_put_contents($destfn, $bibtext);
    // we return the filename where the bibliography is saved
    return $destfn;
  }

  function parse_refnotes_page ($fn) {
    $filestr = file_get_contents($fn);
    preg_match_all('#(?<=<code bibtex>)(((?!</code>).)*)(?=</code>)#ms', $filestr, $matches);
    $return = '';
    foreach($matches[0] as $match) {
      $return .= $match."\n";
    }
    return $return;
  }

  function set_prefix() {
    if (!$this->conf['use_prefix']) {
      $this->prefix = '';
      return;
    } else {
      if (!empty($this->conf['pre_prefix'])) {
        $this->prefix = $this->conf['pre_prefix'].":";
      }
      $this->prefix .= $this->ns;
      if ($this->conf['prefix_separator']) {
        $this->prefix = str_replace(':', $this->conf['prefix_separator'], $this->prefix);
        $this->prefix .= $this->conf['prefix_separator'];
      } // else we keep it this way
    }
  }

  function _create_dir($path) {
    global $conf;
    $res = init_path($path);
    if(empty($res)) {
      // let's create it, recursively
      $res = io_mkdir_p($path);
      //$res = mkdir($path, $conf['dmode'], true);
      if(!$res){
        die("Unable to create directory $path, please create it.");
      }
    }
  }

// This function escapes a filename so that it doesn't contain _ character:
  function _escape_fn($fn) {
    $bn = basename($fn);
    $bn = str_replace('_', '-', $bn);
    $dn = dirname($fn);
    if ($dn == ".") {
      return $bn;
    }
    return dirname($fn).'/'.$bn;
  }

  function _set_media_dir() {
    global $conf;
    $path = $conf['mediadir'];
    $path .= '/'.str_replace(':','/',$this->ns);
    // taken from init_paths in inc/init.php
    $this->_create_dir($path);
    $this->mediadir = $path;
  }
  
  function _set_texit_dir() {
    global $conf;
    $path = $this->conf['texitdir'];
    // taken from init_paths in inc/init.php
    $path = empty($path) ? $conf['datadir'].'/../texit' : $path;
    $path .= '/'.str_replace(':','/',$this->ns);
    $this->_create_dir($path);
    $path = realpath($path);
    $this->texitdir = $path;
  }
  
  function get_zip_fn() {
    return $this->mediadir.'/'.$this->get_common_basename().".zip";
  }
  
  function get_base_bib_fn() {
    return $this->bibfn;
  }
  
  function get_dest_bib_fn() {
    // we always call it texit.bib for practical reasons, this may
    // change in the future
    return $this->texitdir.'/'.'texit.bib';
  }
  
  function get_pdf_media_fn() {
    return $this->mediadir.'/'.$this->prefix.$this->get_common_basename().".pdf";
  }

  function get_pdf_media_id() {
    return $this->ns.':'.$this->prefix.$this->get_common_basename().".pdf";
  }

  function get_pdf_texit_fn() {
    return $this->texitdir.'/'.$this->get_common_basename().".pdf";
  }



 /* This returns 'all' if in namespace-mode, or the escaped ID, without extension.
  *
  */
  function get_common_basename() {
    if ($this->namespace_mode) {
      return "all";
    } else {
      return $this->_escape_fn(noNS($this->id));
    }
  }

  /* This returns the full path of the base header file we take as reference
   * for this compilation. In case nothing is found, false is returned.
   */
  function get_base_header_fn() {
    // first we look for nsbpc headers
    // the names are 'texit-namespace' or 'texit-page'
    $header_name = "texit-page";
    if ($this->namespace_mode) {
      $header_name = "texit-namespace";
    }
    $found = $this->nsbpc->getConfFN($header_name, $this->ns);
    if ($found) {
      return $found;
    }
    // No nsbpc configuration was found, now looking in the conf/ directory of
    // the plugin. Names are different here...
    $header_name = "header-page.tex";
    if ($this->namespace_mode) {
      $header_name = "header-namespace.tex";
    }
    if (is_readable(PLUGIN_TEXIT_CONF.$header_name)) {
      return PLUGIN_TEXIT_CONF.$header_name;
    }
    return false;
  }

  /* This returns the full path of the header file we want in the destination
   * texit namespace.
   */
  function get_dest_header_fn() {
    if ($this->namespace_mode) {
      return $this->texitdir."/all.tex";
    } else {
      return $this->texitdir.'/'.$this->get_common_basename().".tex";
    }
  }
  /* This returns the full path of the base footer file we take as reference
   * for this compilation, or false if there is no such file.
   */
  function get_base_footer_fn() {
    // first we look through nsbpc
    $found = $this->nsbpc->getConfFN("texit-footer", $this->ns);
    if ($found) {
      return $found;
    }
    // No nsbpc configuration was found, now looking in the conf/ directory of
    // the plugin.
    if (is_readable(PLUGIN_TEXIT_CONF."footer.tex")) {
      return PLUGIN_TEXIT_CONF."footer.tex";
    }
    return false;
  }
  /* This returns the full path of the commands file we want in the destination
   * texit namespace.
   */
  function get_dest_footer_fn() {
    return $this->texitdir."/footer.tex";
  }
  /* This returns the full path of the base coommands file we take as reference
   * for this compilation.
   */
  function get_base_commands_fn() {
    // first we look through nsbpc
    $found = $this->nsbpc->getConfFN("texit-commands", $this->ns);
    if ($found) {
      return $found;
    }
    // No nsbpc configuration was found, now looking in the conf/ directory of
    // the plugin.
    if (is_readable(PLUGIN_TEXIT_CONF."commands.tex")) {
      return PLUGIN_TEXIT_CONF."commands.tex";
    }
    return false;
  }
  /* This returns the full path of the commands file we want in the destination
   * texit namespace.
   */
  function get_dest_commands_fn() {
    return $this->texitdir."/commands.tex";
  }

 /* This function returns an array of all IDs of pages to be rendered by TeXit.
  *
  */
  function get_all_IDs() {
    global $conf;
    if ($this->namespace_mode) {
      $list = array();
      $nsdir = str_replace(':', '/', $this->ns);
      $opts = array('listdirs'  => false,
                    'listfiles' => true,
                    'pagesonly' => true,
                    'depth'     => 1,
                    'skipacl'   => false, // to check for read right
                    'sneakyacl' => true,
                    'showhidden'=> false,
                    );
	  if ($this->conf['includestart'] == false) {
	    $opts['idmatch'] = "^((?!start$).)+$";
      }
      search($list,$conf['datadir'],'search_universal',$opts,$nsdir);
      return $list;
    } else {
      return array(array('id' => $this->id));
    }
  }

 /* Returns an array with base and destination filenames. Works with full paths.
  *
  * The returned array has the following structure:
  *    [base] => (type, fn)
  * where:
  *  * base is the base filename (like /path/to/dkwiki/pages/ns/id.txt)
  *  * type is either "header", "commands", "tex" or "bib".
  *  * fn is the absolute destination filename (prefix included)
  */
  function get_all_files() {
   // this gives us all the page ids that need txt->tex conversion:
   $id_array = $this->get_all_IDs();
   $result = array();
   // now we put them all in the $result array
   foreach($id_array as $value) {
     if (!is_array($value) || !$value['id']) { // I did'nt find any more elegant way to do so
       continue;
     }
     $fn = wikiFN($value['id']);
     $dest = $this->texitdir.'/'.noNS($value['id'])."-content.tex";
     $dest = $this->_escape_fn($dest);
     $result[$fn] = array('type' => 'tex', 'fn' => $dest);
   }
   // and we add the header and command
   $base = $this->get_base_header_fn();
   if (!$base) {
     nice_die("TeXit: Unable to find a header file!");
   }
   $result[$base] = array('type' => 'header', 'fn' => $this->get_dest_header_fn());
   $base = $this->get_base_commands_fn();
   if (!$base) {
     nice_die("TeXit: Unable to find a commands file!");
   }
   $result[$base] = array('type' => 'commands', 'fn' => $this->get_dest_commands_fn());
   $bib = $this->get_base_bib_fn();
   if ($bib) { // not mandatory
     $result[$bib] = array('type' => 'bib', 'fn' => $this->get_dest_bib_fn());
   }
   $footer = $this->get_base_footer_fn();
   if ($footer) { // not mandatory
     $result[$footer] = array('type' => 'footer', 'fn' => $this->get_dest_footer_fn());
   }
   $this->all_files = $result;
  }

 /* This function takes three arguments:
  *    * base is the full path of the base header file
  *           (for instance /path/to/dkwiki/lib/plugin/texit/conf/header-page.tex)
  *    * dest is the full path of the destination header file
  *    * all_files is the table returned by get_all_files()
  *
  * It reads $base, adds \input lines for $all_files and writes the result in
  * $dest.
  */
  function compile_header($base, $dest, $all_files) {
    // first we simply copy the file
    $this->simple_copy($base, $dest);
    // we prepare a string to append at the end:
    $toappend = "\n";
    // we spot the last value:
    $beginning = 1;
    $footer = false;
    foreach($this->all_files as $value) {
      switch($value['type']) {
        case 'tex':
          // between two different files, we call the \dokuinternspagedo
          // macro, doing nothing by default.
          if (!$beginning) {
            $toappend .= "\\dokuinternspagedo\n\n";
          }
          $toappend .= '\dokuinclude{'.basename($value['fn'], '.tex')."}\n\n";
          break;
        case 'footer':
          $footer = basename($value['fn'], '.tex');
        default:
          break;
      }
      $beginning = 0;
    }
    if ($footer) {
      $toappend .= "\dokuinclude{".$footer."}\n";
    }
    $toappend .= "\n\\end{document}";
    // the we open it in append mode to write things at the end:
    file_put_contents($dest, $toappend, FILE_APPEND);
  }

 /* This function takes two arguments:
  *    * base is the full path of the base page file
  *           (for instance /path/to/dkwiki/data/pages/ns/id.txt)
  *    * dest is the full path of the destination tex file
  *
  * It reads $base, renders it into TeX and writes $dest.
  */
  function compile_tex($base, $dest) {
    if (!$this->conf['latexentities'])
      {
        $this->conf['latexentities'] = $this->get_entities();
      }
    if (!$this->texit_render_obj)
      {
        $this->texit_render_obj = new texitrender_plugin_texit($this);
      }
    $this->texit_render_obj->process($base, $dest);
  }

 /* This function takes two arguments:
  *    * base is the full path of the base file
  *    * dest is the full path of the destination tex file
  *
  * It copies $base into $dest.
  */
  function simple_copy($base, $dest) {
    if (!copy($base, $dest)) {
      nice_die("TeXit: unable to copy $base into $dest.");
    }
  }

 /*
  * This functions returns true if $base is more recent that $dest, and
  * false otherwise. If $dest doesn't exist, then we consider it needs
  * update and thus return true.
  */
  function _needs_update($base, $dest) {
    if (!file_exists($dest) || !file_exists($dest)) {
        return true;
      }
    return filemtime($base) > filemtime($dest);
  }
  
 /* This function sets the TeX compilation environment up by copying the files
  * in the good folders and renames them. It uses file modification timestamps
  * to evaluate if files need to be recompiled or recopied.
  *
  * The returned value is a boolean: true if something has been updated, and
  * false otherwise.
  */
  function setup_files() {
    if (!is_array($this->all_files)) {
        $this->get_all_files();
      }
    if (!is_array($this->all_files)) {
        die("TeXit: cannot analyze files");
      }
    $needsupdate = false;
    foreach($this->all_files as $base => $dest) {
      $destfn = $dest['fn'];
      if ($this->_needs_update($base, $destfn)) {
        $needsupdate = true;
        switch($dest['type']) {
          case "header":
            $this->compile_header($base, $destfn, $this->all_files);
            break;
          case "commands":
            $this->simple_copy($base, $destfn);
            break;
          case "bib":
            $this->simple_copy($base, $destfn);
            break;
          case "footer":
            $this->simple_copy($base, $destfn);
            break;
          case "tex":
            $this->compile_tex($base, $destfn);
            break;
          default:
            break;
        }
      }
    }
    return $needsupdate;
  }
  
 /* This function calls latexmk with the good options on the good files.
  */
  function _do_latexmk() {
    if (!is_dir($this->texitdir)) {
      die("TeXit: directory $this->texitdir doesn't exit");
    }
    chdir($this->texitdir);
    $basecmdline = '';
    if (isset($this->conf['latexmk_path']) 
      && trim($this->conf['latexmk_path']) != "") {
      $basecmdline = $this->conf['latexmk_path'] . DIRECTORY_SEPARATOR;
    } else {
      $basecmdline = '';
    }
    $cmdline = $basecmdline."latexmk -f ";
    if ($this->bibfn) {
      $cmdline .= "-bibtex ";
    }
    switch ($this->conf['latex_mode'])
    {
      case "latex":
        // TODO: test, comes from http://users.phys.psu.edu/~collins/software/latexmk-jcc/
        $cmdline .= "-e '\$dvipdf = \"dvipdfm %O -o %D %S\";' -pdfdvi "; 
        break;
      case "pdflatex":
        $cmdline .= "-pdf ";
        break;
      case "lualatex":
        $cmdline .= "-pdf -pdflatex=lualatex ";
        break;
      case "xelatex":
        $cmdline .= "-latex=xelatex -e '\$dvipdf = \"dvipdfmx %O -o %D %S\";' -pdfdvi ";
        break;
      default:
        // error
        break;
    }
    $file = basename($this->get_dest_header_fn());
    $cmdline .= $file . ' 2>&1 ';
    $ret = 0;
    @exec($cmdline, $output, $ret);
    if ($ret) {
      print("<br/>TeXit error: latexmk returned error code ".$ret."<br/>\n<br/>Log:<br/>\n");
      print_r(implode("<br/>\n", $output));
    }
    // at the end, we clean temporary files. There is currently no way to tell
    // latexmk to clean at the end of the compilation... quite a shame...
    // An email has been written to the author in this sense.
    $cmdline = $basecmdline."latexmk -c 2>&1";
    $log = @exec($cmdline, $output, $ret);
    if ($ret) {
      print("<br/>TeXit error: latexmk -c returned error code ".$ret."<br/>\n<br/>Log:<br/>\n");
      print_r(implode("<br/>\n", $output));
    }
  }

 /* This function zips the good files in the texit namespace in a .zip archive
  * in the media namespace.
  */
  function compile_zip() {
    $zipfn = $this->get_zip_fn();
    // if the file already exists and needs update, remove it.
    if (@file_exists($zipfn)) {
      unlink($zipfn);
    }
    $zip = new ZipArchive();
    if ($zip->open($zipfn, ZipArchive::CREATE) !== true) {
      exit("Unable to create $zipfn\n");
    }
    // First argument of addFile is the absolute, second is the name we want
    // in the archive (in our case, the basename).
    $zip->addFile($this->get_pdf_texit_fn(), basename($this->get_pdf_texit_fn()));
    foreach($this->all_files as $base => $dest) {
      $zip->addFile($dest['fn'], basename($dest['fn']));
    }
    $zip->close();
  }
  
 /* My mind is too used to C programming and thus this is a bit too
  * iterative and not object-oriented enough...
  *
  * This function processes everything when the user asks for a PDF.
  */
  function process() {
    $needsupdate = $this->setup_files();
    $pdftexitfn  = $this->get_pdf_texit_fn();
    $pdfmediafn  = $this->get_pdf_media_fn();
    $pdfmediaid  = $this->get_pdf_media_id();
    $zipfn       = $this->get_zip_fn();    
    if ($needsupdate || !@file_exists($pdftexitfn)) {
      $this->_do_latexmk();
    }
    // then copy the pdf to media
    if ($needsupdate || !@file_exists($pdfmediafn)) {
      $this->simple_copy($pdftexitfn, $pdfmediafn);
    }
    if ($this->conf['use_zip'] && ($needsupdate || !@file_exists($zipfn))) {
        $this->compile_zip();
    }
    return $this->id_to_url($pdfmediaid);
  }

 /* This returns an absolute URL from a media ID.
  */
  function id_to_url($pdfmediaid) {
    // internal dokuwiki function, defined in inc/common.php
    return ml($pdfmediaid, '', true, '&amp;', true);
  }
}

?>
