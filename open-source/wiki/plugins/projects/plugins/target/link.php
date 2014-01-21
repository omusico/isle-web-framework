<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');

class projects_plugin_link extends MakeRule {
	/**
	 * The name of the rule, a human readable string, a unique identifier
	 */
	public function name() { return "link"; }
	
	/**
	 * whether this rule can make a given target
	 */
	public function can_handle($project, $file) {
		$deps = $file->dependency();
		if (!$deps) return false;
		foreach ($deps as $dep) {
			if (has_extension($dep, ".o")) continue;
			if (has_extension($dep, ".a")) continue;
			return false;
		}
		return true;
	}

	/** 
	 * The dependent recipe
	 */
	protected function recipe($project, $file) {
		$deps = $file->dependency();
		$args = implode(" ", $deps);
		$name = $file->name();
		if (has_extension($name, ".a"))
			return "ar rcs $name $args";
		return "g++ -static -o $name $args";
	}
}

?>