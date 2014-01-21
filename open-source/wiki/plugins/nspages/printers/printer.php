<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();

abstract class nspages_printer {
    protected $plugin;
    protected $renderer;
    protected $mode;
    private $pos;
    private $acualLevelTitle;

    function __construct($plugin, $mode, $renderer, $data){
      $this->plugin = $plugin;
      $this->renderer =& $renderer;
      $this->mode = $mode;
      $this->pos = $data['pos'];
      $this->actualTitleLevel = $data['actualTitleLevel'];
    }

    function printTOC($tab, $type, $text, $reverse){
        $this->_printHeader($tab, $type, $text, $reverse);

        if(empty($tab)) {
            return;
        }

        $this->_print($tab, $type);
    }

    abstract function _print($tab, $type);

    function printUnusableNamespace($wantedNS){
         $this->renderer->section_open(1);
         $this->renderer->cdata($this->plugin->getLang('doesntexist').$wantedNS);
         $this->renderer->section_close();
    }

    private function _printHeader(&$tab, $type, $text, $reverse) {
        $this->_sort($tab, $reverse);

        if($text != '') {
            if($this->actualTitleLevel){
                $this->renderer->header($text, $this->actualTitleLevel, $this->pos);
            } else if($this->mode == 'xhtml') {
                $this->renderer->doc .= '<p class="catpageheadline">';
                $this->renderer->cdata($text);
                $this->renderer->doc .= '</p>';
            } else {
                $this->renderer->linebreak();
                $this->renderer->p_open();
                $this->renderer->cdata($text);
                $this->renderer->p_close();
            }
        }

        if(empty($tab)) {
            $this->renderer->p_open();
            $this->renderer->cdata($this->plugin->getLang(($type == 'page') ? 'nopages' : 'nosubns'));
            $this->renderer->p_close();
        }
    }

    private function _sort(&$tab, $reverse) {
        if(!$reverse) {
            usort($tab, array("nspages_printer", "_order"));
        } else {
            usort($tab, array("nspages_printer", "_orderReverse"));
        }
    } // _sort

    private static function _order($p1, $p2) {
        return strcasecmp(utf8_strtoupper($p1['sort']), utf8_strtoupper($p2['sort']));
    } //_order

    private static function _orderReverse($p1, $p2) {
        return -strcasecmp(utf8_strtoupper($p1['sort']), utf8_strtoupper($p2['sort']));
    }

    /**
     * @param Array        $item      Represents the file
     */
    protected function _printElement($item) {
        if($item['type'] !== 'd') {
            $this->renderer->listitem_open(1);
            $this->renderer->listcontent_open();
            $this->renderer->internallink(':'.$item['id'], $item['title']);
            $this->renderer->listcontent_close();
            $this->renderer->listitem_close();
        } else { //Case of a subnamespace
            if($this->mode == 'xhtml') {
                $this->renderer->doc .= '<li class="closed">';
            } else {
                $this->renderer->listitem_open(1);
            }
            $this->renderer->listcontent_open();
            $this->renderer->internallink(':'.$item['id'], $item['title']);
            $this->renderer->listcontent_close();
            $this->renderer->listitem_close();
        }
    }


    function printEnd(){
        //this is needed to make sure everything after the plugin is written below the output
        if($this->mode == 'xhtml') {
            $this->renderer->doc .= '<br class="catpageeofidx">';
        } else {
            $this->renderer->linebreak();
        }
    }

    function printTransition(){ }
}
