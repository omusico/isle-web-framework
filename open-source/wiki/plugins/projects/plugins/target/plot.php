<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');
require_once(dirname(__FILE__).'/../../lib/project_file.php');
require_once(dirname(__FILE__).'/../file/plot_dependency.php');
require_once(dirname(__FILE__).'/../../pchart/pchart/pchart.php');
require_once(dirname(__FILE__).'/../../pchart/pchart/pdata.php');

class projects_plugin_plot extends MakeRule {
	/**
	 * The name of the rule, a human readable string, a unique identifier
	 */
	public function name() { return "Plot"; }
	
	/**
	 * whether this rule can make a given target
	 */
	public function can_handle($project, $file) {
		if (!has_extension($file->name(), '.png')) return false;
		$plot = $this->replace_extension($file->name(), '.png', '.plot');
		return $project->file($plot) != NULL; 
	}

	/** 
	 * returns a project file that can make $name
	 */
	public function handle($project, $file) {
		$plot = $this->replace_extension($file->name(), '.png', '.plot');
		$source = new PlotDefinition(array('type' => SOURCE, 
			'name' => $plot));
		$source->add_content(file_get_contents($project->path() . $plot));
		$target = new PlotTarget($project, $file, $source);
		return $target;
	}

}

class PlotTarget extends ProjectGenerated {
	private $plot = NULL;
	public function __construct($project, $file, $source) {
		$deps = $file->dependency();
		if (!in_array($source->name(), $deps)) $deps[] = $source->name();
		$this->deps = $deps;
		$this->name = $file->name();
		$this->plot = $source;
	}
	
	public function makable() { return true; }
	
	public function make($working_path) {
		// This will import the file http://my.domain.com/myData.csv using each column as a serie  
		if (!$this->plot->valid()) {
			$file = $this->plot->name();
			$error = $this->plot->error();
			$this->log($working_path, "Plot file $file is invalid: $error");
			return 1;
		}
		$data = new pData();
		$columns = array();
		$all_columns = array();

		$x_column = $this->plot->property('x_column') - 1;
		if ($x_column === NULL) $x_column = 0; 
		$data_file = $this->plot->data_property('name');
		$deliminator = $this->plot->data_property('deliminator');
		$has_header = $this->plot->data_property('header') == 'yes';
		foreach ($this->plot->columns() as $column => $name) {
			if ($column != $x_column + 1) $columns[] = $column - 1;
			$all_columns[] = $column - 1;
		}
		$data->ImportFromCSV($working_path . $data_file, 
			$deliminator, $all_columns, $has_header);
		foreach ($columns as $column) {
			$name = $this->plot->column($column + 1);
			$data->AddSerie('Serie' . $column);
			$data->SetSerieName($name, "Serie" . $column);
		}
		$max_col = -1;
		$max_val = NULL;
		foreach ($data->GetData() as $record) {
			foreach ($columns as $column) {
				$point = $record['Serie' .$column];
				if ($max_val === NULL || $point > $max_val) {
					$max_val = $point;
					$max_col = $column;
				}
			}
		}
		$x_label = $this->plot->axis_property('x', 'label');
		if ($x_label) 
			$data->SetXAxisName($x_label);
		else
			$data->SetXAxisName($this->plot->column($x_column + 1));
		$x_unit = $this->plot->axis_property('x', 'unit');
		if ($x_unit) $data->SetXAxisUnit($x_unit);
		$y_label = $this->plot->axis_property('y', 'label');
		reset($columns);
		if ($y_label) 
			$data->SetYAxisName($y_label);
		else
			$data->SetYAxisName($this->plot->column(current($columns) + 1));
		$y_unit = $this->plot->axis_property('y', 'unit');
		if ($y_unit) $data->SetyAxisUnit($y_unit);

		$width = $this->plot->property('width');
		if (!$width) $width = 640; 
		$height = $this->plot->property('height');
		if (!$height) $height = 480; 
		$plot = new pChart($width, $height);
		$font_name = $this->plot->property('font-name');
		if (!$font_name) $font_name = 'tahoma.ttf';
		$font_name = 'lib/plugins/projects/pchart/fonts/' . $font_name;
		$font_size = $this->plot->property('font_size');
		if (!$font_size) $font_size = 12;
		$plot->setFontProperties($font_name, $font_size);
		$h = $font_size + 10;
		$left_margin = 0;
		foreach ($data->GetData() as $record) {
	    	$position   = imageftbbox($font_size, 0, $font_name, 
	    		$record['Serie' . $max_col]);
	    	$text_width  = $position[2] - $position[0];
			if ($text_width > $left_margin) $left_margin = $text_width;
		}
		$left_margin += 2 * $h;
		$plot->setGraphArea($left_margin, 2 * $h, $width - $h, $height - 2 * $h);  
		$background = $this->plot->property('background');
		if (!$background) 
			$background = array('R' => 255, 'G' => 255, 'B' => 255);
		else $background = html_color_to_RGB($background);
		$plot->drawGraphArea($background['R'], $background['G'], 
			$background['B']);
		// pick the largest scale
		$plot->drawXYScale($data->GetData(), $data->GetDataDescription(),
			'Serie' . $max_col, 'Serie' . $x_column,
			0, 0, 0, TRUE, 0, 0);
		$line_no = 0;
		$colors = array();
		foreach ($columns as $column) {
			$name = $this->plot->column($column + 1);
			$style = $this->plot->line_style($name, 'style');
			$line_color = $this->plot->line_style($name, 'color');
			if (!$line_color) 
				$colors[$name] = array('R' => 0, 'G' => 0, 'B' => 0);
			else $colors[$name] = html_color_to_RGB($line_color);
			$plot->setColorPalette($line_no, $colors[$name]['R'],
				$colors[$name]['G'], $colors[$name]['B']);
			if (!$style || $style == 'line' || $style == 'both') {
				$line_width = $this->plot->line_style($name, 'width');
				if (!$line_width) $line_width = 1;
				$dot_size = $this->plot->line_style($name, 'dot-size');
				if (!$dot_size) $dot_size = 0;
				$plot->setLineStyle($line_width, $dot_size);
				$plot->drawXYGraph($data->GetData(), 
					$data->GetDataDescription(), 
					'Serie' . $column, 'Serie' . $x_column, $line_no);
			}
			if ($style == 'point' || $style == 'both') {
				$radius = $this->plot->line_style($name, 'radius');
				if (!$radius) $radius = 5;
				$plot->drawXYPlotGraph($data->GetData(), 
					$data->GetDataDescription(), 
					'Serie' . $column, 'Serie' . $x_column, $line_no,
					$radius, $radius - 2);
			}
			$line_no++;
		}
		
		$title = $this->plot->property('title');

		foreach ($columns as $column)
			$data->removeSerie('Serie' . $column);
		$in_legend = array();
		$description = $data->GetDataDescription();
		$description['Description'] = array();
		$palette_id = 0;
		foreach ($columns as $column) {
			$name = $this->plot->column($column + 1);
			if (in_array($name, $in_legend)) continue;
			$in_legend[] = $name;
			$description['Description']['Serie' . $column] = $name;
			$plot->setColorPalette($palette_id, $colors[$name]['R'],
				$colors[$name]['G'], $colors[$name]['B']);
			++$palette_id;
		}
		$legend_box_size =$plot->getLegendBoxSize($description);
		$legend_position = $this->plot->property('legend-position');
		if (!$legend_position) $legend_position = 'top-left';
		switch ($legend_position) {
			case 'top-left':
				$legend_left = 0;
				$legend_top = 0;
				break;
			case 'top':
				$legend_left = ($width - $h - $left_margin - $legend_box_size[0]) / 2;
				$legend_top = 0;
				break;
			case 'top-right':
				$legend_left = $width - $left_margin - $h - $legend_box_size[0];
				$legend_top = 0;
				break;
			case 'left':
				$legend_left = 0;
				$legend_top = ($height - 4 * $h - $legend_box_size[1]) / 2;
				break;
			case 'center':
				$legend_left = ($width - $h - $left_margin - $legend_box_size[0]) / 2;
				$legend_top = ($height - 4 * $h - $legend_box_size[1]) / 2;
				break;
			case 'right':
				$legend_left = $width - $left_margin - $h - $legend_box_size[0];
				$legend_top = ($height - 4 * $h  - $legend_box_size[1]) / 2;
				break;
			case 'bottom-left':
				$legend_left = 0;
				$legend_top = $height - 4 * $h - $legend_box_size[1];
				break;
			case 'bottom':
				$legend_left = ($width - $h - $left_margin - $legend_box_size[0]) / 2;
				$legend_top = $height - 4 * $h - $legend_box_size[1];
				break;
			case 'bottom-right':
				$legend_left = $width - $left_margin - $h - $legend_box_size[0];
				$legend_top = $height - 4 * $h - $legend_box_size[1];
				break;
		}
		$plot->drawLegend($left_margin + $legend_left, 2 * $h + $legend_top, $description, 
			255, 255, 255, 100, 100, 100, 0, 0, 0, true);
		$plot->drawTitle($h, 0, $title, 0, 0, 0, $width-$h, $h * 2, 100);  
		$plot->Render($this->path($working_path));  

		$file_name =$this->plot->name();
		$plot_name =$this->name();
		$this->log($working_path, "Successfully plotted $file_name as $plot_name\ntitle: $title\n");
		return 0;
	}
}

?>