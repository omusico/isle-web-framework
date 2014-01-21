<?php
/**
 * TeXit-Plugin
 * Copyright (C) 2013 Elie Roux <elie.roux@telecom-bretagne.eu>
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

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'texit/config.php');

class action_plugin_texit extends DokuWiki_Action_Plugin {
  /**
   * Registers a callback function for action (?do=foo in the URL) handling
   *
   * @param Doku_Event_Handler $controller DokuWiki's event controller object
   * @return void
   */
  public function register(Doku_Event_Handler &$controller) {
     $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this,
                                'handle_action_act_preprocess');
  }
  /**
   * This is the main function, call at every action.
   *
   * @param Doku_Event $event  event object by reference
   * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
   *                           handler was registered]
   * @return void
   */

  public function handle_action_act_preprocess(Doku_Event &$event, $param) {
    // first check if it's a texit event
    if ($event->data != "texit" && $event->data != "texitns") {
        return false;
    }
    // check user's rights
    if ( auth_quickaclcheck(getID()) < AUTH_READ ) {
      return false;
    }
    $this->loadConfig(); // we need to get the usual plugin config
    $pdfurl = $this->generate_pdf($event->data);
    $this->redirect_to_pdf($pdfurl);
    $event->preventDefault();
    $event->stopPropagation();
    exit();  
  }
  
 /* A bit hackish, but I don't know any other way... (I cannot call getConf in 
  * a non-Dukuwiki plugin class)
  */
  function get_plugin_config() {
    global $conf;
    if (!$this->configloaded){ $this->loadConfig();}
    return $conf['plugin']['texit'];
  }
 /* Generates the pdf and returns the URL.
  * $action is the action name (texit or texitns)
  */
  function generate_pdf($action) {
    global $conf;
    $namespace_mode = false;
    if ($action == "texitns") {
      $namespace_mode = true;
    }
    // we want to have a nsbpc instance in config.php, but as it's not
    // a Dokuwiki plugin, we can't... so we get it here and pass it
    // to config.php
    $nsbpc_obj = $this->loadHelper('nsbpc');
    $texit = new config_plugin_texit(getID(), $namespace_mode, $this->get_plugin_config(), $nsbpc_obj);
    $pdfurl = $texit->process();
    return $pdfurl;
  }

 /* A simple function to redirect the client to the PDF.
  */
  function redirect_to_pdf($pdfurl) {
    //header("Status: 200"); // TODO: see if it's necessary in Chrome
    header("Location: ".$pdfurl, true, 303);
    print("Redirecting to <a href=\"".$pdfurl."\">".$pdfurl."</a>");
  }
}
?>
