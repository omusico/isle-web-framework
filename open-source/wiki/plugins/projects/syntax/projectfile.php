<?php
/**
 * The syntax plugin to handle <project-file> tags
 *
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/../lib/layout.php');
require_once(dirname(__FILE__).'/../lib/file_definition.php');
require_once(dirname(__FILE__).'/../lib/project.php');
require_once DOKU_PLUGIN . 'syntax.php';
require_once DOKU_INC . 'inc/common.php';

class syntax_plugin_projects_projectfile extends DokuWiki_Syntax_Plugin {
    // the current project-file
    private $project_file = NULL;
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Junling Ma',
            'email'  => 'junlingm@gmail.com',
            'date'   => '2011-11-24',
            'name'   => 'Project-file Plugin',
            'desc'   => 'display the header of project files',
            'url'    => 'http://www.math.uvic.ca/~jma'
        );
    }
 
    function getType() { 
    	return 'substition';
    }
        
    function getPType() { 
    	return 'block';
    }
        
    function getSort() { 
    	return 1; 
    }
    
    function connectTo($mode) {
    	$this->Lexer->addSpecialPattern('<' . PROJECTS_TAG . ' .*?/>',
    		$mode, 'plugin_projects_projectfile'); 
    }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        set_media_file_revision_limit($this->getConf('media file revision limit'));
    	if ($state != DOKU_LEXER_SPECIAL) return NULL;
    	global $ID;
    	$path = explode(":", $ID);
    	$name = array_pop($path);
        $this->project_file = FileDefinition::parse($name, $match);
        $this->project_file->position = $pos;
        $this->project_file->end = $pos + strlen($match) - 1;
        return $this->project_file;
    }
 
    /**
     * Create output
     */
    function render($mode, &$renderer, $file) {
		if ($file == NULL) return;
		switch ($mode) {
			case 'metadata' :
				if (is_array($data)) return;
				global $ID;
				$renderer->meta['ProjectFile'] = $file;
				if ($file->attribute('highlight'))
					$renderer->persistent['ProjectFile:highlight'] = $file->attribute('highlight');
				break;
			case 'xhtml' :
				$this->render_xhtml($renderer, $file);
				break;
		}
	}

	private function render_xhtml(&$renderer, $file) {
		global $version_file;
		global $changelog;
		global $ID;
		if (auth_quickaclcheck($ID) == AUTH_ADMIN && $this->getConf('check updates')) {
			include(dirname(__FILE__).'/../version.php');
			$new_version = file_get_contents($version_file);
			if (strcmp($new_version, $version) > 0) 
				msg('A new version ' . $new_version . 
					' of the projects plugin is available. See the <a href="' 
					. $changelog . '">change log</a>'); 
			if (!$file) return;
		}
		$project = Project::project(NULL, true);
		$renderer->nocache();
		switch ($file->type()) {
    		case SOURCE:
		    	$layout = new ProjectFileLayout($project, $ID, $file); 
				break;
			case TARGET:
		    	$layout = new TargetLayout($project, $ID, $file); 
		    	break;
		    case CROSSLINK:
		    	$layout = new CrosslinkLayout($project, $ID, $file); 
		    	break;
		}
		$layout->render($renderer);
	}
	
}
?>