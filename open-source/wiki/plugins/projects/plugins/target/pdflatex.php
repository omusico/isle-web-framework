<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');

class projects_plugin_pdflatex extends MakeRule {
	/**
	 * The name of the rule, a human readable string, a unique identifier
	 */
	public function name() { return "PDFLatex"; }
	
	/**
	 * whether this rule can make a given target
	 */
	public function can_handle($project, $file) {
		if (!has_extension($file->name(), ".pdf")) return false;
		$tex = $this->replace_extension($file->name(), ".pdf", ".tex");
		return $project->file($tex) != NULL;
	}

	/** 
	 * The dependent files needed by this rule
	 */
	protected function dependency($project, $file) { 
		$tex = $this->replace_extension($file->name(), ".pdf", ".tex");
		$deps = $file->dependency();
		if (!is_array($deps)) $deps = array();
		if (!in_array($tex, $deps))
	 		$deps[] = $tex;
		return $deps;
	}
	
	/**
	 * The default recipe
	 */
	protected function recipe($project, $file) {
		$tex = $this->replace_extension($file->name(), ".pdf", ".tex");
		$base = substr($tex, 0, -4);
		$bibtex = "";
		$pdflatex = "pdflatex -interaction=nonstopmode $tex\n";
		$texfile = $project->file($tex);
		if (!$texfile) return '';
		foreach ($texfile->dependency() as $dep)
			if (has_extension($dep, ".bib")) {
				$bibtex = "bibtex $base\n";
				break;
			}
		return "rm -f $base.aux" . "\n" . 
			$pdflatex . $bibtex . $pdflatex;
	}
}

?>