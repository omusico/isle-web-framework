<?php

/**
 * manages the display layout of the project files on the wiki page
 *
 */
 
require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/tools.php');

class ProjectFileLayout {
    protected $project = NULL;
	protected $ID = NULL;
	protected $file_name = NULL;
	protected $file = NULL;
	protected $errors = NULL;
	protected $layour = NULL;
	protected $tag = NULL;
	protected $header = NULL;
	protected $name = NULL;
	protected $content = NULL;
	protected $failure = false;
	
	public function __construct($project, $ID, $file) {
		$this->ID = $ID;
		$this->file_name = array_pop(explode(":", $ID));
		$this->project = $project;
		$this->file = $file;
	}
	
	private function set_errors(&$renderer) {
		if ($this->project == NULL) {
			$renderer->doc .= "<div class=\"project_file_errors\">";
			$this->add_error($renderer, htmlspecialchars('<' . PROJECTS_TAG . '>') . 
				" tags can only be used in \"" . PROJECTS_NAMESPACE .
				'" namespace. ' .
				"The following project file does not have any effect!");
			$renderer->doc .= "</div>";
			return;
		}
		$errors = $this->project->error($this->file_name);
		if (!$errors) return;
		$renderer->doc .= "<div class=\"project_file_errors\">";
		foreach ($errors as $error) {
			if ($error == 'undefined') {
				$this->add_error($renderer, "Do not know how to generate the file");
				continue;
			}
			if (isset($error['failure'])) {
				$this->failure = true;
				$this->add_error($renderer, 
					"Failed to generate. see the log below.");
				continue;
			}
			if (isset($error['dependency'])) {
				$deps = $this->file->dependency();
				$dep = $error['dependency'];
				if (in_array($dep, $deps)) continue;
				$dep_id = $this->project->id($dep);
				if ($dep_id != NULL)
					$link = "<a href=\"" . wl($dep_id) . "\">$dep</a>";
				else $link = $dep;
				$this->add_error($renderer, 
					"Do not know how to generate the dependency $link");
				continue;
			}
			if (isset($error['loop'])) {
				$msg = "";
				foreach ($error['loop'] as $loop) 
					$msg .= ' ' . $loop;
				$this->add_error($renderer, "Circular dependency:" . $msg);
				continue;
			}
		}
		$renderer->doc .= "</div>";
	}
	
	public function render(&$renderer) {
		$id = $this->file_name;
		// the whole tag
		$renderer->doc .= "<div class=\"project_file\" id=\"$id\">";
		$renderer->doc .= "<a name=\"$id\"></a>";
		//header
		$renderer->doc .= "<div class=\"project_file_header\">";
		//type
		$renderer->doc .= "<div class=\"tag_header\">Type</div>";
		$type = $this->file->type();
		$renderer->doc .= "<div class=\"project_file_type\">$type</div>";
		//name
		$renderer->doc .= "<div class=\"tag_header\">Name</div>";
		$renderer->doc .= "<div class=\"project_file_name\">";
		$this->render_name($renderer);
		$renderer->doc .= "</div>";
		//project
		if ($this->project != NULL)
			$project_name = $this->project->name();
		else
			$project_name = "None";
		$renderer->doc .= "<div class=\"tag_header\">in project</div>";
		$renderer->doc .= 
			"<div class=\"project_file_project\">$project_name";
		// close project
		$renderer->doc .= "</div>";
		//buttons
		$renderer->doc .= "<div class=\"spacer\"></div>";
		$renderer->doc .= "<div class=\"project_file_buttons\">";
		$this->render_buttons($renderer);
		$renderer->doc .= "</div>";
		//close header
		$renderer->doc .= "</div>";
		$this->set_errors($renderer);
		$renderer->doc .= "<div class=\"project_file_content\">";
		$renderer->doc .= $this->render_content($renderer);
		// close content
		$renderer->doc .= "</div>";
		$renderer->doc .= "</div>";
	}

	protected function render_name(&$renderer) {
		$renderer->doc .= $this->file_name;
	}

	protected function render_content(&$renderer) {}
	
	protected function render_buttons(&$renderer) {
		global $ID;
        $renderer->doc .= button_add("Add dependency", USE_TAG);
		$renderer->doc .= button_delete($this->project->id($this->file_name));
		if ($this->project == NULL) return;
		$renderer->doc .= action_button("Manage files", 'manage_files');
	}
	
	public function add_error(&$renderer, $error) {
		$renderer->doc .= "<div class=\"error\">$error</div>";
	}
}

class TargetLayout extends ProjectFileLayout {
	private $link = NULL;
	private $display = NULL;

	public function __construct($project, $ID, $file) {
		parent::__construct($project, $ID, $file);
		if ($project != NULL) $this->link = ml($ID);
		$this->display = $file->attribute('display');
	}
	
	protected function render_name(&$renderer) {
		if ($this->display == 'link')
			$renderer->internalmedia($this->file_name, NULL, NULL, NULL, NULL, NULL, 'linkonly');
		else parent::render_name($renderer);
	}
	
	protected function render_content(&$renderer) {
		if ($this->display_link) return;
		$name = $this->file_name;
		$link = $this->link;
		if ($this->failure) {
			$renderer->preformatted(file_get_contents(
				$this->project->path() . $name . '.make.log'));
			return;
		}
        $this->render_file_content($renderer);
    }
    
    protected function render_file_content(&$renderer) {
        $name = $this->file_name;
        $renderer->internalmedia($this->project->id($name));
        if (!isset($attributes['display']) ||
            $attributes['display'] != 'link') {
            $mime = file_mimetype($this->ID, $this->project);
          if (substr($mime, 0, 5) == 'text/' || $mime == 'plain/text')
                render_code($renderer, file_get_contents(
                    $this->project->path() . $name), 'unspecified');
        }
	}

	protected function render_buttons(&$renderer) {
		global $ID;
		if (auth_quickaclcheck($ID) > AUTH_READ)
			$renderer->doc .= button_remake($this->project->id($this->file_name));
		parent::render_buttons($renderer);
	}	
}

class CrossLinkLayout extends TargetLayout {
	private $crosslink = NULL;
	private $media = FALSE;
	public function __construct($project, $name, $file) {
		parent::__construct($project, $name, $file);
		$this->crosslink = $file->crosslink();
        $path = explode(':', $this->crosslink);
        $this->media = ($path[0] == '[media]');
        if ($this->media) array_shift($path);
        $id = implode(':', $path);
        if (!getNS($id)) $id = $project->id($id);
        $this->crosslink = $id;
	}

	protected function render_file_content(&$renderer) {
		$renderer->doc .= "<div class=\"crosslink\">";
		$path = explode(':', $this->crosslink);
		if ($path[0] == PROJECTS_NAMESPACE) {
        	if ($this->media)
			    $renderer->doc .= '<a href="' . ml($this->crosslink) . '">' . $this->crosslink . '</a>';
            else
			    $renderer->doc .= html_wikilink($this->crosslink);
		}
		$renderer->doc .= "</div>"; 
	}
}

?>