<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Randolf Rotta <rrotta@informatik.tu-cottbus.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_usercss extends DokuWiki_Action_Plugin{
  var $pagename = ':usercss';

  function getInfo(){
    return array(
      'author' => 'Randolf Rotta',
      'email'  => 'rrotta@informatik.tu-cottbus.de',
      'date'   => '2009-04-05',
      'name'   => 'user-editable CSS',
      'desc'   => 'Uses CSS definitions from a wiki page',
      'url'    => 'http://www.dokuwiki.org/plugin:usercss',
    );
  }

  function register(&$contr){
    $contr->register_hook(
      'TPL_CSS_OUTPUT',
      'BEFORE',
      $this,
      'add_css',
      array()
    );
    $contr->register_hook(
      'TPL_CSS_CACHEOK',
      'BEFORE',
      $this,
      'add_cached_files',
      array()
    );
  }

  function add_css(&$event, $param) {
    // get content from wiki page
    $txt = io_readWikiPage(wikiFN($this->pagename), $this->pagename);

    // filter for CSS definitions in <code css> blocks
    preg_match_all('/<code css>(.*?)<\/code>/sm', $txt, $matches);
    $usercss = implode("\n", $matches[1]);

    // fix url() locations
    $usercss = preg_replace_callback(
	     '#(url\([ \'"]*)([^\'"]*)#',
	     create_function('$matches',
			     'global $ID; $m = $matches[2];
                              resolve_mediaid(getNS($ID), $m, $exists);
                              return $matches[1].ml($m);'),
	     $usercss);

    // append all
    $event->data .= $usercss;
  }

  function add_cached_files(&$event, $param) {
    $event->data[] = wikiFN($this->pagename);
  }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :