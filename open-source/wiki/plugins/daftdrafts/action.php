<?php
/**
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jon Magne Bøe <jonmagneboe@hotmail.com>
 * @author Gerry Weissbach <gweissbach@inetsoftware.de>
*/

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_daftdrafts extends DokuWiki_Action_Plugin {
	
	/** 
	*  Returns information about the plugin.
	*/
	function getInfo(){
		return array (
            'author' => 'Jon Magne Bøe',
            'date' => '2011-11-06',
            'name' => 'DaftDrafts plugin',
            'desc' => 'Marks pages as drafts, which hides them from unregistered users.',
            'url' => 'http://www.dokuwiki.org/plugin:daftdrafts',
        );
	}

	/**
	* Register event handlers
	* 
	* @author Gerry Weissbach <gweissbach@inetsoftware.de>
	* @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function register(&$controller) {
		$controller->register_hook('PARSER_METADATA_RENDER','AFTER',$this,'_daftdrafts');
		$controller->register_hook('IO_WIKIPAGE_WRITE','AFTER',$this,'_daftdrafts_write');
		$controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
		$controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'show_banner', array());
	}

	/**
	* When  WRITE is triggered, and the content is empty
	*
	* @author Gerry Weissbach <gweissbach@inetsoftware.de>
	* @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function _daftdrafts_write(&$event, $param) {
		global $INFO;
	
		if ( empty($event->data[0][1]) ) {
			$id = resolve_id($event->data[1], $event->data[2]);
			$this->_daftdrafts_del_acl($id);
			$INFO['perm'] = $this->_auth_quickaclcheck($id);
		}
	}
		
	/**
	* In case of read or write of a page, this function is triggered.
	*
	* @author  Gerry Weissbach <gweissbach@inetsoftware.de>
	* @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function _daftdrafts(&$event, $param) {
		global $INFO, $auth, $ID;

		$id = cleanID( empty($event->data['page']) ? $ID : $event->data['page'] );
		$isDraft = false;
		$value = $event->data['current']['type'];
		$isDraft = !empty($value) && $value == 'daftdrafts'; //triggered when the text contains this plugin's syntax.
		
		if ( $isDraft ) {
			$this->_daftdrafts_add_acl($id, $event->data['current']['last_change']['user']);
		} else {
			$this->_daftdrafts_del_acl($id);
		}
		
		$INFO['perm'] = $this->_auth_quickaclcheck($id);
	}

	/**
	*  Add ACL to @ALL and current User
	*
	* @author Gerry Weissbach <gweissbach@inetsoftware.de>
	* @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function _daftdrafts_add_acl($id, $user) {
		global $auth;
		if ( !($daftdrafts =& plugin_load('helper', 'daftdrafts')) ) { return; }
		$daftdrafts->acl_add($id, '@ALL', AUTH_NONE); 
		$daftdrafts->acl_add($id, '@user', AUTH_EDIT);
	}

	/**
	*  Remove ACL to @ALL and current User
	*
	* @author Gerry Weissbach <gweissbach@inetsoftware.de
	* @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function _daftdrafts_del_acl($id) {
		
		if ( !($daftdrafts =& plugin_load('helper', 'daftdrafts')) ) { return; }
		$daftdrafts->acl_del($id);
	}

	/**
	*  Show a banner when viewing pages that are not published.
	*
	*  @author Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function show_banner(& $event, $param) {
		global $AUTH_ACL, $ID, $INFO;
		$autorizationList = $AUTH_ACL;
		$pageIdentification = $ID;
		$allUsers = auth_nameencode('@ALL', true); //all users
		$additional='DAFTDRAFTS';
		$autorizationList = $AUTH_ACL;
		if ( empty($authorizationList) ) $authorizationList = file(DOKU_CONF.'acl.auth.php');
		$daftAuthorizationList = file(dirname(__FILE__).'/daft.auth.php');
		$additional = '\t#' . $additional;
		$acl_pattern_nocomment = '^'.preg_quote($pageIdentification,'/').'\s+.*\s+[0-8].*$'; //pattern for searching acl.auth.php
		$authorizationHits = preg_grep("/$acl_pattern_nocomment/", $authorizationList);
		$numberOfAuthorizations = count($authorizationHits);
		$daftHits = preg_grep("/$acl_pattern_nocomment/", $daftAuthorizationList);
		$numberOfDaftauthorizations = count($daftHits);
		//If both acl.auth.php and daft.auth.php contains restrictions of authorization, it's safe to assume it's not published.
		if ($numberOfAuthorizations != 0 && $numberOfDaftauthorizations != 0) {
			$published = false;
		} else { //Ideally the acl.auth.php and the daft.auth.php-files should be in sync, meaning we don't need to check anything else.
			$published = true;
		}
		$htmlcode = array();
		//This code runs if the page is unpublished, i.e. tagged as a draft and hidden from unregistered users.
		if (!$published && isset($INFO['userinfo'])) {
			$htmlcode[] = '<div class="unpublished">';
			$htmlcode[] = '<span class="draft">';
			$htmlcode[] = $this->getLang('unpublished');
			$htmlcode[] = '<br>';
			$htmlcode[] = $this->getLang('howtopublish'). ' ~~' .$this->getLang('code'). '~~';
			$htmlcode[] = '</span>';
			$htmlcode[] = '</div>';
			ptln(implode($htmlcode));
			return true;
		} else {
			return;
		}
	}
	
	/**
	*  Adds a button to the toolbar for easy adding of the draft-code
	*
	* @author  Jon Magne Bøe <jonmagneboe@hotmail.com>
	*/
	function insert_button(& $event, $param) {
		$event->data[] = array (
			'type' => 'format',			
			'title' => $this->getLang('nowiki'), //Doesn't exist, done on purpose to avoid entering a sample (which is automatically set to title if blank)
			'icon' => '../../plugins/daftdrafts/images/daftdrafts.gif',
			'open' => '~~' .$this->getLang('code'). '~~',
			'close' => '',
		);
	}
	
	/**
	* Quick acl-check
	*/
	function _auth_quickaclcheck($id) {
		if ( function_exists('auth_quickaclcheck') ) {
			return auth_quickaclcheck($id);
		}
		return 0;
	}
}