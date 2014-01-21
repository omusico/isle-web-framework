<?php
/**
 * The syntax plugin to handle <use> tags
 *
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/../lib/tools.php');
require_once DOKU_PLUGIN . 'syntax.php';
require_once DOKU_INC . 'inc/common.php';

class syntax_plugin_projects_use extends DokuWiki_Syntax_Plugin {
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Junling Ma',
            'email'  => 'junlingm@gmail.com',
            'date'   => '2010-12-16',
            'name'   => 'Project-file use Plugin',
            'desc'   => 'display project files',
            'url'    => 'http://www.math.uvic.ca/~jma',
        );
    }
 
    function getType() { 
        return 'substition';
    }
        
    function getSort() { 
    	return 8; 
    }
    
    function connectTo($mode) {
    	$this->Lexer->addSpecialPattern('<' . USE_TAG . ' .*?/>',
    		$mode, 'plugin_projects_use'); 
    }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        if ($state != DOKU_LEXER_SPECIAL) return NULL;
 		$xml = DOMDocument::loadXML($match);
 		if (!$xml) return NULL;
 		$name = $xml->firstChild->getAttribute('name');
        return array("use" => trim($name), "pos" => $pos, 
        	"end" => $pos + strlen($match) + 1);
    }
 
    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
		if ($data == NULL) return;
        switch ($mode) {
        	case 'metadata':
				global $ID;
				$file = $renderer->meta['ProjectFile'];
				if ($file != NULL) {
					$use = $data['use'];
					if ($use) $file->add_dependency($data["use"]);
				}
				break;
        	case 'xhtml' :
        		$this->render_xhtml($renderer, $data);
        		break;
        }
    }

    private function button_rename_use($range) {
        global $ID;
        if (auth_quickaclcheck($ID) < AUTH_EDIT) return '';
        $self = noNS($ID);
		$project = Project::project();
        if ($project == NULL) return '';
        $files = array('');
        foreach (array_keys($project->files()) as $file) 
                if ($file != $self) $files[] = $file;
        $form = new Doku_Form("change_use");
        $form->addHidden('do', 'change_use');
        $form->addHidden('range', $range);
        $form->addElement(form_makeMenuField('use',  $files, '', '', '', '', 
                array("onchange" => "submit();")));
        return $form->getForm();
    }

	private function render_xhtml(&$renderer, $data) {
		if (!$data || !isset($data['use'])) return;
		$use = $data['use'];
		global $ID;
		$name = array_pop(explode(":", $ID));
		$project = Project::project();
		//use
		$renderer->doc .= '<div class="use">';
		//header
		$renderer->doc .= '<div class="tag_header">Use</div>';
		//use_use
		$renderer->doc .= '<div class="use_use">';
		$range = $data['pos'] . '-' . $data['end'];
		if (!$use) {
			$choices = array_diff(array_keys($project->files()), 
				array($name));
			array_unshift($choices, "");
			$renderer->doc .= $this->button_rename_use($range, $choices);
		}
		else {
			$use_id = $project->id($use);
			if ($use_id) 
				$renderer->internallink($project->id($use));
			else $renderer->doc .= "$use";
		}
		// end use_use
		$renderer->doc .= "</div>";
		$renderer->doc .= "<div class=\"spacer\"></div>";
		// buttons
		$renderer->doc .= "<div class=\"use_buttons\">";
		$renderer->doc .= button_remove($range, "Use");
		// end buttons
		$renderer->doc .= "</div>";
		if ($project != NULL) {
			$errors = $project->error($use);
			if ($errors) foreach ($errors as $error) 
				if ($error == 'undefined') {
					$render->doc .="<div class=\"error\">Do not know how to generate this file.</div>";
				}
			$errors = $project->error($name);
			if ($errors) foreach ($errors as $error)
				if (is_array($error) && isset($error['dependency'])
					&& $error['dependency'] == $use) {
					$renderer->doc .="<div class=\"error\">This file cannot be generated.</div>";
				}
		}
		$renderer->doc .= "<div class=\"use_content\"></div>";
		// closing use
		$renderer->doc .= "</div>";
	}	
}

?>