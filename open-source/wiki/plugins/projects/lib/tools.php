<?php

require_once(dirname(__FILE__).'/../conf.php');

function wiki_debug($name, $obj = "", $hsc = false) {
    if (is_scalar($obj)) {
        if ($hsc)
            $obj = htmlspecialchars($obj);
        echo "<p>" . "$name = $obj", "</p>";
    }
    else {
        $obj = print_r($obj, true);
        if ($hsc)
            $obj = htmlspecialchars($obj);        
        echo "<pre> $name = ", $obj, "</pre>";
    }
}

// recursively delete all the files
function delete_dir($dir) {
    if (($dh = opendir($dir)) != false) {
        while (($file = readdir($dh)) !== false) {
            if ($file === '.' || $file === '..') continue;
            $file = $dir . $file;
            if (is_dir($file))
                delete_dir($file . '/');
            else
                unlink($file);
        }
        closedir($dh);
    }
    rmdir($dir);
}

function html_color_to_RGB($color) {
    if (strlen($color) == 7 && substr($color, 0, 1) != '#') 
        return array(0, 0, 0);
    $R = hexdec(substr($color, 1, 2));
    $G = hexdec(substr($color, 3, 2));
    $B = hexdec(substr($color, 5, 2));
    return array('R' => $R, 'G' => $G, 'B' => $B);
}

function has_extension($name, $ext) {
    $n = strlen($ext);
    $l = strlen($name);
    $tail = substr($name, $l - $n);
    return stristr($tail, $ext) != false;
}

function replace_extension($name, $from, $to) { 
    return substr($name, 0, -strlen($from)) . $to;
}

function action_button($button_name, $action = '', $hidden = NULL) {
    global $ID;
    $form = new Doku_Form('Form_' . $button_name);
    if (!$action) $action = $button_name;
    $form->addHidden('do', $action);
    if (is_array($hidden)) foreach ($hidden as $key => $value)
        $form->addHidden($key, $value);
    $form->addElement(form_makeButton('submit', '', $button_name));
    return $form->getForm();
}

function render_code(&$renderer, $code, $lang) {
    // code
    $renderer->doc .= "<div class=\"code_block_code\">";
    $geshi = new GeSHi($code, $lang);
    $geshi->set_header_type(GESHI_HEADER_DIV);
    $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
    $renderer->doc .= $geshi->parse_code();
    $renderer->doc .= "</div>";
}

function button_delete($ID) {
    global $REV;
    if (auth_quickaclcheck($ID) < AUTH_DELETE || $REV) return '';
    return action_button('Delete', 'save', array('id' => $ID, 'wikitext' => '', 'summary' => "delete $ID"));
}

function button_remake($ID) {
    global $REV;
    global $INFO;
    if (!$INFO['writable'] || $REV) return '';
    return action_button('Remake', 'remake', array('id' => $ID));
}

function button_remove($range, $tag) {
    global $ID;
    global $REV;
    global $INFO;
    if (!$INFO['writable'] || $REV) return '';
    return action_button('Remove', 'remove_tag', array(
        'tag' => $tag,
        'range' => $range
    ));
}

function button_add($button_name, $tag, $name="") {
    global $ID;
    global $REV;
    global $INFO;
    if (!$INFO['writable'] || $REV) return '';
    return action_button($button_name, 'add_tag', array(
        'tag' => $tag,
        'name', $name
    ));
}

function set_media_file_revision_limit($limit) {
    global $media_file_revision_limit;
    if (!$limit) $limit = '0';
    $limit = trim(strtoupper($limit));
    switch ($limit) {
        case '0':
        case 'OFF': 
        case 'FALSE':
        case 'NULL':
            $media_file_revision_limit = 0;
            break;
        default:
            if (!preg_match('/(\d+) *(B|KB|MB|GB|TB)?/', $limit, $matches)) {
                msg('Cannot understand the configuration setting for "media file revision limit"', -1);
                break;
            }
            $size = $matches[1];
            if (count($matches) > 2) switch ($matches[2]) {
                case 'TB':
                    $size *= 1024;
                case 'GB':
                    $size *= 1024;
                case 'MB':
                    $size *= 1024;
                case 'KB':
                    $size *= 1024;
            }
            $media_file_revision_limit = $size;
    }
}

function copy_updated_file($from, $to) {
    return copy($from, $to);
}

function file_mimetype($id, $project) {
    list($ext, $mime, $dl) = mimetype($id, false);
    if ($ext === false)
        $mime = 'application/octet-stream';
    if ($mime == 'application/octet-stream') {
        $path = $project->path() . noNS($id);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);
    }
    return $mime;
}

?>