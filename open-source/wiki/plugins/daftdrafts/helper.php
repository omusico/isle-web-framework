<?php
/**
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jon Magne Bøe <jonmagneboe@hotmail.com>
 * @author i-net software <tools@inetsoftware.de>
 * @author Gerry Weissbach <gweissbach@inetsoftware.de>
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

class helper_plugin_daftdrafts extends DokuWiki_Plugin { // DokuWiki_Helper_Plugin

	var $functions = null;

	/**
	* Returns information about this plugin.
	*/
	function getInfo(){
		return array (
            'author' => 'Jon Magne Bøe',
            'date' => '2011-11-06',
            'name' => 'DaftDrafts (Helper Component)',
            'desc' => 'Marks pages as drafts, which hides them from unregistered users.',
            'url' => 'http://www.dokuwiki.org/plugin:daftdrafts',
        );
	}
  
	function getMethods(){
		$result = array();
		$result[] = array(
		  'name'   => 'acl_add',
		  'desc'   => 'Add an ACL Line into the acl.auth.php',
		  'params' => array('acl_scope' => 'string', 'acl_user' => 'string', 'acl_level' => 'string', 'additional' => 'string'),
		  'return' => array('success' => 'boolean'),
		);
		$result[] = array(
		  'name'   => 'acl_del',
		  'desc'   => 'Remove an ACL Line into the acl.auth.php',
		  'params' => array('acl_scope' => 'string', 'acl_user' => 'string', 'additional' => 'string'),
		  'return' => array('success' => 'boolean'),
		);
		return $result;
	}

	/**
	 * Adds a new acl-entry to conf/acl.auth.php, as well as lib/plugins/daftdrafts/daft.auth.php.
	 *
	 * @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	 * @author Frank Schubert <frank@schokilade.de>
	 * @author Gerry Weissbach <gweissbach@inetsoftware.de>
	 */
	function acl_add($acl_scope, $acl_user, $acl_level, $additional='DAFTDRAFTS'){
		global $AUTH_ACL;
		$acl_config = $AUTH_ACL;
		if ( empty($acl_config) ) $acl_config = file(DOKU_CONF.'acl.auth.php');
		$daftAcl = file(dirname(__FILE__).'/daft.auth.php');
		$acl_user = auth_nameencode($acl_user,true);
		if ( empty($acl_user) ) { return false; }
		if ( !empty($additional) ) {
			$additionalCheck = '\t#' . $additional;
			$additional = "\t#" . $additional;
		}
		//Checks that the acl_level is not higher than the permitted maximum for pages:
		if(strpos($acl_scope,'*') === false) {
			if($acl_level > AUTH_EDIT) $acl_level = AUTH_EDIT;
		}
		$existInDaft = false;
		$existInAcl = false;
		$acl_pattern = '^'.preg_quote($acl_scope,'/').'\s+'.$acl_user.'\s+[0-8].*' . $additionalCheck . '$';
		$acl_pattern_nocomment = '^'.preg_quote($acl_scope,'/').'\s+'.$acl_user.'\s+[0-8].*$';
		//Checks if this exists in daftAcl:
		if (preg_grep("/$acl_pattern/", $daftAcl)) {
			if (preg_grep("/$acl_pattern_nocomment/", $acl_config)) {
				return true;
			} else {
				$existInDaft = true;
			}
		} elseif (preg_grep("/$acl_pattern_nocomment/", $acl_config)) {
			$existInAcl = true;
		}
		if (!$existInDaft) {
			$daftAcl[] = "$acl_scope\t$acl_user\t$acl_level$additional\n"; //Adds acl-info to daftAcl
			io_saveFile(dirname(__FILE__).'/daft.auth.php', join('', $daftAcl));
		}
		if (!$existInAcl) {
			$acl_config[] = "$acl_scope\t$acl_user\t$acl_level$additional\n"; //Adds acl-info to acl_config
			$AUTH_ACL = $acl_config;
			io_saveFile(DOKU_CONF.'acl.auth.php', join('', $acl_config));
		}
		return true; //The function does not check if the files were actually saved.
	}

	/**
	 * Removes an acl-entry from conf/acl.auth.php
	 *
	 * @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	 * @author Frank Schubert <frank@schokilade.de>
	 * @author Gerry Weissbach <gweissbach@inetsoftware.de>
	 */
	function acl_del($acl_scope, $additional='DAFTDRAFTS'){
		global $AUTH_ACL;
		$acl_config = $AUTH_ACL;
		if ( empty($acl_config) ) $acl_config = file(DOKU_CONF.'acl.auth.php');
		$daftAcl = file(dirname(__FILE__).'/daft.auth.php');
		$additional = '\t#' . $additional;
		$acl_pattern = '^'.preg_quote($acl_scope,'/').'\s+.*\s+[0-8].*' . $additional . '$';
		$acl_pattern_nocomment = '^'.preg_quote($acl_scope,'/').'\s+.*\s+[0-8].*$';
		$new_config = preg_grep("/$acl_pattern_nocomment/", $acl_config, PREG_GREP_INVERT);
		$newDaftAcl = preg_grep("/$acl_pattern/", $daftAcl, PREG_GREP_INVERT);
		$AUTH_ACL = $new_config;
		if ($new_config != $acl_config) {
			io_saveFile(DOKU_CONF.'acl.auth.php', join('',$new_config));
		}
		if ($newDaftAcl != $daftAcl) {
			io_saveFile(dirname(__FILE__).'/daft.auth.php', join('',$newDaftAcl));
		}
		return;
	}
}