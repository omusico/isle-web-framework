<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');
require_once(dirname(__FILE__).'/../../lib/project_file.php');

class projects_plugin_media extends MakeRule {
	/**
	 * The name of the rule, a human readable string, a unique identifier
	 */
	public function name() { return "MediaLibrary"; }
	
	/**
	 * whether this rule can make a given target
	 */
	public function can_handle($project, $file) {
      return file_exists(mediaFN($project->name() . ':' . $file->name())); 
	}

	/** 
	 * returns a recipe to link to the media file
	 */
	protected function recipe($project, $file) {
      $name = $file->name();
      $id = $project->name() . ':' . $name;
      $media_name = mediaFN($id);
      if ($media_name) 
		return "ln -s $media_name $name";
      return NULL;
	}

}

?>