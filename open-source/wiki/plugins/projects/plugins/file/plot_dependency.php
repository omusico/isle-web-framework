<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');

class projects_plugin_plot_dependency extends Plugin {
	/**
	 * The name of the parser, a human readable string, a unique identifier
	 */
	public function name() { return "Plot Dependency"; }
	
	/**
	 * whether this parser can make a given target
	 */
	public function can_handle($project, $file) { 
		if ($file->is_target()) return false;
		return has_extension($file->name(), ".plot"); 
	}

	public function handle($project, $file) { 
		$plot = new PlotDefinition($file->attributes());
		$plot->add_content($file->content());
		return $plot;
	}

}

class PlotDefinition extends SourceDefinition {
	private $properties = array();
	private $data = NULL;
	private $columns = array();
	private $line_styles = array();
	private $axes = array();
	private $valid = true;
	private $error = NULL;
	
	public function valid() { return $this->valid; }
	public function columns() { return $this->columns; }
	public function error() { return $this->error; }
	
	public function data_property($property) { 
		return $this->data[$property]; 
	}
	
	public function column($column) { return $this->columns[$column]; }

	public function property($name) { 
		if (!isset($this->properties[$name])) return NULL;
		return $this->properties[$name]; 
	}

	public function line_style($style_name, $property_name) {
		$style = $this->line_styles[$style_name];
		if (!$style) return NULL;
		return $style[$property_name];
	}

	public function axis_property($axis_name, $property_name) {
		$axis = $this->axes[$axis_name];
		if (!axis) return NULL;
		return $axis[$property_name];
	}

	public function __construct($attributes) {
		parent::__construct($attributes);
	}
	
	public function add_content($content) {
		parent::add_content($content);
		$lines = explode("\n", $content);
		foreach ($lines as $line) {
			$fields = explode('=>', $line);
			if (count($fields) < 2) continue;
			$name = trim($fields[0]);
			// lines started with # are comments
			if (substr($name, 1, 1) == '#') continue;
			$value = trim($fields[1]);
			if ($name == 'data') {
				$pairs = $this->pairs($value);
				$data_name = $pairs['name'];
				if ($data_name) $this->data = $pairs;
				continue;
			}
			if ($name == 'axis') {
				$pairs = $this->pairs($value);
				$axis_name = $pairs['name'];
				if ($axis_name) $this->axes[$axis_name] = $pairs;
				continue;
			}
			// columns are marked as a number or a range as number - number
			$range = explode('-', $name);
			if (count($range) == 1) $range[1] = $range[0];
			if (!is_numeric($range[0])) {
				$this->properties[$name] = $value;
				continue;
			}
			sort($range);
			// columns must be numbers
			foreach ($range as $column) {
				$column = trim($column);
				if (!is_numeric($column)) continue 2;
			}
			// parse the description which consists of comma separated
			// pairs of the form name1 = value1, name2 = value2 ...
			$pairs = $this->pairs($value);
			if (!isset($pairs['name'])) continue;
			$style = $pairs['name'];
			$this->line_styles[$style] = $pairs;
			if (isset($pairs['as'])) {
				$use = $pairs['as'];
				if ($use != 'x' && $use != 'y') continue;
			}
			else $use = NULL;
			for ($i = trim($range[0]); $i <= trim($range[1]); $i++)
				$this->columns[$i] = $style;
			if ($use == 'x') $this->properties['x_column'] = trim($range[0]); 
		}
		if (!$this->data) {
			$this->error = "No data file.";
			$this->valid = false;
			return;
		}
		$x_column = $this->property('x_column');
		if (!$x_column || !is_numeric($x_column)) {
			$this->error = "No data column specified for x-axis.";
			$this->valid = false;
			return;
		}
		if (!$this->columns) {
			$this->error = "No data column.";
			$this->valid = false;
			return;
		}
		if (!in_array($this->data, $this->dependency()))
			$this->add_dependency($this->data['name']); 
	}
	
	private function pairs($string) {
		$pairs = array();
		$matched = preg_match_all(
			'/ *(.*?) *= *(?i:([^\'"]*?)|\'(.*?)\'|"(.*?)") *(,|$)/', $string, $matches, PREG_SET_ORDER);
		foreach ($matches as $match)
			$pairs[$match[1]] = $match[2] . $match[3] . stripcslashes($match[4]);
		return $pairs;
	}
}
?>