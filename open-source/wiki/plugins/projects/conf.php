<?php

/**
 * projects configurations
 *
 * @author     Junling Ma <junlingm@gmail.com>
 */

if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

define('DOKU_DATA',DOKU_INC.'data/');

define('PROJECTS_NAMESPACE', 'projects');
define('PROJECTS_DIR', DOKU_DATA.PROJECTS_NAMESPACE.'/');
define('PROJECTS_PERMISSIONS', 0755);

define('PROJECTS_PLUGINS_DIR', DOKU_PLUGIN . 'projects/plugins/');
define('PROJECTS_PLUGINS_FILE_DIR', 'file/');
define('PROJECTS_PLUGINS_TARGET_DIR', 'target/');
define('PROJECTS_PLUGINS_PREFIX', 'projects_plugin_');

define('PROJECTS_TAG', 'project-file');
define('SOURCE', 'source');
define('TARGET', 'generated');
define('CROSSLINK', 'crosslink');

define('USE_TAG', 'use');
define('RECIPE_TAG', 'recipe');
define('CONTENT_TAG', 'content');
define('MAX_PARALLEL_JOBS', 5);

define('PROJECTS_VERSION', '0.1.4');

global $version_file; 
global $changelog;
$version_file = 'http://rsv.math.uvic.ca/projects.php?version'; 
$changelog = 'http://rsv.math.uvic.ca/dokuwiki/doku.php/changelog';
?>