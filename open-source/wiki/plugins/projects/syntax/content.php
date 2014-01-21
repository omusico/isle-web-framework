<?php
/**
 * The syntax plugin to handle <recipe> tags
 *
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/../lib/code_block.php');
require_once DOKU_PLUGIN . 'syntax.php';
require_once DOKU_INC . 'inc/geshi.php';

class syntax_plugin_projects_content extends CodeBlock {
    /**
     * return some info
     */
    function getInfo(){
    	$info = parent::getInfo();
    	$info['date'] = '2010-12-16';
    	$info['name'] = 'Project-file content Plugin';
        $info['desc'] = 'display the content tag in a project file';
        return $info;
    }
 
    function tag_name() { return 'content'; }

	function language() {
		global $ID;
		$parts = explode(".", $ID);
		if (count($parts) <= 1) return NULL;
		$extension = array_pop($parts);
		$lang = GeSHi::get_language_name_from_extension($extension);
		return $lang;
	}
	
    protected function add_content($file, $content) {
    	$file->add_content($content);
    }
}
?>