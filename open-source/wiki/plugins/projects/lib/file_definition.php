<?php
/**
 * FileDefinition and its children: classes that declare a wiki page to 
 * be a project file with the same name.
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/tools.php');

class FileDefinition {
	// the attributes like name and type
	private $attributes = array();
	// the dependency
	private $deps = array();
	// the beginning and end position in the wiki page
	public $end = NULL;
	public $position = NULL;

	public function __construct($attributes) {
		$this->attributes = $attributes;
	}
	
	public function attributes() { return $this->attributes; }

	public function attribute($name) {
		if (!isset($this->attributes[$name])) return NULL;
		return $this->attributes[$name];
	}

	public function name() { return $this->attribute('name'); }
	public function type() { return $this->attribute('type'); }
	
	public function dependency() { return $this->deps; }
	public function add_dependency($name) { 
		if (!in_array($name, $this->deps)) $this->deps[] = $name; 
	}
	
	public static function parse($name, $tag) {
		$xml = DOMDocument::loadXML($tag);
		if ($xml == false) return NULL;
		$attributes = array();
		foreach ($xml->firstChild->attributes as $attribute)
			$attributes[$attribute->name] = $attribute->value;
		$attributes['name'] = $name;
		if (!isset($attributes['type'])) return NULL; 
		$type = $attributes['type'];
		if ($type == SOURCE) 
			return new SourceDefinition($attributes);
		if ($type == TARGET) 
			return new TargetDefinition($attributes);
		if ($type == CROSSLINK) 
			return new CrosslinkDefinition($attributes);
		return NULL;
	}
}

Class SourceDefinition extends FileDefinition {
	private $content = NULL;
	
	public function is_target() { return false; }
	public function content() { return $this->content; }
	public function add_content($content) { 
		$this->content .= $content; 
	}
}

Class TargetDefinition extends FileDefinition {
	private $recipe = NULL;
	public function is_target() { return true; }
	public function recipe() { return $this->recipe; }
	public function add_recipe($recipe) { 
		$this->recipe .= "\n" . $recipe;
		trim($this->recipe);
	}
}

Class CrossLinkDefinition extends FileDefinition {
	public function is_target() { return true; }
	public function __construct($attributes) {
		if (!isset($attributes['linkto'])) 
			$attributes['linkto'] = "";
		else {
			$linkto = $attributes['linkto'];
			if (strpos($linkto, "/")) $linkto = ""; 
			else if (strpos($linkto, "\\")) $linkto = ""; 
			if ($linkto) {
				$path = explode(":", $linkto);
				if ($path[0] == '[media]') array_shift($path);
                if (count($path) > 1 && $path[0] != PROJECTS_NAMESPACE) $linkto = "";
			}
			$attributes['linkto'] = $linkto;
		}
		parent::__construct($attributes);
	}

	public function crosslink() { 
		return trim($this->attribute('linkto'));
	}
	public function recipe() { return NULL; }
}