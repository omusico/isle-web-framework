<?php
/**
 * projects Action Plugin: hajacking the modification of metadata
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */
 
require_once(dirname(__FILE__).'/../lib/project.php');
require_once DOKU_PLUGIN.'action.php';
 
class action_plugin_projects_metadata extends DokuWiki_Action_Plugin {

    function getInfo(){
		return array(
		 'author' => 'Junling Ma',
		 'email'  => 'junlingm@gmail.com',
		 'date'   => '2010-12-15',
		 'name'   => 'Projects',
		 'desc'   => 'Manage the projects media files',
		 'url'    => 'http://www.math.uvic.ca/~jma'
		 );
	}
 
    /**
     * Register its handlers with the DokuWiki's event controller
     */
    function register(&$controller) {
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this,
                                   'saved');
    }

	// the matedata has been rendered 
    function saved(&$event, $param) {
		global $ID;
		global $PROJECTS_REMAKE;
		if (auth_quickaclcheck($ID) <= AUTH_READ) return;
		$project = Project::project();
		if ($project == NULL) return;
		$file = $event->data['current']['ProjectFile'];
		$name = noNS($ID);
		if ($file == NULL) {
			// check whether the file is deleted
			if ($project->file($name) == NULL) return;
			// it was int he project
	    	if (!$project->remove_file($name)) {
				msg('Other users are currently updating the project. Please save this page later.');
				$evemt->data['current']['internal']['cache'] = false;
			}
			return;
		}
		if (!$project->update_file($file)) {
			msg('Other users are currently updating the project. Please save this page later.');
			$evemt->data['current']['internal']['cache'] = false;
		}
	}
		
}

?>