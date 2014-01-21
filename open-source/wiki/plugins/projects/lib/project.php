<?php
/**
 * Project: a class that implements the management of a project
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/project_file.php');
require_once(dirname(__FILE__).'/maker.php');
require_once(dirname(__FILE__).'/mutex.php');

function unlock_with_error() {
    $project = Project::project();
	if ($project) $project->unlock();
	
/*
if(!is_null($e = error_get_last())) {
	    header('content-type: text/plain');
    	print "Error occurred:\n\n". print_r($e,true);
    }
*/
}

register_shutdown_function('unlock_with_error', E_ERROR);

class Project extends DOMDocument {
	private static $projects = array();

	public static function reload_project($project_ID = NULL) {
		unset(self::$projects[$project_ID]);
		return self::$projects($project_ID, false);
	}
	
	public static function project($project_ID = NULL, $create = false) {
		if (!$project_ID) {
			global $ID;
			$project_ID = getNS($ID);
		}
		if (isset(self::$projects[$project_ID]))
			return self::$projects[$project_ID];
		$name = noNS($project_ID);
		$project_file = DOKU_DATA . implode('/', explode(':', $project_ID))
			. "/$name.project";
    	if (file_exists($project_file)) {
    		$project = unserialize(file_get_contents($project_file));
    		if (!method_exists($project, 'version') || 
    				$project->version() != PROJECTS_VERSION) {
    			$project = new Project($project_ID);
    			$project->rebuild();
    		}
    	}
    	else if ($create)
    		$project = new Project($project_ID);
    	else return NULL;
    	self::$projects[$project_ID] = $project;
    	return $project;
	}

	// the namespace ID of the project
	private $ID = NULL;
	// the path to the project dir
	private $project_path = NULL;
	// the path to the project file
	private $project_file = NULL;
	// the list of files defined in the project
	private $files = array();
	// the list of errors
	private $errors = array();
	// whether the content is modified
	private $modified = false;
	// mutex
	private $mutex = NULL;
	// version string
	private $version_string = NULL;
	
	/**
	 * The constructor, this creates a new project.
	 * Taking an array that specifies the path a project.
	 * This array is created from the wiki namespace path
	 */
	public function __construct($ID) {
		$this->ID = $ID;
		$this->project_path = DOKU_DATA . implode('/', explode(":", $ID)) . '/';
	    $this->project_file = $this->project_path . 
	    	noNS($this->ID) . '.project';
	    $this->mutex = new Mutex($this->project_file);
		$this->version_string = PROJECTS_VERSION;
		$this->create();
	}

	/**
	 * The destructor. saves project is not saved
	 */
	function __destruct() {
		$this->save_project(false);
//		$this->mutex->release();
	}

	public function version() {
		if (isset($this->version_string))
			return $this->version_string; 
		return NULL;
	}
	/** 
	 * create a project
	 *
	 */
	protected function create() {
		// make sure the parent exists
		$parent = $this->parent(true);
		// create the project dir
	    @mkdir($this->project_path, PROJECTS_PERMISSIONS, true);
	    // create an empty project file
	    $this->modified = true;
		$this->save_project();
	}

	public function path() { return $this->project_path; }
	public function project_file() { return $this->project_file; }
	public function name() { return $this->ID; }

	// return the page id of a file with given name
	public function id($name) { 
		if ($this->file($name) == NULL) return NULL;
		return $this->ID . ":$name"; 
	}

	public function parent($create = false) {
		$parent = getNS($this->ID);
		if (!$parent) return NULL;
		return self::project(getNS($this->ID), $create);
	}

	/**
	 * save the project
	 *
	 */
	protected function save_project() {
		if (!$this->modified) return;
		$this->modified = false;
		file_put_contents($this->project_file, serialize($this));
	}

	/**
	 * delete this project
	 *
	 */
	public function delete() {
		delete_dir($this->project_path);
	}
	
	public function files() {
		return $this->files;
	}

	public function file($name) {
		if (!isset($this->files[$name])) return NULL;
		return $this->files[$name];
	}

	public function errors() {
		return $this->errors;
	}
	
	public function error($name) {
		if (isset($this->errors[$name])) return $this->errors[$name];
		return NULL;
	}
	
	public function changed() { return $this->changed; }

	// remove a file from the project
	public function remove_file($name) {
		if (!$this->remove_file_without_remake($name)) return false;
		return $this->remake();
	}

	private function remove_file_without_remake($name) {
		if (!$this->mutex->acquire()) return false;
		if (isset($this->files[$name])) {
			$this->files[$name]->delete($this->project_path);
			unset($this->files[$name]);
		}
		if (isset($this->errors[$name])) unset($this->errors[$name]);
		// if this file is in the changed list, drop it from the list
		$key = array_search($name, $this->changed);
		if ($key !== false) unset($this->changed[$key]);
		$this->modified = true;
		$this->save_project();
		$this->mutex->release();
		return true;
	}

	public function update_file($file) {
		$file = $this->handle($file);
		if ($file == NULL) return true;
		if (!$this->mutex->acquire()) return false;
		// let the plugins handle the file, if needed
		$name = $file->name();
		// check if it is a new file not registered in the project
		if (!isset($this->files[$name]))
			$this->files[$name] = ProjectFile::create($this, $file);
		else {
			$old = $this->files[$name];
			// check if two files are the same type, if not, delete the old
			if ($old->type() != $file->type()) {
				$this->mutex->release();
				if (!$this->remove_file_without_remake($name, false)) return false;
				return $this->update_file($file);
			}
			// check if two files are the same
			if ($old->equal($this->project_path, $file)) {
				$this->mutex->release();
				return true;
			}
			// copy to the project
			$old->copy($this->project_path, $file);
		}
		// this file has been changed, project needs to be remade
		$this->modified = true;
		$this->save_project();
		$this->mutex->release();
		$this->remake(array($name));
		return true;
	}

	// returns NULL if no default rule can make $name
	// otherwise return a target file that can make it
	public function handle($file) {
		$plugins = new Plugins(PROJECTS_PLUGINS_FILE_DIR);
		$handlers = $plugins->handlers($this, $file);
		if (!$handlers) return $file;
		reset($handlers);
		$handler = current($handlers);
		return $handler->handle($this, $file);
	}

	public function remake($files = array()) {
		$files = array_merge($files, $this->files_need_update());
		$files = array_keys(array_flip($files));
		if (!$this->mutex->acquire()) return false;
		$maker = new Maker($this);
		foreach ($this->files as $name => $file)
			if ($file->type() == CROSSLINK && !in_array($nme, $files))
				$files[] = $name;
		if ($files) $files = $maker->update($files);
		$this->errors = $maker->errors();
		// those that have failed to make will be deleted
		foreach ($this->errors as $name => $error) {
			$file = $this->files[$name];
			if ($file) {
				if ($file->is_target()) $file->delete($this->path());
			}
			else {
				$path = $this->project_path . $name;
				if (file_exists($path)) unlink($path);
			}
		}
		foreach ($files as $name)
			if (!isset($this->errors[$name])) {
				if (!isset($this->files[$name])) continue;
				$file = $this->files[$name];
				if ($file->is_target()) 
					$file->set_last_made_time($this->path());
			}
		$this->modified = true;
		// should not remake when saving. otherwise infinite loop
		$this->save_project();
		$this->mutex->release();
		return true;
	}
	
	public function clean($recursive = true) {
		if (!$this->mutex->acquire()) return false;
		if (($dh = opendir($this->project_path)) === false) {
			$this->mutex->release();
			return true;
		}
		while (($file = readdir($dh)) !== false) {
    		if ($file === '.' || $file === '..') continue;
    		$path = $this->project_path . $file;
			if (is_dir($path)) {
				$sub_project = self::project($this->name . ":" . $file);
				if ($sub_project === NULL) delete_dir($path . '/');
				else if ($recursive) {
					if ($sub_project->clean()) continue;
					$this->mutex->release();
					return false;
				}
			}
			// is not a project file and not a lock
			$file = $this->file($file);
			if ($file === NULL) {
				if ($path != $this->project_file . '.project.lock')
					@unlink($path);
			}
		}
		closedir($dh);
		foreach ($this->files as $file)
			if ($file->is_target()) $this->changed[] = $file->name();
		$this->modified = true;
		$this->save_project();
		$this->mutex->release();
		return true;
	}
	
	public function rebuild() {
		if (!$this->mutex->acquire()) return false;
		@unlink($this->project_file);
		$this->files = array();
		$this->errors = array();
		$this->modified = true;
		$this->save_project(false);
		$this->mutex->release();
		$this->clean();

		global $ID;
		$pages_path = DOKU_DATA . 'pages/' . implode('/', 
			explode(':', $this->ID)) . '/';
		if (($dh = opendir($pages_path)) === false) return true;
		while (($file = readdir($dh)) !== false) {
    		if ($file === '.' || $file === '..' || 
    			!has_extension($file, '.txt')) continue;
			if (is_dir($path)) continue;
			$file_id = substr($file, 0, -4);
			$this->sync($file_id);
 		}
		closedir($dh);
		return true;
	}

	public function unlock() {
		$this->mutex->release();
	}
	
	private function sync($id) {
		global $ID;
		// save $ID
   		$save_ID = $ID;
		$pages_path = DOKU_DATA . 'pages/' . implode('/', 
			explode(':', $this->ID)) . '/';
		$path = $pages_path . $id . '.txt';
   		$ID = $this->ID . ":" . $id;
   		// clear cache
   		$cache = new cache_renderer($ID, $path, 'metadata');
   		$cache->removeCache();
   		$cache = new cache_renderer($ID, $path, 'xhtml');
   		$cache->removeCache();
   		$cache = new cache_instructions($ID, $path);
   		$cache->removeCache();
   		p_cached_output($path, 'metadata', $ID);
   		// restore $ID
 		$ID = $save_ID;
	}
	
	private function files_need_update() {
		$changed = array();
		foreach ($this->files as $name => $file)
			if ($file->is_target() && $file->needs_update($this->path()))
				$changed[] = $name;
		return $changed;
	}

	public function subprojects() {
		$subprojects = array();
		if (($dh = opendir($this->project_path)) === false) return array();
		while (($file = readdir($dh)) !== false) {
			if ($file === '.' || $file === '..') continue;
			$path = $this->project_path . $file;
			if (!is_dir($path) || self::project($this->ID . ":$file", false) == NULL)
				continue;
			$subprojects[] = $file;
		}
		closedir($dh);
		return $subprojects;
	}

}

?>