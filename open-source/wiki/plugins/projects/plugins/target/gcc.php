<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');

class projects_plugin_gcc extends MakeRule {
	/**
	 * The name of the rule, a human readable string, a unique identifier
	 */
	public function name() { return "gcc"; }
	
	/**
	 * whether this rule can make a given target
	 */
	public function can_handle($project, $file) {
		$name = $file->name();
		if (!has_extension($name, ".o")) return false;
		$cpp = $this->replace_extension($name, ".o", ".cpp");
		if ($project->file($cpp) != NULL) return true;
		$c = $this->replace_extension($name, ".o", ".c");
		if ($project->file($c) != NULL) return true;
		return false;
	}

	/** 
	 * The dependent files needed by this rule
	 */
	protected function dependency($project, $file) { 
		$deps = $file->dependency();
		$cpp = $this->replace_extension($file->name(), ".o", ".cpp");
		if ($project->file($cpp) != NULL && !in_array($cpp, $deps)) {
			$deps[] = $cpp;
			return $deps;
		}
		$c = $this->replace_extension($file->name(), ".o", ".c");
		if ($project->file($c) != NULL && !in_array($c, $deps)) {
			$deps[] = $c;
			return $deps;
		}
		return $deps;
	}
	
	/**
	 * The default recipe
	 */
	protected function recipe($project, $file) {
		$cpp = $this->replace_extension($file->name(), ".o", ".cpp");
		if ($project->file($cpp) != NULL)
			return "g++ -O3 -c -I. -o " . $file->name() . " $cpp";
		$c = $this->replace_extension($file->name(), ".o", ".c");
		if ($project->file($c) != NULL)
			return "gcc -O3 -c -I. -o " . $file->name() . " $c";
		return NULL;
	}
}

?>