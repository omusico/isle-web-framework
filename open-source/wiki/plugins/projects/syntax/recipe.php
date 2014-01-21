<?php
/**
 * The syntax plugin to handle <recipe> tags
 *
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/../lib/code_block.php');
require_once DOKU_PLUGIN . 'syntax.php';
require_once DOKU_INC . 'inc/common.php';

class syntax_plugin_projects_recipe extends CodeBlock {
    /**
     * return some info
     */
    function getInfo(){
    	$info = parent::getInfo();
    	$info['date'] = '2010-12-16';
    	$info['name'] = 'Project-file recipe Plugin';
        $info['desc'] = 'display the recipe tag in a project file';	
        return $info;
    }
 
    function tag_name() { return 'recipe'; }

	function language() { return 'bash'; }

    protected function add_content($file, $content) {
    	$file->add_recipe($content);
    }
}
?>