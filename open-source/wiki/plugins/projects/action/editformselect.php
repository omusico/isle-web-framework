<?php
/**
* projects Action Plugin: hijack the ACTION_ACT_PREPROCESS events for admin action
*
* @author     Junling Ma <junlingm@gmail.com>
*/

require_once(dirname(__FILE__).'/../lib/project.php');
require_once DOKU_PLUGIN.'action.php';

class action_plugin_projects_editformselect extends DokuWiki_Action_Plugin {

    function getInfo(){
    return array(
        'author' => 'Junling Ma',
        'email'  => 'junlingm@gmail.com',
        'date'   => '2012-08-13',
        'name'   => 'Projects',
        'desc'   => 'Create HTML edit form for projects wiki represented file contents',
        'url'    => 'http://www.math.uvic.ca/~jma'
        );
    }

    /**
    * Register its handlers with the DokuWiki's event controller
    */
    function register(&$controller) {
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this,
                'select');
    }

    function select(&$event, $param) {
        if ($event->data['target'] != 'projects_wiki_file') return;
        global $conf;
        if ($conf['compress'])
            $event->data['form']->addElement('<div class=error>CodeMirror is not compactable with Javascript and CSS compression. Please set compress to off in <a href="?do=admin&page=config"> Admin/Configuration Settings</a>".</div>');
        $event->data['form']->addHidden('projects_wiki_lang', $_REQUEST['lang']);
        $event->data['target'] = 'section';
    }
    
}

?>