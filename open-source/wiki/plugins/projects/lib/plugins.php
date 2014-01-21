<?php

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/tools.php');
require_once(dirname(__FILE__).'/file_definition.php');

class MakeRule extends Plugin {
	public function name() { return NULL; }
	public function can_handle($project, $file) { return false; }
	protected function recipe($project, $file) { return NULL; }
	protected function dependency($project, $file) { return $file->dependency(); }

	/** 
	 * returns a target project file that can make $name
	 */
	public function handle($project, $file) {
		$deps = $this->dependency($project, $file);
		$recipe = $this->recipe($project, $file);
		$def = new TargetDefinition(array('name' => $file->name(),
			'type' => TARGET));
		if (is_array($deps)) foreach ($deps as $dep) $def->add_dependency($dep);
		$def->add_recipe($recipe);
		return ProjectFile::create($project, $def);
	}
	
}

class Plugin {
	public function name() { return NULL; }
	public function can_handle($project, $file) { return false; }
	public function handle($project, $file) { return $file; }

	protected function replace_extension($name, $from, $to) { 
		return substr($name, 0, -strlen($from)) . $to;
	}
}

class Plugins {
	private $plugins = array();

	// $dir is which dir the plugins reside, 
	// $prefix is the prefix of the class name
	// a plugin has the name in the form of prefix_filename
	public function __construct($dir) {
		$dir = PROJECTS_PLUGINS_DIR . $dir;
		$dh = opendir($dir);
		if ($dh == false) return;
		while (($file = readdir($dh)) != false) {
			if (!has_extension($file, '.php')) continue;
			include_once($dir . $file);
			$name = explode('.', $file);
			array_pop($name);
			$class = PROJECTS_PLUGINS_PREFIX . implode('_', $name);
			if (!class_exists($class)) continue;
			$plugin = new $class();
			if ($plugin != NULL) $this->plugins[$plugin->name()] = $plugin;
		}
		closedir($dh);
	}
	
	public function handlers($project, $file) {
		$handlers = array();
		foreach ($this->plugins as $key => $plugin)
			if ($plugin->can_handle($project, $file)) 
				$handlers[$key] = $plugin;
		return $handlers;
	}
}

?>