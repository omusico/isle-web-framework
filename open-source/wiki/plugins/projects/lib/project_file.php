<?php
/**
 * ProjectFile: a class that declares a single poject-file in a project
 * definition file.
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */

class ProjectFile {
	protected $name = NULL;
	protected $deps = NULL;

	public function __construct($file) {
		$this->name = $file->name();
		$this->deps = $file->dependency();
	}

	public function name() { return $this->name; }	
	public function dependency() { return $this->deps; }	
	public function changed_on_disk() { return $this->changed_on_disk; }	

	public function path($project_path) { 
		return $project_path . $this->name; 
	}

	public function delete($project_path) {
		$path = $this->path($project_path);
		if (file_exists($path)) {
        	$this->delete_media_file($project_path);
        	unlink($path);
        }
	}
	
	public function copy($project_path, $file) {
		$this->deps = $file->dependency();
	}
	
	public function type() { return NULL; }

	public function equal($project_path, $file) {
		return $this->type() == $file->type()
			&& $this->deps == $file->dependency();
	}
	
	public static function create($project, $file) {
		$class = 'Project' . ucfirst($file->type());
		return new $class($project, $file);
	}
	
	public function time($project_path) {
		$path = $this->path($project_path);
		$time = (file_exists($path)) ? filemtime($path) : NULL;
		return $time;
	}

	public function content($project_path) { 
		$path = $this->path($project_path);
		if (file_exists($path))
			return file_get_contents($path); 
		return NULL;
	}

	protected function update_media_file($project_path) {
    	$path = $this->path($project_path);
    	if (is_link($path)) return;
        global $ID;
        $id = getNS($ID) . ':' . $this->name;
        $media_path = mediaFN($id);
    	global $media_file_revision_limit;
    	if ($media_file_revision_limit > 0 && 
        	filesize($media_path) > $media_file_revision_limit) {
        	unlink($media_path);
        	msg("File $id is over the \"media file revision limit\". A revision will not be saved");
    	}
    	list($ext, $mime) = mimetype($path);
    	$data[0] = $path;
    	$data[1] = mediaFN($id);
    	$data[2] = $id;
    	$data[3] = $mime;
    	$data[4] = file_exists(mediaFN($id));
    	$data[5] = 'copy_updated_file';
    	trigger_event('MEDIA_UPLOAD_FINISH', $data, '_media_upload_action', true);
	}

	protected function delete_media_file($project_path) {
        global $ID;
        $id = getNS($ID) . ':' . $this->name;
    	$media_path = mediaFN($id);
    	if (file_exists($media_path)) {
        	media_saveOldRevision($id);
        	unlink($media_path);
    	}
	}
}

class ProjectSource extends ProjectFile {
	public function __construct($project, $file) {
		parent::__construct($file);
		$this->set_content($project->path(), $file->content());
	}

	public function is_target() { return false; }
	public function type() { return SOURCE; }

	public function equal($project_path, $file) {
		if (!parent::equal($project_path, $file)) return false;
		return $this->content($project_path) == $file->content();
	}

	private function set_content($project_path, $content) { 
		file_put_contents($this->path($project_path), $content);
        $this->update_media_file($project_path);
	}

	public function copy($project_path, $file) {
		parent::copy($project_path, $file);
		$this->set_content($project_path, $file->content());
	}

	public function makable() { return true; }
}

class ProjectGenerated extends ProjectFile {
	protected $recipe = NULL;
	protected $last_made = NULL;
	
	public function __construct($project, $file) {
		parent::__construct($file);
		$this->recipe = $file->recipe();
	}

	public function equal($project_path, $file) {
		return parent::equal($project_path, $file) 
			&& $this->recipe == $file->recipe();
	}

	public function type() { return TARGET; }
	public function recipe() { return $this->recipe; }

	public function copy($project_path, $file) {
		parent::copy($project_path, $file);
		$this->recipe = $file->recipe();
	}

	public function delete($project_path, $delete_log = false) {
		parent::delete($project_path);
		if ($delete_log) {
			$path = $this->path($project_path) . '.make.log';
			if (file_exists($path)) unlink($path);
		}
	}

	public function make($working_path) {
		$current_dir = getcwd();
		chdir($working_path);
		$path = $working_path . $this->name;
		if (!$this->recipe) return 0;
		if (file_exists($path)) unlink($path);
		$log = $this->log_file($working_path);
		if (file_exists($log)) unlink($log);
		$command = "/bin/sh -e >$log 2>&1";
		$recipe = $this->recipe();
		$f = popen($command, 'w');
		fprintf($f, "%s\n", $this->recipe());
		$result = pclose($f);
        if ($result === 0)
        	$this->update_media_file($working_path);
        else $this->delete_media_file($working_path);
		chdir($current_dir);
		return $result;
	}

	public function set_last_made_time($working_path) {
		$this->last_made = $this->time($working_path);
	}
	
	public function needs_update($working_path) {
		if ($this->last_made == NULL) return true;
		return $this->time($working_path) != $this->last_made;
	}
	
	public function makable() { 
		if ($this->recipe === NULL) return false;
		return (trim($this->recipe) != "");
	}
	
	public function is_target() { return true; }
	
	protected function log_file($working_path) {
		return $this->path($working_path) . '.make.log';
	}

	protected function log($working_path, $content) {
		$log = $this->log_file($working_path);
		file_put_contents($log, $content);
	}
}

class ProjectCrossLink extends ProjectGenerated {
	protected $crosslink = NULL;

	public function __construct($project, $file) {
		parent::__construct($project, $file);
		$this->crosslink = $file->crosslink(); 
	}

	public function crosslink() { return $this->crosslink; }

	public function type() { return CROSSLINK; }

	public function equal($project_path, $file) {
		return parent::equal($project_path, $file) 
			&& $this->crosslink == $file->crosslink();
	}

	public function copy($project_path, $file) {
		parent::copy($project_path, $file);
		$this->crosslink = $file->crosslink();
	}

	protected function crosslink_path() {
		if (!$this->crosslink) return NULL;
		$path = explode(":", $this->crosslink);
        $media = ($path[0] == '[media]');
        if ($media) array_shift($path);
		if (count($path) > 1 && $path[0] != PROJECTS_NAMESPACE) return NULL;
		$id = implode(':', $path);
        $project = Project::project();
        if (!getNS($id))
            $id = $project->id($id);
        if ($media) return mediaFN($id);
        return $project->path($id);
	}

	public function time($working_path) {
		$path = $working_path . $this->name;
		if (!file_exists($path)) return NULL;
		$lspath = lstat($path);
		return $lspath['mtime'];
	}
	
	public function make($working_path) {
    	$log = $this->log_file($working_path);
		if (!$this->crosslink) {
			file_put_contents($log, "The crosslink is not defined in this file!\n");
			return 1;
		}
		$link = realpath($this->crosslink_path());
		$path = $this->path($working_path);
		$crosslink = $this->crosslink;
		if (!file_exists($link)) {
			if (file_exists($path)) unlink($path);
			file_put_contents($log, "file $crosslink does not exist!\n");
			return 1;
		}
		file_put_contents($log, "linked $crosslink");
		if (file_exists($path))	unlink($path);
		symlink($link, $path);
		return 0;
	}

	public function makable() { return true; }
}

?>