<?php
/**
 * projects Action Plugin: hijack the page write events
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */
 
require_once(dirname(__FILE__).'/../conf.php');
require_once DOKU_PLUGIN.'action.php';

class action_plugin_projects_page extends DokuWiki_Action_Plugin {

	function getInfo(){
		return array(
		 'author' => 'Junling Ma',
		 'email'  => 'junlingm@gmail.com',
		 'date'   => '2010-12-15',
		 'name'   => 'Projects',
		 'desc'   => 'hijack page write events',
		 'url'    => 'http://www.math.uvic.ca/~jma'
		 );
	}
 
    /**
     * Register its handlers with the DokuWiki's event controller
     */
    function register(&$controller) {
        $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this,
                                   'wrote');
    }

    /**
     * intercept page deletion
     *
     */
    function wrote(&$event, $param) {
		$content = $event->data[0][1];
		if ($content) return;
		$namespace = $event->data[1];
		$project = Project::project($namespace);
		if ($project == NULL) return;
		$name = $event->data[2];
		if ($project->file($name) == NULL) return;
    	if (!$project->remove_file($name))
			msg('Other users are currently updating the project. Please save this page later.'); 
	}
	
}

?>