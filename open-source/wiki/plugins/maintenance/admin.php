<?php
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');


/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_maintenance extends DokuWiki_Admin_Plugin {

    function __construct() {
        $this->helper =& plugin_load('helper', 'maintenance');
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 10;
    }

    /**
     * handle user request
     */
    function handle() {
        switch($_REQUEST['fn']) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'lock':
                $this->lock();
                break;
            case 'unlock':
                $this->unlock();
                break;
        }
    }

    /**
     * output appropriate html
     */
    function html() {
        print $this->locale_xhtml('intro');

        $form = new Doku_Form(array('id'=>'start'));
        $form->addHidden("page", $_REQUEST['page']);
        $form->addHidden("fn", "start");
        $form->addElement(form_makeButton('submit', 'admin', $this->getLang('start_btn')));
        $form->addElement('<p>'.$this->getLang('start_desc').'</p>');
        html_form('', $form);

        $form = new Doku_Form(array('id'=>'stop'));
        $form->addHidden("page", $_REQUEST['page']);
        $form->addHidden("fn", "stop");
        $form->addElement(form_makeButton('submit', 'admin', $this->getLang('stop_btn')));
        $form->addElement('<p>'.$this->getLang('stop_desc').'</p>');
        html_form('', $form);

        $form = new Doku_Form(array('id'=>'lock'));
        $form->addHidden("page", $_REQUEST['page']);
        $form->addHidden("fn", "lock");
        $form->addElement(form_makeButton('submit', 'admin', $this->getLang('lock_btn')));
        $form->addElement('<p>'.$this->getLang('lock_desc').'</p>');
        html_form('', $form);

        $form = new Doku_Form(array('id'=>'unlock'));
        $form->addHidden("page", $_REQUEST['page']);
        $form->addHidden("fn", "unlock");
        $form->addElement(form_makeButton('submit', 'admin', $this->getLang('unlock_btn')));
        $form->addElement('<p>'.$this->getLang('unlock_desc').'</p>');
        html_form('', $form);
    }

    function start() {
        // check script
        $script = $this->helper->get_script();
        if (!is_file($script)) {
            $msg = sprintf( $this->getLang('start_no_script'), $script);
            msg($msg, -1);
            return;
        }
        // check if already locked
        if ($this->helper->is_locked()) {
            $msg = sprintf( $this->getLang('locked'), $script);
            msg($msg, -1);
            return;
        }
        // lock the site and run script
        $result = $this->helper->script_start($script);
        switch ($result) {
            case 0:
                $msg = sprintf( $this->getLang('start_fail'), $script);
                msg($msg, -1);
                break;
            case 1:
                $msg = $this->getLang('start_success');
                msg($msg, 1);
                break;
            case 2:
                $msg = $this->getLang('start_already');
                msg($msg, -1);
                break;
        }
    }

    function stop() {
        $result = $this->helper->script_stop();
        switch ($result) {
            case 0:
                $msg = $this->getLang('stop_fail');
                msg($msg, -1);
                break;
            case 1:
                $msg = $this->getLang('stop_success');
                msg($msg, 1);
                break;
            case 2:
                $msg = $this->getLang('stop_already');
                msg($msg, -1);
                break;
        }
    }

    function lock() {
        // check if already locked
        if ($this->helper->is_locked()) {
            $msg = sprintf( $this->getLang('locked'), $script);
            msg($msg, -1);
            return;
        }
        $result = $this->helper->manual_lock();
        switch ($result) {
            case 0:
                $msg = $this->getLang('lock_fail');
                msg($msg, -1);
                break;
            case 1:
                $msg = $this->getLang('lock_success');
                msg($msg, 1);
                break;
            case 2:
                $msg = $this->getLang('lock_already');
                msg($msg, -1);
                break;
        }
    }

    function unlock() {
        $result = $this->helper->manual_unlock();
        switch ($result) {
            case 0:
                $msg = $this->getLang('unlock_fail');
                msg($msg, -1);
                break;
            case 1:
                $msg = $this->getLang('unlock_success');
                msg($msg, 1);
                break;
            case 2:
                $msg = $this->getLang('unlock_already');
                msg($msg, -1);
                break;
        }
    }
}
