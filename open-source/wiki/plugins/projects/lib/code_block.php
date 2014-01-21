<?php
/**
 * The syntax plugin to handle <content> tags
 *
 */

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/tools.php');
require_once DOKU_PLUGIN . 'syntax.php';
require_once DOKU_INC . 'inc/geshi.php';

class CodeBlock extends DokuWiki_Syntax_Plugin {
    private $pos = NULL;
    private $text_pos = NULL;
    private $text = NULL;
    private $text_end = NULL;
    private $end = NULL;
    private $name = NULL;
    
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Junling Ma',
            'email'  => 'junlingm@gmail.com',
            'url'    => 'http://www.math.uvic.ca/~jma'
        );
    }

    function tag_name() { return NULL; }

    function getType() { 
        return 'protected';
    }
        
    function getSort() { 
        return 8; 
    }
    
    function syntax_mode() { return "plugin_projects_" . $this->tag_name(); }

    function connectTo($mode) {
        if ($this->tag_name() == NULL) return;
        $tag = $this->tag_name();
        $this->Lexer->addEntryPattern('<' . $tag . '.*?>(?=.*?</' . 
            $tag .'>)', $mode, $this->syntax_mode()); 
    }

    function postConnect() { 
        if ($this->tag_name() == NULL) return;
        $tag = $this->tag_name();
        $syntax_mode = "plugin_projects_$tag";
        $this->Lexer->addExitPattern('</' . $tag . '>', 
            $this->syntax_mode()); 
    }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $doc = $match . "</" . $this->tag_name() . ">";
                $xml = DOMDocument::loadXML($doc);
                $data = array();
                foreach ($xml->firstChild->attributes as $name => $node)
                    $data[$name] = $node->value;
                $data["pos"] = $pos;
                $data['text_pos'] = $pos + strlen($match);
                return $data;
            case DOKU_LEXER_EXIT :      
                return array("end" => $pos + strlen($match) - 1, 
                    'text_end' => $pos);
            case DOKU_LEXER_UNMATCHED :
                // skip the first blank line after the tag
                $l = strlen($match);
                $i = 0;
                while ($match[$i] == ' ' || $match[$i] == "\t" || $match[$i] == "\r") {
                    if ($i == $l) break;
                    $i++;
                }
                if ($i < $l && $match[$i] == "\n") $i++;
                $match = substr($match, $i);
                $pos = $pos + $i;
                // skip the trailing spaces and the last \n
                $l = strlen($match) - 1;
                if ($l >= 0 && $match[$l] == "\n") $l--;
                $match = substr($match, 0, $l + 1);
                  return array("code" => $match, 'pos' => $pos);
        }
        return NULL;
    }
 
    /**
     * Create output
     */
    
    protected function add_content($file, $content) {} 

    function render($mode, &$renderer, $data) {
        if (!is_array($data)) return;
        if ($mode == 'metadata') {
            if (!isset($data['code'])) return;
            global $ID;
            $file = $renderer->meta['ProjectFile'];
            if ($file != NULL)
                $this->add_content($file, $data['code']);
            return;
        }
        if ($mode == 'xhtml') {
            if (isset($data['highlight'])) 
                $this->lang = $data['highlight'];
            else if (!isset($this->lang) || !$this->lang) {
                global $ID;
                $this->lang = p_get_metadata($ID, 'ProjectFile:highlight', false);
                if (!isset($this->lang) || !$this->lang) $this->lang = $this->language();
                if (!isset($this->lang) || !$this->lang) $this->lang = "unspecified";
            }
            $this->render_xhtml($renderer, $data);
        }
    }

    private function render_xhtml(&$renderer, $data) {
        if (isset($data['text_pos'])) {
            $this->text_pos = $data['text_pos'];
            $this->name = $data['name'];
            return;
        }
        if (isset($data['code'])) {
            $this->text = $data['code'];
            $this->text_pos = $data['pos'];
            $this->text_end = $this->text_pos + strlen($this->text);
            return;
        }
        if (isset($data['end'])) {
            if ($this->text_end == NULL) $this->text_end = $data['text_end'];
            $this->end = $data['end'];
            // render
            $renderer->doc .= "<div class=\"code_block\">";
            $this->render_header($renderer, $data);
            render_code($renderer, $this->text, $this->lang);
            // end render
            $renderer->doc .= "</div>";
        }
    }
    
    function render_header(&$renderer, $data) {
        $type = ucfirst($this->tag_name());
        $name = $this->name;
        // header
        $renderer->doc .= "<div class=\"code_block_header\">";
        // type
        $renderer->doc .= "<div class=\"tag_header\">$type</div>";
        // name
        $renderer->doc .= "<div class=\"code_block_name\">$name&nbsp;";
        $this->render_buttons($renderer, $this->end);
        //end name
        $renderer->doc .= "</div>";
        //end header        
        $renderer->doc .= "</div>";
    }

    function render_buttons(&$renderer, $end) {
        // edit button
        global $ID;
        if (auth_quickaclcheck($ID) <= AUTH_READ) return;
        $renderer->doc .= "<div class=\"code_block_buttons\">";
        include_once(DOKU_INC . 'inc/html.php');
        $range = $this->text_pos . "-" . $this->text_end;
        $tag = $this->tag_name();
        $name = $this->name;
        if ($name) {
            $id = $tag . '_' . $name;
            $name = $tag . ' ' . $name;
        }
        else {
            $id = $tag;
            $name = $tag;
        }
        $data = array(
            "secid" => $id, 
            'target' => 'projects_wiki_file', 
            'name' => $name, 
            'range' => $range,
            'lang' => $this->lang);
        $renderer->doc .= trigger_event('HTML_SECEDIT_BUTTON', $data,
            'html_secedit_get_button');
        $renderer->doc .= "</div>";
    }
}
?>