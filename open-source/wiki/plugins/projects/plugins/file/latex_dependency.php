<?php

require_once(dirname(__FILE__).'/../../lib/plugins.php');

class projects_plugin_latex_dependency extends Plugin {
    /**
     * The name of the parser, a human readable string, a unique identifier
     */
    public function name() { return "Latex Dependency"; }
    
    /**
     * whether this parser can make a given target
     */
    public function can_handle($project, $file) { 
        if ($file->is_target()) return false;
        return has_extension($file->name(), ".tex"); 
    }

    /** 
     * The files used in this file
     */
    private function dependency($content) { 
        $inputs = find_command('input', $content);
        $includes = find_command('include', $content);
        $graphs = find_command('includegraphics', $content, ".pdf");
        $bibs = find_command('bibliography', $content, ".bib");
        $all = array_merge($inputs, $includes, $graphs, $bibs);
        return array_keys(array_flip($all));
    }    

    public function handle($project, $source_file) { 
        $deps = $source_file->dependency(); 
        $uses = array_diff($this->dependency($source_file->content()), 
            $deps);
        foreach ($uses as $use) $source_file->add_dependency($use);
        return $source_file;
    }

}

function match_command($command, $content) {
    $parameters = '(?i:\[.*?\])?';
    $pattern = "/\\\\$command *$parameters *\{ *(?P<content>.*?) *\}/";
    $matched = preg_match_all($pattern, $content, $matches);
    if ($matched == 0) return NULL;
    return $matches;
}

function find_command($command, $content, $file_extension = ".tex") {
    $deps = array();
    $matches = match_command($command, $content);
    if ($matches == NULL) return array();
    foreach ($matches['content'] as $match)
        if (has_extension($match, $file_extension))
            $deps[] = $match;
        else
            $deps[] = $match . $file_extension;
    return $deps;
}
