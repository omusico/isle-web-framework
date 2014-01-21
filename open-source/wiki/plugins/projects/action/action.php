<?php
/**
* projects Action Plugin: hijack the ACTION_ACT_PREPROCESS events for admin action
*
* @author     Junling Ma <junlingm@gmail.com>
*/

require_once(dirname(__FILE__).'/../lib/project.php');
require_once DOKU_PLUGIN.'action.php';

class action_plugin_projects_action extends DokuWiki_Action_Plugin {

    function getInfo(){
    return array(
        'author' => 'Junling Ma',
        'email'  => 'junlingm@gmail.com',
        'date'   => '2010-12-15',
        'name'   => 'Projects',
        'desc'   => 'hijack page write events',
        'url'    => 'http://www.math.uvic.ca/~jma'
        );
    }

    /**
    * Register its handlers with the DokuWiki's event controller
    */
    function register(&$controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this,
                'filter');
        $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this,
                'render');
    }

    private function wikitext($type) {
        $crosslink = "";
        switch ($type) {
            case SOURCE:
                $content = '<' . CONTENT_TAG . '></' . CONTENT_TAG . '>';
                break;
            case TARGET:
                $content = '<' . RECIPE_TAG . '></' . RECIPE_TAG . '>';
                break;
            case CROSSLINK:
                $content = "";
                $crosslink = " linkto=\"\"";
                break;
            default:
                return NULL;
        }
        $tag = "<project-file type=\"$type\"$crosslink/>\n";
        return $tag . $content;
    }
    
    /**
    * render the manage_files action
    *
    */
    function render(&$event, $param) {
        global $ID;
        $perm = auth_quickaclcheck($ID);
        if ($event->data != 'manage_files') return;
        $event->preventDefault();
        if ($perm < AUTH_READ) {
            echo '<h1>Error</h1><p>You do not have permission to view this page</p>';
            return;
        }
        $project = Project::project();
        if ($project == NULL) {
            echo '<h1>Error</h1>';
            $project = getNS($ID);
            $path = explode(":", $project);
            if (!$project || $path[0] != PROJECTS_NAMESPACE) {
                echo "<p>Projects wiki has to work under the " . PROJECTS_NAMESPACE
                    . " namespace</p>";
                return;
            }
            $parent = getNS($project);
            if (!$parent) {
                echo "<p>The namespace " . PROJECTS_NAMESPACE .
                    " has not been created yet!</p>";
                return;
            }
            echo "<p>This project does not exist!</p>";
            $name = noNS($parent);
            $link = DOKU_URL . "/doku.php?id=$parent:$name&do=manage_files";
            echo "<p>Go back to <a href=\"$link\">$parent</a>";
            return;
        }
        echo '<h1>Manage Project ' . $project->name() . '</h1>';
        $files = $project->files();
    ksort($files);
    echo "<h1>Manage Files</h1>";
    echo "<table>";
        foreach (array(SOURCE, TARGET, CROSSLINK) as $type) {
            $count = 0;
            $utype =ucfirst($type);
            echo "<tr><td></td><td></td><td><h2>$utype Files</h2></td></tr>";
        foreach ($files as $file) {
            if ($file->type() != $type) continue;
            echo "<tr>";
                $name = $file->name();
                echo "<td>";
                if ($file->is_target() && $perm > AUTH_READ)
                echo button_remake($project->id($name));
                echo "</td>";
                echo "<td>";
                if ($perm >= AUTH_DELETE)
                    echo button_delete($project->id($name));
                echo "</td>";
            echo "<td>";
			echo html_wikilink($project->id($name));
			if ($project->error($name) != NULL) 
                echo "<img src=\"" . DOKU_URL . "/lib/images/error.png\"></img>";
                echo "</td>";
            echo "</tr>";
            $count++;
        }
        if ($count == 0) 
                echo "<tr><td></td><td></td><td>No $type files in this project</td></tr>";
        }
        $files = $project->subprojects();
        if ($files) {
            sort($files);
            echo "<tr><td></td><td></td><td><h2>Subprojects</h2></td></tr>";
            foreach ($files as $file) {
                $id = $project->name() . ":$file";
                $link = DOKU_URL . "/doku.php?id=$id:$file&do=manage_files";
                echo "<tr><td></td><td></td><td>";
                echo "<a href=\"$link\">$file</a>";
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";

        $parent = $project->parent();
        if ($parent != NULL) {
            echo "<h1>Parent project</h1>";
            $name = $parent->name();
            $file = end(explode(":", $name));
            $link = DOKU_URL . "/doku.php?id=$name:$file&do=manage_files";
            echo "<a href=\"$link\">$name</a>";
        }
    
        if ($perm <= AUTH_READ) return;
        
    echo "<p/><h1>Create Files</h1>";
    $create = new Doku_Form("Create");
    
    $create->addHidden("do", "create");
    $create->addHidden("page", "projects_manage_files");
    $create->addHidden("id", $ID);

        $create->startFieldSet('Create a new file');
    $create->addElement(form_makeOpenTag("p"));
    $create->addElement(form_makeField('text', 'File name'));
    $create->addElement(form_makeCloseTag("p"));

    $create->addElement(form_makeOpenTag("p"));
    $create->addElement(form_makeRadioField('Type', SOURCE, "Source", "", "", array('checked' => "true"))); 
    $create->addElement(form_makeRadioField('Type', TARGET, 'Generated')); 
    $create->addElement(form_makeRadioField('Type', CROSSLINK, 'Crosslink')); 
    $create->addElement(form_makeCloseTag("p"));

    $create->addElement(form_makeButton("submit", '', "Create"));
    $create->endFieldSet();
    echo $create->getForm();

        echo "<h1>Create subproject</h1>";
        $subproject = new Doku_Form("Subproject");

        $subproject->addHidden("do", "create_subproject");
        $subproject->addHidden("page", "projects_manage_files");
        $subproject->addHidden("id", $ID);
        $subproject->startFieldSet('Create a new subproject');
        $subproject->addElement(form_makeOpenTag("p"));
        $subproject->addElement(form_makeField('text', 'Project name'));
        $subproject->addElement(form_makeCloseTag("p"));

        $subproject->addElement(form_makeButton("submit", '', "Create sub-project"));
        $subproject->endFieldSet();
        echo $subproject->getForm();

        echo "<h1>Clean up</h1>";
    $clean = new Doku_Form("Clean");
    $clean->addHidden("do", "clean");
    $clean->addHidden("page", "projects_manage_files");
    $clean->addHidden("id", $ID);
        $clean->startFieldSet('Clean the project');
    $clean->addElement(form_makeCheckboxField("Recursive"));
    $clean->addElement(form_makeButton("submit", "", "Clean"));
    $clean->endFieldSet();
    echo $clean->getForm();

    if ($perm < AUTH_ADMIN) return;

    echo "<h1>Rebuild the project</h1>";
    $rebuild = new Doku_Form("rebuild");
    $rebuild->addHidden("do", "rebuild");
    $rebuild->addHidden("page", "projects_manage_files");
    $rebuild->addHidden("id", $ID);
        $rebuild->startFieldSet('Rebuild the project');
    $rebuild->addElement(form_makeButton("submit", '', "Rebuild"));
    $rebuild->endFieldSet();
    echo $rebuild->getForm();
    }
    
    /**
    * an action has been called
    *
    */
    function filter(&$event, $param) {
        global $ACT;
        global $ID;
        global $TEXT;
        global $PRE;
        $perm = auth_quickaclcheck($ID);
        if ($event->data == 'manage_files') {
            $event->preventDefault();
            return;
        }
        $project = Project::project();
        if ($project == NULL) return;
        switch ($event->data) {
            case 'change_use' : 
                if ($perm <= AUTH_READ) {
                    msg('You do not have permission to change this file', -1);
                    $event->data = 'show';
                }
                else {
                    $range = $_REQUEST['range'];
                    $name = $_REQUEST['use'];
                    $slices = rawWikiSlices($range, $ID);
                    $TEXT = $slices[0] . '<use name="' . $name . '"/>' . $slices[2];
                }
                $event->data = 'save';
                break;
            case 'create' :
                if ($perm < AUTH_CREATE) {
                    msg('You do not have permission to create a file', -1);
                    if ($perm < AUTH_READ) {
                        $event->data = 'show';
                        break;
                    }
                    $event->data = 'manage_files';
                    $event->preventDefault();
                    break;
                }
                $text = $this->wikitext($_REQUEST['Type']);
                $name = $_REQUEST['File_name'];
                $ID = $project->name() . ':' . $name;
                if ($text == NULL) return;
                $TEXT = $text;
                $event->data = 'save';
                break;
            case 'remake' :
                if ($perm <= AUTH_READ) {
                    msg('You do not have permission to change this file', -1);
                    if ($perm < AUTH_READ) {
                        $event->data = 'show';
                        break;
                    }
                    $event->data = 'manage_files';
                    $event->preventDefault();
                }
                else {
                    set_media_file_revision_limit($this->getConf('media file revision limit'));
                    global $ID;
                    $id = $_REQUEST['id'];
                    if (getNS($name)) $ID = $id;
                    $id = noNS($id);
                    $project->remake(array($id));
                    $ID = $project->id($id);
                    $event->data = 'show';
                }
                $event->preventDefault();
                break;
            case "create_subproject" :
                if ($perm < AUTH_CREATE) {
                    msg('You do not have permission to create a subproject');
                    if ($perm < AUTH_READ) {
                        $event->data = 'show';
                        break;
                    }
                }
                else {
                    $name = $_REQUEST['Project_name'];
                    $project = Project::project($project->name() .':' . $name, true);
                    $ID = $project->name() . ':' . $name . ':manage_files';
                }
                $event->data = 'manage_files';
                $event->preventDefault();
                break;
            case 'clean' :
                if ($perm <= AUTH_READ) {
                    msg('You do not have permission to clean this project', -1);
                    if ($perm < AUTH_READ) {
                        $event->data = 'show';
                        break;
                    }
                }
                else {
                    $recursive = $_REQUEST['Recursive'];
                    if (!$project->clean($recursive))
                        msg('Other users are updating the project. Please clean later.');
                }
                $event->data = 'manage_files';
                $event->preventDefault();
                break;
            case 'rebuild' :
                $event->preventDefault();
                if ($perm <= AUTH_READ) {
                    msg('You do not have permission to rebuild this project', -1);
                    if ($perm < AUTH_READ) {
                        $event->data = 'show';
                        break;
                    }
                }
                else {
                    if ($project == NULL) 
                        $project = Project::project(NULL, true);
                    if (!$project->rebuild())
                        msg('Other users are updating the project. Please rebuild later.');
                }
                $event->data = 'manage_files';
                $event->preventDefault();
                break;
            case 'media' :
                $file_id = $_REQUEST['image'];
                if (!$file_id) return; 
                $project = Project::project(getNS($file_id));
                if ($project === NULL) return;
                $file = $project->path() . noNS($file_id);
                if (file_exists($file)) {
                    $event->preventDefault();
                    send_redirect(ml($file_id));
                    break;
                }
                return;
            case 'remove_tag' :
                $tag = $_REQUEST['tag'];
                if ($perm < AUTH_EDIT) {
                    msg("You do not have permission to edit this file", -1);
                    $event->data = 'show';
                    $event->preventDefault();
                    break;
                }
                $range = $_REQUEST['range'];
                $slices = rawWikiSlices($range, $ID);
                if (substr(strtoupper($slices[1]), 0, strlen($tag)+1) 
                    != '<' . strtoupper($tag)) {
                    msg("Missing the $tag tag?", -1);
                    $event->data = 'show';
                    $event->preventDefault();
                    break;
                }
                $text = $slices[0] .$slices[2];
                saveWikiText($ID, $slices[0] . $slices[2], "Remove $tag", FALSE);
                $event->data = 'show';
                break;
            case 'add_tag' :
                if ($perm < AUTH_EDIT) {
                    msg("You do not have permission to edit this file", -1);
                    $event->data = 'show';
                    $event->preventDefault();
                    break;
                }
                $tag = $_REQUEST['tag'];
                if (isset($_REQUEST['name'])) {
                    $name = $_REQUEST['name'];
                    $name = "name=\"$name\"";
                }
                else $name = '';
                $text = rawWiki($ID) . "<$tag $name";
                if (strtoupper($tag) == 'USE') 
                    $text .= '/>';
                else $text = "></$tag>";
                saveWikiText($ID, $text, "Add $tag", FALSE);
                $event->data = 'show';
                break;
            default:
                return;
        }
        $ACT = $event->data;
    }
    
}

?>