<?php
/**
 * projects Action Plugin: manage the projects namespace
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */
 
require_once(dirname(__FILE__).'/../lib/project.php');
require_once DOKU_PLUGIN.'action.php';

class action_plugin_projects_namespace extends DokuWiki_Action_Plugin {

	function getInfo(){
		return array(
		 'author' => 'Junling Ma',
		 'email'  => 'junlingm@gmail.com',
		 'date'   => '2010-12-15',
		 'name'   => 'Projects',
		 'desc'   => 'Manage the projects namespaces',
		 'url'    => 'http://www.math.uvic.ca/~jma'
		 );
	}
 
    /**
     * Register its handlers with the DokuWiki's event controller
     */
    function register(&$controller) {
        $controller->register_hook('IO_NAMESPACE_CREATED', 'AFTER', $this,
                                   'created');
        $controller->register_hook('IO_NAMESPACE_DELETED', 'AFTER', $this,
                                   'deleted');
    }
 
    /**
     * a namespace has been created
     *
     */
    function created(&$event, $param) {
    	$wiki_path = $event->data[0]; 
    	$project = Project::project($wiki_path, true);
    }
	
    /**
     * a namespace has been deleted
     *
     */
    function deleted(&$event, $param) {
    	$wiki_path = $event->data[0]; 
    	$project = Project::project($wiki_path);
    	if ($project != NULL) $project->delete();
	}
	
}

?>